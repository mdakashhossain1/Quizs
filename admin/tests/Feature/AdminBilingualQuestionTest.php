<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Option;
use App\Models\Question;
use App\Models\QuestionTranslation;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptAnswer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * bilingual_question_management_prd.md §4-§7: the Admin Panel creates one
 * question with two translations from a single form, allows editing either
 * language independently, and a legacy single-language quiz's question form
 * must keep working exactly as it did before this feature existed.
 */
class AdminBilingualQuestionTest extends TestCase
{
    use RefreshDatabase;

    private function makeCategory(): Category
    {
        return Category::create([
            'name' => 'Test Category', 'slug' => 'test-category-' . uniqid(), 'color' => '#000',
            'is_active' => true, 'sort_order' => 1,
        ]);
    }

    private function makeBilingualQuiz(): Quiz
    {
        return Quiz::create([
            'category_id' => $this->makeCategory()->id, 'language' => null, 'title' => 'Bilingual Quiz',
            'slug' => 'bilingual-quiz-' . uniqid(), 'duration_minutes' => 5, 'passing_percentage' => 50,
            'difficulty' => 'easy', 'is_active' => true, 'sort_order' => 1,
        ]);
    }

    private function makeLegacyQuiz(): Quiz
    {
        return Quiz::create([
            'category_id' => $this->makeCategory()->id, 'language' => 'en', 'title' => 'Legacy Quiz',
            'slug' => 'legacy-quiz-' . uniqid(), 'duration_minutes' => 5, 'passing_percentage' => 50,
            'difficulty' => 'easy', 'is_active' => true, 'sort_order' => 1,
        ]);
    }

    public function test_admin_can_create_a_bilingual_question_with_both_languages(): void
    {
        $admin = User::factory()->admin()->create();
        $quiz = $this->makeBilingualQuiz();

        $this->actingAs($admin, 'web')->post(route('admin.questions.store'), [
            'quiz_id' => $quiz->id,
            'points' => 10,
            'sort_order' => 0,
            'correct_option' => 1,
            'translations' => [
                'en' => ['question_text' => 'What is the capital of India?', 'explanation' => 'New Delhi is the capital.', 'options' => ['Mumbai', 'New Delhi', 'Kolkata', 'Chennai']],
                'hi' => ['question_text' => 'भारत की राजधानी क्या है?', 'explanation' => 'नई दिल्ली भारत की राजधानी है।', 'options' => ['मुंबई', 'नई दिल्ली', 'कोलकाता', 'चेन्नई']],
            ],
        ])->assertRedirect();

        $question = Question::firstOrFail();
        $this->assertCount(2, $question->translations);
        $this->assertSame('भारत की राजधानी क्या है?', $question->translations->firstWhere('language_code', 'hi')->question_text);

        $options = $question->options()->with('translations')->get();
        $this->assertCount(4, $options);
        $correctOption = $options->firstWhere('is_correct', true);
        $this->assertSame('New Delhi', $correctOption->translations->firstWhere('language_code', 'en')->option_text);
        $this->assertSame('नई दिल्ली', $correctOption->translations->firstWhere('language_code', 'hi')->option_text);
    }

    public function test_admin_can_save_english_only_as_a_draft_missing_hindi(): void
    {
        $admin = User::factory()->admin()->create();
        $quiz = $this->makeBilingualQuiz();

        $this->actingAs($admin, 'web')->post(route('admin.questions.store'), [
            'quiz_id' => $quiz->id,
            'points' => 10,
            'sort_order' => 0,
            'correct_option' => 0,
            'translations' => [
                'en' => ['question_text' => 'English only question.', 'options' => ['A', 'B']],
                'hi' => ['question_text' => '', 'options' => ['', '']],
            ],
        ])->assertRedirect();

        $question = Question::firstOrFail();
        $this->assertCount(1, $question->translations);
        $this->assertSame('en', $question->translations->first()->language_code);
        $this->assertFalse($question->hasCompleteTranslations(['en', 'hi']));
    }

    public function test_admin_can_edit_hindi_translation_without_touching_english(): void
    {
        $admin = User::factory()->admin()->create();
        $quiz = $this->makeBilingualQuiz();
        $question = Question::create(['quiz_id' => $quiz->id, 'question_text' => '', 'points' => 10, 'sort_order' => 0]);
        QuestionTranslation::create(['question_id' => $question->id, 'language_code' => 'en', 'question_text' => 'Original English.']);

        $this->actingAs($admin, 'web')->put(route('admin.questions.update', $question), [
            'quiz_id' => $quiz->id,
            'points' => 10,
            'sort_order' => 0,
            'correct_option' => 0,
            'translations' => [
                'en' => ['question_text' => 'Original English.', 'options' => ['A', 'B']],
                'hi' => ['question_text' => 'नया हिंदी अनुवाद।', 'options' => ['ए', 'बी']],
            ],
        ])->assertRedirect();

        $question->refresh()->load('translations');
        $this->assertSame('Original English.', $question->translations->firstWhere('language_code', 'en')->question_text);
        $this->assertSame('नया हिंदी अनुवाद।', $question->translations->firstWhere('language_code', 'hi')->question_text);
    }

    public function test_legacy_quiz_question_form_is_unaffected(): void
    {
        $admin = User::factory()->admin()->create();
        $quiz = $this->makeLegacyQuiz();

        $this->actingAs($admin, 'web')->post(route('admin.questions.store'), [
            'quiz_id' => $quiz->id,
            'question_text' => 'Legacy question?',
            'explanation' => 'Legacy explanation.',
            'points' => 10,
            'sort_order' => 0,
            'options' => ['A', 'B', 'C', 'D'],
            'correct_option' => 2,
        ])->assertRedirect();

        $question = Question::firstOrFail();
        $this->assertSame('Legacy question?', $question->question_text);
        $this->assertCount(0, $question->translations);
        $this->assertCount(4, $question->options);
    }

    public function test_index_search_finds_a_bilingual_question_by_hindi_text(): void
    {
        $quiz = $this->makeBilingualQuiz();
        $question = Question::create(['quiz_id' => $quiz->id, 'question_text' => '', 'points' => 10, 'sort_order' => 0]);
        QuestionTranslation::create(['question_id' => $question->id, 'language_code' => 'hi', 'question_text' => 'भारत की राजधानी क्या है?']);
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin, 'web')->get(route('admin.questions.index', ['search' => 'राजधानी']));

        $response->assertSee('भारत की राजधानी क्या है?', false);
    }

    public function test_create_form_renders_for_both_quiz_types(): void
    {
        $admin = User::factory()->admin()->create();
        $bilingualQuiz = $this->makeBilingualQuiz();
        $legacyQuiz = $this->makeLegacyQuiz();

        $this->actingAs($admin, 'web')->get(route('admin.questions.create', ['quiz_id' => $bilingualQuiz->id]))->assertOk();
        $this->actingAs($admin, 'web')->get(route('admin.questions.create', ['quiz_id' => $legacyQuiz->id]))->assertOk();
    }

    public function test_edit_form_renders_for_a_bilingual_question(): void
    {
        $admin = User::factory()->admin()->create();
        $quiz = $this->makeBilingualQuiz();
        $question = Question::create(['quiz_id' => $quiz->id, 'question_text' => '', 'points' => 10, 'sort_order' => 0]);
        QuestionTranslation::create(['question_id' => $question->id, 'language_code' => 'en', 'question_text' => 'English text.']);

        $this->actingAs($admin, 'web')
            ->get(route('admin.questions.edit', $question))
            ->assertOk()
            ->assertSee('English text.', false);
    }

    public function test_edit_form_renders_for_a_legacy_question(): void
    {
        $admin = User::factory()->admin()->create();
        $quiz = $this->makeLegacyQuiz();
        $question = Question::create(['quiz_id' => $quiz->id, 'question_text' => 'Legacy text.', 'points' => 10, 'sort_order' => 0]);

        $this->actingAs($admin, 'web')
            ->get(route('admin.questions.edit', $question))
            ->assertOk()
            ->assertSee('Legacy text.', false);
    }

    public function test_correct_option_out_of_range_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();
        $quiz = $this->makeLegacyQuiz();

        $this->actingAs($admin, 'web')->post(route('admin.questions.store'), [
            'quiz_id' => $quiz->id,
            'question_text' => 'Q?',
            'points' => 10,
            'sort_order' => 0,
            'options' => ['A', 'B', 'C', 'D'],
            // Only indices 0-3 exist — 4 is out of range and must be
            // rejected, not silently create a question where no option is
            // ever is_correct.
            'correct_option' => 4,
        ])->assertSessionHasErrors('correct_option');

        $this->assertSame(0, Question::count());
    }

    public function test_bilingual_correct_option_out_of_range_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();
        $quiz = $this->makeBilingualQuiz();

        $this->actingAs($admin, 'web')->post(route('admin.questions.store'), [
            'quiz_id' => $quiz->id,
            'points' => 10,
            'sort_order' => 0,
            'correct_option' => 4,
            'translations' => [
                'en' => ['question_text' => 'Q?', 'options' => ['A', 'B', 'C', 'D']],
                'hi' => ['question_text' => '', 'options' => ['', '', '', '']],
            ],
        ])->assertSessionHasErrors('correct_option');

        $this->assertSame(0, Question::count());
    }

    public function test_editing_a_question_preserves_option_ids_used_by_past_attempt_answers(): void
    {
        $admin = User::factory()->admin()->create();
        $quiz = $this->makeLegacyQuiz();
        $question = Question::create(['quiz_id' => $quiz->id, 'question_text' => 'Original?', 'points' => 10, 'sort_order' => 0]);
        $optionA = Option::create(['question_id' => $question->id, 'option_text' => 'A', 'is_correct' => true]);
        Option::create(['question_id' => $question->id, 'option_text' => 'B', 'is_correct' => false]);

        $user = User::factory()->create();
        $attempt = QuizAttempt::create(['user_id' => $user->id, 'quiz_id' => $quiz->id, 'status' => 'completed', 'started_at' => now(), 'completed_at' => now(), 'total_questions' => 1]);
        $answer = QuizAttemptAnswer::create([
            'quiz_attempt_id' => $attempt->id, 'question_id' => $question->id,
            'selected_option_id' => $optionA->id, 'correct_option_id' => $optionA->id,
            'is_correct' => true, 'answered_at' => now(),
        ]);

        $this->actingAs($admin, 'web')->put(route('admin.questions.update', $question), [
            'quiz_id' => $quiz->id,
            'question_text' => 'Edited wording?',
            'explanation' => null,
            'points' => 10,
            'sort_order' => 0,
            'options' => ['A edited', 'B', 'C', 'D'],
            'correct_option' => 0,
        ])->assertRedirect();

        $this->assertSame($optionA->id, $optionA->fresh()->id, 'Option A must keep the same row.');
        $this->assertSame('A edited', $optionA->fresh()->option_text);
        $this->assertNotNull($answer->fresh()->selected_option_id, 'Editing a question must not null out historical answer references.');
        $this->assertSame($optionA->id, $answer->fresh()->selected_option_id);
        $this->assertCount(4, $question->options()->get());
    }

    public function test_index_filter_finds_bilingual_questions_missing_hindi(): void
    {
        $quiz = $this->makeBilingualQuiz();
        $complete = Question::create(['quiz_id' => $quiz->id, 'question_text' => '', 'points' => 10, 'sort_order' => 0]);
        QuestionTranslation::create(['question_id' => $complete->id, 'language_code' => 'en', 'question_text' => 'Complete EN']);
        QuestionTranslation::create(['question_id' => $complete->id, 'language_code' => 'hi', 'question_text' => 'Complete HI']);

        $missing = Question::create(['quiz_id' => $quiz->id, 'question_text' => '', 'points' => 10, 'sort_order' => 1]);
        QuestionTranslation::create(['question_id' => $missing->id, 'language_code' => 'en', 'question_text' => 'Missing HI EN only']);

        $admin = User::factory()->admin()->create();
        $response = $this->actingAs($admin, 'web')->get(route('admin.questions.index', ['translation_status' => 'hi_missing']));

        $response->assertSee('Missing HI EN only');
        $response->assertDontSee('Complete EN');
    }
}
