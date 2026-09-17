<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Option;
use App\Models\OptionTranslation;
use App\Models\Question;
use App\Models\QuestionTranslation;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * bilingual_question_management_prd.md §9, §16: the API serves whichever
 * language is requested for a bilingual quiz, falls back gracefully when a
 * translation is missing, and a legacy single-language quiz keeps behaving
 * exactly as it did before this feature existed.
 */
class BilingualQuizApiTest extends TestCase
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
        $quiz = Quiz::create([
            'category_id' => $this->makeCategory()->id, 'language' => null, 'title' => 'Bilingual Quiz',
            'slug' => 'bilingual-quiz-' . uniqid(), 'duration_minutes' => 5, 'passing_percentage' => 50,
            'difficulty' => 'easy', 'is_active' => true, 'sort_order' => 1,
        ]);

        $question = Question::create(['quiz_id' => $quiz->id, 'question_text' => '', 'points' => 10, 'sort_order' => 0]);
        QuestionTranslation::create(['question_id' => $question->id, 'language_code' => 'en', 'question_text' => 'What is the capital of India?', 'explanation' => 'New Delhi is the capital.']);
        QuestionTranslation::create(['question_id' => $question->id, 'language_code' => 'hi', 'question_text' => 'भारत की राजधानी क्या है?', 'explanation' => 'नई दिल्ली भारत की राजधानी है।']);

        $optionA = Option::create(['question_id' => $question->id, 'option_text' => '', 'is_correct' => false]);
        OptionTranslation::create(['option_id' => $optionA->id, 'language_code' => 'en', 'option_text' => 'Mumbai']);
        OptionTranslation::create(['option_id' => $optionA->id, 'language_code' => 'hi', 'option_text' => 'मुंबई']);

        $optionB = Option::create(['question_id' => $question->id, 'option_text' => '', 'is_correct' => true]);
        OptionTranslation::create(['option_id' => $optionB->id, 'language_code' => 'en', 'option_text' => 'New Delhi']);
        OptionTranslation::create(['option_id' => $optionB->id, 'language_code' => 'hi', 'option_text' => 'नई दिल्ली']);

        return $quiz;
    }

    public function test_quiz_detail_serves_hindi_when_requested(): void
    {
        $quiz = $this->makeBilingualQuiz();
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/quizzes/{$quiz->id}?lang=hi")
            ->assertOk()
            ->json();

        $this->assertSame('hi', $response['requested_language']);
        $question = $response['quiz']['questions'][0];
        $this->assertSame('भारत की राजधानी क्या है?', $question['question_text']);
        $this->assertFalse($question['translation_fallback']);

        $optionTexts = collect($question['options'])->pluck('option_text')->all();
        $this->assertContains('नई दिल्ली', $optionTexts);
        $this->assertContains('मुंबई', $optionTexts);
    }

    public function test_quiz_detail_serves_english_by_default(): void
    {
        $quiz = $this->makeBilingualQuiz();
        $user = User::factory()->create();

        $question = $this->actingAs($user, 'sanctum')
            ->getJson("/api/quizzes/{$quiz->id}")
            ->assertOk()
            ->json('quiz.questions.0');

        $this->assertSame('What is the capital of India?', $question['question_text']);
    }

    public function test_quiz_detail_falls_back_to_english_when_hindi_translation_missing(): void
    {
        $quiz = Quiz::create([
            'category_id' => $this->makeCategory()->id, 'language' => null, 'title' => 'Partial Bilingual Quiz',
            'slug' => 'partial-bilingual-' . uniqid(), 'duration_minutes' => 5, 'passing_percentage' => 50,
            'difficulty' => 'easy', 'is_active' => true, 'sort_order' => 1,
        ]);
        $question = Question::create(['quiz_id' => $quiz->id, 'question_text' => '', 'points' => 10, 'sort_order' => 0]);
        QuestionTranslation::create(['question_id' => $question->id, 'language_code' => 'en', 'question_text' => 'English only question.']);
        $user = User::factory()->create();

        $responseQuestion = $this->actingAs($user, 'sanctum')
            ->getJson("/api/quizzes/{$quiz->id}?lang=hi")
            ->assertOk()
            ->json('quiz.questions.0');

        $this->assertSame('English only question.', $responseQuestion['question_text']);
        $this->assertTrue($responseQuestion['translation_fallback']);
    }

    public function test_legacy_quiz_detail_is_unaffected_by_lang_param(): void
    {
        $category = $this->makeCategory();
        $quiz = Quiz::create([
            'category_id' => $category->id, 'language' => 'en', 'title' => 'Legacy English Quiz',
            'slug' => 'legacy-en-' . uniqid(), 'duration_minutes' => 5, 'passing_percentage' => 50,
            'difficulty' => 'easy', 'is_active' => true, 'sort_order' => 1,
        ]);
        Question::create(['quiz_id' => $quiz->id, 'question_text' => 'Legacy question text.', 'points' => 10, 'sort_order' => 0]);
        $user = User::factory()->create();

        $question = $this->actingAs($user, 'sanctum')
            ->getJson("/api/quizzes/{$quiz->id}?lang=hi")
            ->assertOk()
            ->json('quiz.questions.0');

        $this->assertSame('Legacy question text.', $question['question_text']);
    }

    public function test_bilingual_quizzes_appear_regardless_of_requested_category_language(): void
    {
        $category = $this->makeCategory();
        $bilingualQuiz = Quiz::create([
            'category_id' => $category->id, 'language' => null, 'title' => 'Bilingual Quiz',
            'slug' => 'bilingual-' . uniqid(), 'duration_minutes' => 5, 'passing_percentage' => 50,
            'difficulty' => 'easy', 'is_active' => true, 'sort_order' => 1,
        ]);
        $englishOnlyQuiz = Quiz::create([
            'category_id' => $category->id, 'language' => 'en', 'title' => 'English Only Quiz',
            'slug' => 'english-only-' . uniqid(), 'duration_minutes' => 5, 'passing_percentage' => 50,
            'difficulty' => 'easy', 'is_active' => true, 'sort_order' => 1,
        ]);

        $hindiTitles = collect(
            $this->getJson("/api/categories/{$category->id}/quizzes?language=hi")->json('quizzes')
        )->pluck('title');

        $this->assertContains('Bilingual Quiz', $hindiTitles);
        $this->assertNotContains('English Only Quiz', $hindiTitles);
    }

    public function test_starting_a_bilingual_quiz_records_the_language_used(): void
    {
        $quiz = $this->makeBilingualQuiz();
        $user = User::factory()->create();

        $attemptId = $this->actingAs($user, 'sanctum')
            ->postJson("/api/quizzes/{$quiz->id}/start?lang=hi")
            ->assertOk()
            ->json('attempt.id');

        $this->assertSame('hi', \App\Models\QuizAttempt::find($attemptId)->language_used);
    }
}
