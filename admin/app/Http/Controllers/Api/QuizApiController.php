<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Option;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptAnswer;
use App\Models\User;
use App\Services\PerformanceMessageService;
use App\Services\QuizRankingService;
use App\Services\QuizStatsService;
use App\Services\TargetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class QuizApiController extends Controller
{
    /**
     * Get all active categories with quizzes count.
     */
    public function categories(): JsonResponse
    {
        $categories = Category::where('is_active', true)
            ->withCount(['quizzes' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'success' => true,
            'categories' => $categories,
        ]);
    }

    /**
     * Get quizzes for a specific category. Bilingual quizzes (no fixed
     * `language`) always show up regardless of the requested language,
     * since they serve both; legacy single-language quizzes only show up
     * for their own language, exactly as before this feature existed.
     */
    public function quizzesByCategory(Request $request, int $categoryId): JsonResponse
    {
        $category = Category::where('is_active', true)->findOrFail($categoryId);
        $lang = $request->query('language', 'en');

        $quizzes = Quiz::where('category_id', $category->id)
            ->where(fn ($q) => $q->where('language', $lang)->orWhereNull('language'))
            ->where('is_active', true)
            ->withCount('questions')
            ->orderBy('sort_order')
            ->get();

        // Roadmap §10-11: real unique-user played count and completion
        // rate, not the fabricated per-title numbers the client used to
        // synthesize.
        QuizStatsService::attachToQuizzes($quizzes);

        return response()->json([
            'success' => true,
            'category' => $category,
            'quizzes' => $quizzes,
        ]);
    }

    /**
     * Get full quiz detail with questions and options, including which
     * option is correct. Answers aren't masked here since the client needs
     * them for its own instant right/wrong feedback; scoring is still
     * authoritatively recomputed server-side on submit.
     *
     * For a bilingual quiz, `?lang=` selects which translation is served
     * (bilingual_question_management_prd.md §9); a legacy single-language
     * quiz ignores it entirely and returns its own fixed-language content,
     * unchanged from before this feature existed.
     */
    public function quizDetail(Request $request, int $id): JsonResponse
    {
        $quiz = Quiz::where('is_active', true)
            ->with(['category'])
            ->with(['questions' => function ($q) {
                $q->orderBy('sort_order')->with(['options', 'translations']);
            }])
            ->with('questions.options.translations')
            ->findOrFail($id);

        $lang = $request->query('lang', $request->query('language', 'en'));
        $this->localizeQuiz($quiz, $lang);

        $stats = QuizStatsService::statsFor($quiz->id);
        $quiz->played_count = $stats['played_count'];
        $quiz->completion_rate = $stats['completion_rate'];

        return response()->json([
            'success' => true,
            'requested_language' => $lang,
            'quiz' => $quiz,
        ]);
    }

    /**
     * Overwrites each question/option's text in-memory with the resolved
     * content for $lang before serialization — a legacy quiz's questions
     * are untouched (their base columns already hold that language), so
     * this only actually changes anything for a bilingual quiz.
     */
    private function localizeQuiz(Quiz $quiz, string $lang): void
    {
        foreach ($quiz->questions as $question) {
            // Avoids each question/option lazily re-querying its own quiz —
            // it's already the one we just loaded.
            $question->setRelation('quiz', $quiz);

            $content = $question->contentFor($lang);
            $question->question_text = $content['question_text'];
            $question->meaning = $content['meaning'];
            $question->explanation = $content['explanation'];
            $question->translation_fallback = $content['translation_fallback'];

            foreach ($question->options as $option) {
                $option->setRelation('question', $question);
                $option->option_text = $option->textFor($lang);
            }
        }
    }

    /**
     * Start a quiz attempt. Recorded immediately (before any question is
     * answered) so an abandoned quiz is still visible to analytics/admin —
     * it just never reaches `status=completed` and so never counts toward
     * completed-quiz statistics or the daily target (roadmap §5.3).
     */
    public function startAttempt(Request $request, int $id): JsonResponse
    {
        $quiz = Quiz::where('is_active', true)->withCount('questions')->findOrFail($id);

        $attempt = QuizAttempt::create([
            'user_id' => $request->user()->id,
            'quiz_id' => $quiz->id,
            // Analytics only (roadmap §11) — never used for scoring.
            'language_used' => $quiz->isBilingual() ? $request->query('lang', $request->query('language')) : $quiz->language,
            'started_at' => now(),
            'status' => 'in_progress',
            'total_questions' => $quiz->questions_count,
        ]);

        return response()->json([
            'success' => true,
            'attempt' => [
                'id' => $attempt->id,
                'quiz_id' => $quiz->id,
                'total_questions' => $quiz->questions_count,
                'started_at' => $attempt->started_at,
            ],
        ]);
    }

    /**
     * Save (or update) the answer for one question of an in-progress
     * attempt. Idempotent: re-answering the same question just replaces the
     * stored answer rather than creating a duplicate row, so retries or a
     * user changing their mind can't double-count anything at submit time.
     */
    public function saveAnswer(Request $request, int $attemptId): JsonResponse
    {
        $attempt = QuizAttempt::where('user_id', $request->user()->id)->findOrFail($attemptId);

        if ($attempt->status !== 'in_progress') {
            return response()->json([
                'success' => false,
                'message' => 'This attempt has already been submitted.',
            ], 409);
        }

        $validated = $request->validate([
            'question_id' => ['required', 'integer', 'exists:questions,id'],
            'selected_option_id' => ['required', 'integer', 'exists:options,id'],
        ]);

        $question = Question::where('quiz_id', $attempt->quiz_id)
            ->with('options')
            ->find($validated['question_id']);

        if (! $question) {
            throw ValidationException::withMessages([
                'question_id' => 'This question does not belong to the attempt\'s quiz.',
            ]);
        }

        $selectedOption = $question->options->firstWhere('id', $validated['selected_option_id']);
        if (! $selectedOption) {
            throw ValidationException::withMessages([
                'selected_option_id' => 'This option does not belong to the given question.',
            ]);
        }

        $correctOption = $question->options->firstWhere('is_correct', true);

        $answer = QuizAttemptAnswer::updateOrCreate(
            ['quiz_attempt_id' => $attempt->id, 'question_id' => $question->id],
            [
                'selected_option_id' => $selectedOption->id,
                'correct_option_id' => $correctOption?->id,
                'is_correct' => $correctOption !== null && $selectedOption->id === $correctOption->id,
                'answered_at' => now(),
            ],
        );

        return response()->json([
            'success' => true,
            'is_correct' => $answer->is_correct,
        ]);
    }

    /**
     * Finalize an attempt from its already-saved answers, calculate score
     * and accuracy, and update the user's running score/streak. Idempotent:
     * resubmitting an already-completed attempt just returns the stored
     * result instead of double-counting the user's score/streak.
     */
    public function submitAttempt(Request $request, int $attemptId): JsonResponse
    {
        $user = $request->user();

        // lockForUpdate + transaction make the completed-status check and
        // the completion write atomic: two near-simultaneous submits of the
        // same attempt (double-tap, client retry) would otherwise both read
        // status=in_progress before either commits, double-awarding score/
        // streak/target credit. The second request now blocks until the
        // first commits, then takes the idempotent early-return below.
        return DB::transaction(function () use ($request, $attemptId, $user) {
            $attempt = QuizAttempt::where('user_id', $user->id)
                ->with('quiz')
                ->lockForUpdate()
                ->findOrFail($attemptId);

            if ($attempt->status === 'completed') {
                return response()->json([
                    'success' => true,
                    'result' => $this->attemptResult($attempt),
                ]);
            }

            $quiz = $attempt->quiz;
            $answers = $attempt->answers()->with('question')->get();

            $attemptedQuestions = $answers->count();
            $correctAnswers = $answers->where('is_correct', true)->count();
            $wrongAnswers = $attemptedQuestions - $correctAnswers;
            $unanswered = max(0, $attempt->total_questions - $attemptedQuestions);
            $earnedScore = (int) $answers->where('is_correct', true)->sum(fn ($a) => $a->question->points ?? 0);
            $accuracy = $attemptedQuestions > 0 ? round(($correctAnswers / $attemptedQuestions) * 100, 2) : 0;
            $percentage = $attempt->total_questions > 0
                ? round(($correctAnswers / $attempt->total_questions) * 100, 1)
                : 0;
            $passed = $percentage >= $quiz->passing_percentage;

            $attempt->forceFill([
                'status' => 'completed',
                'completed_at' => now(),
                'attempted_questions' => $attemptedQuestions,
                'correct_answers' => $correctAnswers,
                'wrong_answers' => $wrongAnswers,
                'unanswered_questions' => $unanswered,
                'score' => $earnedScore,
                'accuracy' => $accuracy,
            ])->save();

            $user->increment('score', $earnedScore);
            if ($passed) {
                $user->increment('streak', 1);
            }

            // Only a genuinely completed quiz counts toward the daily target
            // (roadmap §5.3) — this line only runs on the first-time-completed
            // branch, never on the idempotent early return above.
            TargetService::recordCompletedQuiz($user, $attempt->completed_at);

            return response()->json([
                'success' => true,
                'result' => $this->attemptResult($attempt->fresh(), $passed, $percentage),
            ]);
        });
    }

    /**
     * Shared response shape for a completed attempt, used both right after
     * submission and when re-fetching an already-completed one.
     */
    private function attemptResult(QuizAttempt $attempt, ?bool $passed = null, ?float $percentage = null): array
    {
        $quiz = $attempt->quiz ?? Quiz::find($attempt->quiz_id);
        $percentage ??= $attempt->total_questions > 0
            ? round(($attempt->correct_answers / $attempt->total_questions) * 100, 1)
            : 0;
        $passed ??= $quiz && $percentage >= $quiz->passing_percentage;
        $user = $attempt->user;

        return [
            'attempt_id' => $attempt->id,
            'score' => $attempt->score,
            'correct_answers' => $attempt->correct_answers,
            'wrong_answers' => $attempt->wrong_answers,
            'unanswered_questions' => $attempt->unanswered_questions,
            'total_questions' => $attempt->total_questions,
            'accuracy' => $attempt->accuracy,
            'percentage' => $percentage,
            'passed' => $passed,
            'passing_percentage' => $quiz->passing_percentage ?? null,
            'time_taken_seconds' => QuizRankingService::timeTakenSeconds($attempt),
            // Dynamic result message state (roadmap "Dynamic Result
            // Message") — the frontend maps this to heading/art/copy.
            'performance_state' => PerformanceMessageService::stateFor($percentage),
            // Quiz-specific leaderboard, NOT the global Achievement-page
            // ranking (roadmap Part C — the two must stay separate).
            'quiz_ranking' => QuizRankingService::summaryFor($attempt->quiz_id, $attempt->user_id),
            'updated_streak' => $user?->streak,
            'updated_total_score' => $user?->score,
        ];
    }

    /**
     * Standalone quiz-specific leaderboard refresh (roadmap Part E — a
     * screen can re-fetch this without resubmitting a quiz).
     */
    public function quizRanking(Request $request, int $id): JsonResponse
    {
        Quiz::where('is_active', true)->findOrFail($id);

        return response()->json([
            'success' => true,
            'ranking' => QuizRankingService::summaryFor($id, $request->user()->id),
        ]);
    }

    /**
     * Get global leaderboard.
     */
    public function leaderboard(): JsonResponse
    {
        $leaders = User::where('role', '!=', 'admin')
            ->where('is_active', true)
            ->orderByDesc('score')
            ->orderByDesc('streak')
            ->limit(20)
            ->get(['id', 'name', 'avatar', 'score', 'streak']);

        return response()->json([
            'success' => true,
            'leaderboard' => $leaders,
        ]);
    }

    /**
     * Get authenticated user's attempt history.
     */
    public function userHistory(Request $request): JsonResponse
    {
        $attempts = QuizAttempt::where('user_id', $request->user()->id)
            ->where('status', 'completed')
            ->with(['quiz:id,title,category_id,difficulty', 'quiz.category:id,name,color'])
            ->orderByDesc('completed_at')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'history' => $attempts,
        ]);
    }
}
