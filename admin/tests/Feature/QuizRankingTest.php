<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Option;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use App\Services\PerformanceMessageService;
use App\Services\QuizRankingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * leaderboard_achievement_dynamic_roadmap.md Part A: per-quiz ranking uses
 * each user's BEST completed attempt, is separate from the global ranking,
 * and the submit response carries a dynamic performance state + time taken.
 */
class QuizRankingTest extends TestCase
{
    use RefreshDatabase;

    private function makeQuizWithQuestions(int $questionCount = 5): Quiz
    {
        $category = Category::create([
            'name' => 'Test Category', 'slug' => 'test-category-' . uniqid(), 'color' => '#000',
            'is_active' => true, 'sort_order' => 1,
        ]);
        $quiz = Quiz::create([
            'category_id' => $category->id, 'language' => 'en', 'title' => 'Test Quiz',
            'slug' => 'test-quiz-' . uniqid(), 'duration_minutes' => 5, 'passing_percentage' => 50,
            'difficulty' => 'easy', 'is_active' => true, 'sort_order' => 1,
        ]);
        foreach (range(1, $questionCount) as $i) {
            $question = Question::create([
                'quiz_id' => $quiz->id, 'question_text' => "Q{$i}", 'points' => 10, 'sort_order' => $i,
            ]);
            Option::create(['question_id' => $question->id, 'option_text' => 'Right', 'is_correct' => true]);
            Option::create(['question_id' => $question->id, 'option_text' => 'Wrong', 'is_correct' => false]);
        }

        return $quiz->load('questions.options');
    }

    private function completedAttempt(Quiz $quiz, User $user, int $correct, string $startedAt, string $completedAt): QuizAttempt
    {
        $total = $quiz->questions->count();

        return QuizAttempt::create([
            'user_id' => $user->id, 'quiz_id' => $quiz->id, 'status' => 'completed',
            'started_at' => $startedAt, 'completed_at' => $completedAt,
            'total_questions' => $total, 'attempted_questions' => $total,
            'correct_answers' => $correct, 'wrong_answers' => $total - $correct, 'unanswered_questions' => 0,
            'score' => $correct * 10,
            'accuracy' => $total > 0 ? round($correct / $total * 100, 2) : 0,
        ]);
    }

    public function test_ranking_uses_each_users_best_attempt_not_every_attempt(): void
    {
        $quiz = $this->makeQuizWithQuestions(5);
        $user = User::factory()->create();

        // Same user: a weak attempt, then a perfect one, then another weak one.
        $this->completedAttempt($quiz, $user, 2, '2026-09-16 10:00:00', '2026-09-16 10:05:00');
        $this->completedAttempt($quiz, $user, 5, '2026-09-16 11:00:00', '2026-09-16 11:03:00');
        $this->completedAttempt($quiz, $user, 3, '2026-09-16 12:00:00', '2026-09-16 12:04:00');

        $this->assertSame(3, QuizAttempt::where('user_id', $user->id)->count(), 'All 3 attempts are still stored for history/analytics.');

        $summary = QuizRankingService::summaryFor($quiz->id, $user->id);

        $this->assertSame(1, $summary['total_participants'], 'One user with 3 attempts must appear as ONE leaderboard entry.');
        $this->assertSame(50, $summary['top'][0]['score'], 'The best attempt (5 correct = 50 pts) must be the one that counts.');
    }

    public function test_ranking_order_follows_score_then_accuracy_then_time_then_completion_order(): void
    {
        $quiz = $this->makeQuizWithQuestions(10);
        $fast = User::factory()->create(['name' => 'Fast Finisher']);
        $slow = User::factory()->create(['name' => 'Slow Finisher']);
        $lowScore = User::factory()->create(['name' => 'Low Scorer']);

        // Same score (8/10) but $fast took less time -> ranks above $slow.
        $this->completedAttempt($quiz, $fast, 8, '2026-09-16 10:00:00', '2026-09-16 10:05:00');
        $this->completedAttempt($quiz, $slow, 8, '2026-09-16 10:00:00', '2026-09-16 10:20:00');
        $this->completedAttempt($quiz, $lowScore, 3, '2026-09-16 10:00:00', '2026-09-16 10:02:00');

        $summary = QuizRankingService::summaryFor($quiz->id, $fast->id);
        $names = array_column($summary['top'], 'name');

        $this->assertSame(['Fast Finisher', 'Slow Finisher', 'Low Scorer'], $names);
        $this->assertSame(1, $summary['your_rank']);
        $this->assertSame(3, $summary['total_participants']);
    }

    public function test_global_ranking_and_quiz_ranking_are_independent(): void
    {
        $quizA = $this->makeQuizWithQuestions(5);
        $quizB = $this->makeQuizWithQuestions(5);
        $user = User::factory()->create(['score' => 0]);
        $rival = User::factory()->create(['score' => 500]); // dominates global ranking

        // $user wins on quizA specifically...
        $this->completedAttempt($quizA, $user, 5, '2026-09-16 10:00:00', '2026-09-16 10:02:00');
        $this->completedAttempt($quizA, $rival, 1, '2026-09-16 10:00:00', '2026-09-16 10:02:00');

        $quizSummary = QuizRankingService::summaryFor($quizA->id, $user->id);
        $this->assertSame(1, $quizSummary['your_rank'], 'Winning this specific quiz must not depend on global score.');

        // ...but quizB (which $user never attempted) has no bearing on quizA's ranking.
        $this->completedAttempt($quizB, $rival, 5, '2026-09-16 10:00:00', '2026-09-16 10:02:00');
        $unaffected = QuizRankingService::summaryFor($quizA->id, $user->id);
        $this->assertSame(1, $unaffected['your_rank']);
        $this->assertSame(2, $unaffected['total_participants']);
    }

    public function test_performance_state_thresholds(): void
    {
        $this->assertSame('excellent', PerformanceMessageService::stateFor(95));
        $this->assertSame('good', PerformanceMessageService::stateFor(75));
        $this->assertSame('average', PerformanceMessageService::stateFor(55));
        $this->assertSame('low', PerformanceMessageService::stateFor(20));
    }

    public function test_submit_response_includes_time_taken_performance_state_and_quiz_ranking(): void
    {
        $quiz = $this->makeQuizWithQuestions(4);
        $user = User::factory()->create();

        $attemptId = $this->actingAs($user, 'sanctum')
            ->postJson("/api/quizzes/{$quiz->id}/start")->json('attempt.id');

        foreach ($quiz->questions as $question) {
            $this->actingAs($user, 'sanctum')->postJson("/api/attempts/{$attemptId}/answer", [
                'question_id' => $question->id,
                'selected_option_id' => $question->options->firstWhere('is_correct', true)->id,
            ]);
        }

        $result = $this->actingAs($user, 'sanctum')
            ->postJson("/api/attempts/{$attemptId}/submit")
            ->assertOk()
            ->json('result');

        $this->assertSame('excellent', $result['performance_state']);
        $this->assertIsInt($result['time_taken_seconds']);
        $this->assertSame(1, $result['quiz_ranking']['your_rank']);
        $this->assertSame(1, $result['quiz_ranking']['total_participants']);
        $this->assertCount(1, $result['quiz_ranking']['top']);
    }

    public function test_quiz_ranking_endpoint_matches_submit_response(): void
    {
        $quiz = $this->makeQuizWithQuestions(2);
        $user = User::factory()->create();
        $this->completedAttempt($quiz, $user, 2, '2026-09-16 10:00:00', '2026-09-16 10:01:00');

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/quizzes/{$quiz->id}/ranking")
            ->assertOk()
            ->assertJsonPath('ranking.your_rank', 1)
            ->assertJsonPath('ranking.total_participants', 1);
    }

    public function test_abandoned_attempts_never_appear_in_quiz_ranking(): void
    {
        $quiz = $this->makeQuizWithQuestions(3);
        $completer = User::factory()->create();
        $abandoner = User::factory()->create();

        $this->completedAttempt($quiz, $completer, 3, '2026-09-16 10:00:00', '2026-09-16 10:01:00');
        QuizAttempt::create([
            'user_id' => $abandoner->id, 'quiz_id' => $quiz->id, 'status' => 'in_progress',
            'started_at' => now(), 'total_questions' => 3,
        ]);

        $summary = QuizRankingService::summaryFor($quiz->id, $abandoner->id);
        $this->assertSame(1, $summary['total_participants']);
        $this->assertNull($summary['your_rank'], 'A user who never completed the quiz has no ranking position.');
    }
}
