<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Option;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptAnswer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Roadmap §5 and the Phase 10 checklist: abandoned vs. completed quizzes,
 * per-question answer storage, and multiple attempts of the same quiz.
 */
class QuizAttemptTest extends TestCase
{
    use RefreshDatabase;

    private function makeQuizWithQuestions(int $questionCount = 3): Quiz
    {
        $category = Category::create([
            'name' => 'Test Category', 'slug' => 'test-category-' . uniqid(), 'color' => '#000000',
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

    public function test_starting_a_quiz_records_an_in_progress_attempt(): void
    {
        $quiz = $this->makeQuizWithQuestions();
        $user = User::factory()->create();

        $attemptId = $this->actingAs($user, 'sanctum')
            ->postJson("/api/quizzes/{$quiz->id}/start")
            ->assertOk()
            ->json('attempt.id');

        $attempt = QuizAttempt::find($attemptId);
        $this->assertSame('in_progress', $attempt->status);
        $this->assertSame($quiz->questions->count(), $attempt->total_questions);
    }

    public function test_abandoned_quiz_is_stored_for_analytics_but_never_counts_as_completed(): void
    {
        $quiz = $this->makeQuizWithQuestions();
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')->postJson("/api/quizzes/{$quiz->id}/start")->assertOk();

        // Never submitted — the user closed the app mid-quiz.
        $this->assertSame(0, QuizAttempt::where('status', 'completed')->count());
        $this->actingAs($user, 'sanctum')
            ->getJson('/api/user/history')
            ->assertOk()
            ->assertJsonCount(0, 'history.data');
    }

    public function test_completing_a_quiz_stores_correct_and_wrong_answers_per_question(): void
    {
        $quiz = $this->makeQuizWithQuestions(3);
        $user = User::factory()->create();

        $attemptId = $this->actingAs($user, 'sanctum')
            ->postJson("/api/quizzes/{$quiz->id}/start")->json('attempt.id');

        $questions = $quiz->questions;
        // Answer question 1 correctly, question 2 incorrectly, leave 3 unanswered.
        $this->actingAs($user, 'sanctum')->postJson("/api/attempts/{$attemptId}/answer", [
            'question_id' => $questions[0]->id,
            'selected_option_id' => $questions[0]->options->firstWhere('is_correct', true)->id,
        ])->assertOk()->assertJsonPath('is_correct', true);

        $this->actingAs($user, 'sanctum')->postJson("/api/attempts/{$attemptId}/answer", [
            'question_id' => $questions[1]->id,
            'selected_option_id' => $questions[1]->options->firstWhere('is_correct', false)->id,
        ])->assertOk()->assertJsonPath('is_correct', false);

        $result = $this->actingAs($user, 'sanctum')
            ->postJson("/api/attempts/{$attemptId}/submit")
            ->assertOk()->json('result');

        $this->assertSame(1, $result['correct_answers']);
        $this->assertSame(1, $result['wrong_answers']);
        $this->assertSame(1, $result['unanswered_questions']);
        $this->assertSame('completed', QuizAttempt::find($attemptId)->status);

        // The stored per-question rows are the actual audit trail an admin
        // inspects (roadmap §5.2): Question -> Selected -> Correct -> Right/Wrong.
        $this->assertSame(2, QuizAttemptAnswer::where('quiz_attempt_id', $attemptId)->count());
    }

    public function test_multiple_attempts_by_same_user_are_all_recorded_individually(): void
    {
        $quiz = $this->makeQuizWithQuestions(1);
        $user = User::factory()->create();
        $question = $quiz->questions->first();
        $correctOption = $question->options->firstWhere('is_correct', true);

        foreach (range(1, 3) as $_) {
            $attemptId = $this->actingAs($user, 'sanctum')
                ->postJson("/api/quizzes/{$quiz->id}/start")->json('attempt.id');
            $this->actingAs($user, 'sanctum')->postJson("/api/attempts/{$attemptId}/answer", [
                'question_id' => $question->id, 'selected_option_id' => $correctOption->id,
            ]);
            $this->actingAs($user, 'sanctum')->postJson("/api/attempts/{$attemptId}/submit")->assertOk();
        }

        $this->assertSame(3, QuizAttempt::where('user_id', $user->id)->where('status', 'completed')->count());
    }

    public function test_resubmitting_a_completed_attempt_does_not_double_award_score(): void
    {
        $quiz = $this->makeQuizWithQuestions(1);
        $user = User::factory()->create();
        $question = $quiz->questions->first();
        $correctOption = $question->options->firstWhere('is_correct', true);

        $attemptId = $this->actingAs($user, 'sanctum')
            ->postJson("/api/quizzes/{$quiz->id}/start")->json('attempt.id');
        $this->actingAs($user, 'sanctum')->postJson("/api/attempts/{$attemptId}/answer", [
            'question_id' => $question->id, 'selected_option_id' => $correctOption->id,
        ]);
        $this->actingAs($user, 'sanctum')->postJson("/api/attempts/{$attemptId}/submit")->assertOk();

        $scoreAfterFirst = $user->fresh()->score;

        $this->actingAs($user, 'sanctum')->postJson("/api/attempts/{$attemptId}/submit")->assertOk();
        $this->assertSame($scoreAfterFirst, $user->fresh()->score);
    }

    public function test_answering_after_completion_is_rejected(): void
    {
        $quiz = $this->makeQuizWithQuestions(1);
        $user = User::factory()->create();
        $question = $quiz->questions->first();
        $correctOption = $question->options->firstWhere('is_correct', true);

        $attemptId = $this->actingAs($user, 'sanctum')
            ->postJson("/api/quizzes/{$quiz->id}/start")->json('attempt.id');
        $this->actingAs($user, 'sanctum')->postJson("/api/attempts/{$attemptId}/submit")->assertOk();

        $this->actingAs($user, 'sanctum')->postJson("/api/attempts/{$attemptId}/answer", [
            'question_id' => $question->id, 'selected_option_id' => $correctOption->id,
        ])->assertStatus(409);
    }

    public function test_a_user_cannot_answer_or_submit_another_users_attempt(): void
    {
        $quiz = $this->makeQuizWithQuestions(1);
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $question = $quiz->questions->first();

        $attemptId = $this->actingAs($owner, 'sanctum')
            ->postJson("/api/quizzes/{$quiz->id}/start")->json('attempt.id');

        $this->actingAs($intruder, 'sanctum')->postJson("/api/attempts/{$attemptId}/answer", [
            'question_id' => $question->id,
            'selected_option_id' => $question->options->first()->id,
        ])->assertStatus(404);

        $this->actingAs($intruder, 'sanctum')
            ->postJson("/api/attempts/{$attemptId}/submit")
            ->assertStatus(404);
    }
}
