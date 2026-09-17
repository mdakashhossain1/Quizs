<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use App\Models\UserProgression;
use App\Services\ProfileStatsService;
use App\Services\QuizStatsService;
use App\Services\RankingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Roadmap §8, §9, §10, §11, §12 and the Phase 10 checklist: profile
 * counters, unique-user "Played," completion rate, month rollover, and
 * backend-computed ranking.
 */
class ProfileAndRankingTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function makeQuiz(): Quiz
    {
        $category = Category::create([
            'name' => 'Test Category', 'slug' => 'test-category-' . uniqid(), 'color' => '#000',
            'is_active' => true, 'sort_order' => 1,
        ]);

        return Quiz::create([
            'category_id' => $category->id, 'language' => 'en', 'title' => 'Test Quiz',
            'slug' => 'test-quiz-' . uniqid(), 'duration_minutes' => 5, 'passing_percentage' => 50,
            'difficulty' => 'easy', 'is_active' => true, 'sort_order' => 1,
        ]);
    }

    public function test_profile_counters_only_reflect_completed_attempts(): void
    {
        $quiz = $this->makeQuiz();
        $user = User::factory()->create();

        QuizAttempt::create([
            'user_id' => $user->id, 'quiz_id' => $quiz->id, 'status' => 'completed', 'completed_at' => now(),
            'total_questions' => 10, 'correct_answers' => 8, 'wrong_answers' => 2, 'unanswered_questions' => 0,
        ]);
        // Abandoned — must not count anywhere.
        QuizAttempt::create([
            'user_id' => $user->id, 'quiz_id' => $quiz->id, 'status' => 'in_progress',
            'total_questions' => 10, 'correct_answers' => 5, 'wrong_answers' => 0, 'unanswered_questions' => 0,
        ]);

        $stats = ProfileStatsService::compute($user);

        $this->assertSame(1, $stats['quiz_played']);
        $this->assertSame(8, $stats['right']);
        $this->assertSame(2, $stats['wrong']);
        $this->assertEquals(80.0, $stats['accuracy']);
    }

    public function test_this_month_only_counts_completions_in_the_current_business_month(): void
    {
        $quiz = $this->makeQuiz();
        $user = User::factory()->create();

        Carbon::setTestNow('2026-09-16 12:00:00');
        QuizAttempt::create([
            'user_id' => $user->id, 'quiz_id' => $quiz->id, 'status' => 'completed', 'completed_at' => now(),
            'total_questions' => 5, 'correct_answers' => 5, 'wrong_answers' => 0, 'unanswered_questions' => 0,
        ]);
        QuizAttempt::create([
            'user_id' => $user->id, 'quiz_id' => $quiz->id, 'status' => 'completed',
            'completed_at' => Carbon::parse('2026-08-20'),
            'total_questions' => 5, 'correct_answers' => 5, 'wrong_answers' => 0, 'unanswered_questions' => 0,
        ]);

        $stats = ProfileStatsService::compute($user);
        $this->assertSame(1, $stats['this_month'], 'Last month\'s completion must not bleed into this month\'s count.');

        // Roll over to next month — the September completion drops out.
        Carbon::setTestNow('2026-10-01 00:00:01');
        $stats = ProfileStatsService::compute($user);
        $this->assertSame(0, $stats['this_month']);
    }

    public function test_played_count_is_unique_completers_not_total_attempts(): void
    {
        $quiz = $this->makeQuiz();
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        foreach (range(1, 3) as $_) {
            QuizAttempt::create(['user_id' => $userA->id, 'quiz_id' => $quiz->id, 'status' => 'completed', 'completed_at' => now(), 'total_questions' => 5]);
        }
        QuizAttempt::create(['user_id' => $userB->id, 'quiz_id' => $quiz->id, 'status' => 'in_progress', 'total_questions' => 5]);

        $stats = QuizStatsService::statsFor($quiz->id);

        $this->assertSame(1, $stats['played_count'], 'One user completing 3 times must count once, per roadmap §10.1.');
        // Started = 2 unique users (A, B); completed = 1 (A) -> 50%.
        $this->assertEquals(50.0, $stats['completion_rate']);
    }

    public function test_completion_rate_is_zero_with_no_starters_and_never_divides_by_zero(): void
    {
        $quiz = $this->makeQuiz();
        $stats = QuizStatsService::statsFor($quiz->id);
        $this->assertSame(0, $stats['played_count']);
        $this->assertSame(0.0, $stats['completion_rate']);
    }

    public function test_global_ranking_follows_level_then_xp_then_accuracy_then_completions(): void
    {
        // An admin with a huge score must never appear in the eligible pool.
        User::factory()->admin()->create(['score' => 999999]);

        $quiz = $this->makeQuiz();
        $levelTwo = User::factory()->create();
        UserProgression::create(['user_id' => $levelTwo->id, 'current_level' => 2, 'xp' => 150]);

        $higherXpSameLevel = User::factory()->create();
        UserProgression::create(['user_id' => $higherXpSameLevel->id, 'current_level' => 1, 'xp' => 90]);
        $lowerXpSameLevel = User::factory()->create();
        UserProgression::create(['user_id' => $lowerXpSameLevel->id, 'current_level' => 1, 'xp' => 10]);

        // Same level (1) and xp (0, the default) as each other — decided by
        // accuracy, then by completed-quiz count.
        $higherAccuracy = User::factory()->create();
        QuizAttempt::create(['user_id' => $higherAccuracy->id, 'quiz_id' => $quiz->id, 'status' => 'completed', 'completed_at' => now(), 'total_questions' => 10, 'correct_answers' => 9, 'wrong_answers' => 1]);
        $lowerAccuracy = User::factory()->create();
        QuizAttempt::create(['user_id' => $lowerAccuracy->id, 'quiz_id' => $quiz->id, 'status' => 'completed', 'completed_at' => now(), 'total_questions' => 10, 'correct_answers' => 1, 'wrong_answers' => 9]);

        $this->assertSame(1, RankingService::rankFor($levelTwo), 'Level beats everything else.');
        $this->assertSame(2, RankingService::rankFor($higherXpSameLevel), 'Same level, higher XP wins.');
        $this->assertSame(3, RankingService::rankFor($lowerXpSameLevel));
        $this->assertSame(4, RankingService::rankFor($higherAccuracy), 'Same level/xp, higher accuracy wins.');
        $this->assertSame(5, RankingService::rankFor($lowerAccuracy));

        $this->assertSame(5, RankingService::totalEligibleUsers());
    }

    public function test_global_and_quiz_ranking_use_independent_algorithms(): void
    {
        // A user can dominate a single quiz (QuizRankingService) while
        // ranking poorly overall (RankingService) — the two must never be
        // computed from the same call or influence each other.
        $quiz = $this->makeQuiz();
        $newcomer = User::factory()->create();
        QuizAttempt::create(['user_id' => $newcomer->id, 'quiz_id' => $quiz->id, 'status' => 'completed', 'completed_at' => now(), 'total_questions' => 5, 'correct_answers' => 5, 'wrong_answers' => 0, 'score' => 50]);

        $veteran = User::factory()->create();
        UserProgression::create(['user_id' => $veteran->id, 'current_level' => 10, 'xp' => 5000]);

        $this->assertSame(2, RankingService::rankFor($newcomer), 'Globally the veteran\'s level dominates.');
        $this->assertSame(1, RankingService::rankFor($veteran));
    }

    public function test_profile_stats_endpoint_matches_the_same_computation_admin_sees(): void
    {
        $quiz = $this->makeQuiz();
        $user = User::factory()->create();
        QuizAttempt::create([
            'user_id' => $user->id, 'quiz_id' => $quiz->id, 'status' => 'completed', 'completed_at' => now(),
            'total_questions' => 4, 'correct_answers' => 4, 'wrong_answers' => 0, 'unanswered_questions' => 0,
        ]);

        $apiStats = $this->actingAs($user, 'sanctum')
            ->getJson('/api/profile/stats')->assertOk()->json('stats');
        $serviceStats = ProfileStatsService::compute($user->fresh());

        $this->assertSame($serviceStats['quiz_played'], $apiStats['quiz_played']);
        $this->assertSame($serviceStats['right'], $apiStats['right']);
        $this->assertSame($serviceStats['rank'], $apiStats['rank']);
    }
}
