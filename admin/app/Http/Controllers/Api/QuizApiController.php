<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Option;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
     * Get quizzes for a specific category, filtered by language.
     */
    public function quizzesByCategory(Request $request, int $categoryId): JsonResponse
    {
        $category = Category::where('is_active', true)->findOrFail($categoryId);

        $quizzes = Quiz::where('category_id', $category->id)
            ->where('language', $request->query('language', 'en'))
            ->where('is_active', true)
            ->withCount('questions')
            ->orderBy('sort_order')
            ->get();

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
     */
    public function quizDetail(int $id): JsonResponse
    {
        $quiz = Quiz::where('is_active', true)
            ->with(['category'])
            ->with(['questions' => function ($q) {
                $q->orderBy('sort_order')->with('options');
            }])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'quiz' => $quiz,
        ]);
    }

    /**
     * Submit answers for a quiz, calculate score, record attempt, update user streak/score.
     */
    public function submitQuiz(Request $request, int $id): JsonResponse
    {
        $quiz = Quiz::with(['questions.options'])->findOrFail($id);
        $user = $request->user();

        // Expect answers in format: [ question_id => option_id, ... ]
        $userAnswers = $request->input('answers', []);

        $totalQuestions = $quiz->questions->count();
        $correctAnswers = 0;
        $earnedScore = 0;
        $totalPossibleScore = 0;
        $review = [];

        foreach ($quiz->questions as $question) {
            $totalPossibleScore += $question->points;
            $selectedOptionId = $userAnswers[$question->id] ?? null;

            $correctOption = $question->options->firstWhere('is_correct', true);
            $selectedOption = $selectedOptionId ? $question->options->firstWhere('id', $selectedOptionId) : null;

            $isCorrect = $selectedOption && $correctOption && ($selectedOption->id === $correctOption->id);

            if ($isCorrect) {
                $correctAnswers++;
                $earnedScore += $question->points;
            }

            $review[] = [
                'question_id' => $question->id,
                'question_text' => $question->question_text,
                'explanation' => $question->explanation,
                'selected_option_id' => $selectedOptionId,
                'correct_option_id' => $correctOption ? $correctOption->id : null,
                'is_correct' => $isCorrect,
            ];
        }

        $percentage = $totalQuestions > 0 ? round(($correctAnswers / $totalQuestions) * 100, 1) : 0;
        $passed = $percentage >= $quiz->passing_percentage;

        $attempt = QuizAttempt::create([
            'user_id' => $user->id,
            'quiz_id' => $quiz->id,
            'score' => $earnedScore,
            'total_questions' => $totalQuestions,
            'correct_answers' => $correctAnswers,
            'completed_at' => now(),
        ]);

        $user->increment('score', $earnedScore);
        if ($passed) {
            $user->increment('streak', 1);
        }

        return response()->json([
            'success' => true,
            'result' => [
                'attempt_id' => $attempt->id,
                'score' => $earnedScore,
                'total_possible_score' => $totalPossibleScore,
                'correct_answers' => $correctAnswers,
                'total_questions' => $totalQuestions,
                'percentage' => $percentage,
                'passed' => $passed,
                'passing_percentage' => $quiz->passing_percentage,
                'updated_streak' => $user->fresh()->streak,
                'updated_total_score' => $user->fresh()->score,
                'review' => $review,
            ],
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
            ->with(['quiz:id,title,category_id,difficulty', 'quiz.category:id,name,color'])
            ->orderByDesc('completed_at')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'history' => $attempts,
        ]);
    }
}
