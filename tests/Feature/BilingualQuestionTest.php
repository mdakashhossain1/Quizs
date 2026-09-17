<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Option;
use App\Models\OptionTranslation;
use App\Models\Question;
use App\Models\QuestionTranslation;
use App\Models\Quiz;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * bilingual_question_management_prd.md: one Question ID carries an English
 * and a Hindi translation rather than being duplicated, the correct answer
 * is the same physical Option row regardless of language, and legacy
 * single-language quizzes (untouched per the explicit decision to leave
 * existing content as-is) must keep behaving exactly as before.
 */
class BilingualQuestionTest extends TestCase
{
    use RefreshDatabase;

    private function makeCategory(): Category
    {
        return Category::create([
            'name' => 'Test Category', 'slug' => 'test-category-' . uniqid(), 'color' => '#000',
            'is_active' => true, 'sort_order' => 1,
        ]);
    }

    public function test_legacy_single_language_quiz_question_is_unaffected(): void
    {
        $quiz = Quiz::create([
            'category_id' => $this->makeCategory()->id, 'language' => 'en', 'title' => 'Legacy Quiz',
            'slug' => 'legacy-quiz-' . uniqid(), 'duration_minutes' => 5, 'passing_percentage' => 50,
            'difficulty' => 'easy', 'is_active' => true, 'sort_order' => 1,
        ]);
        $question = Question::create([
            'quiz_id' => $quiz->id, 'question_text' => 'What is 2+2?', 'explanation' => 'Basic math.', 'points' => 10, 'sort_order' => 0,
        ]);

        $this->assertFalse($quiz->isBilingual());

        $content = $question->contentFor('hi');

        $this->assertSame('What is 2+2?', $content['question_text']);
        $this->assertSame('Basic math.', $content['explanation']);
        $this->assertSame('en', $content['served_language']);
        $this->assertFalse($content['translation_fallback']);
    }

    public function test_bilingual_question_serves_the_requested_language(): void
    {
        $quiz = Quiz::create([
            'category_id' => $this->makeCategory()->id, 'language' => null, 'title' => 'Bilingual Quiz',
            'slug' => 'bilingual-quiz-' . uniqid(), 'duration_minutes' => 5, 'passing_percentage' => 50,
            'difficulty' => 'easy', 'is_active' => true, 'sort_order' => 1,
        ]);
        $question = Question::create(['quiz_id' => $quiz->id, 'question_text' => '', 'points' => 10, 'sort_order' => 0]);
        QuestionTranslation::create(['question_id' => $question->id, 'language_code' => 'en', 'question_text' => 'What is the capital of India?', 'explanation' => 'New Delhi is the capital.']);
        QuestionTranslation::create(['question_id' => $question->id, 'language_code' => 'hi', 'question_text' => 'भारत की राजधानी क्या है?', 'explanation' => 'नई दिल्ली भारत की राजधानी है।']);

        $question->load('translations');

        $this->assertTrue($quiz->isBilingual());
        $this->assertSame('भारत की राजधानी क्या है?', $question->contentFor('hi')['question_text']);
        $this->assertSame('What is the capital of India?', $question->contentFor('en')['question_text']);
        $this->assertFalse($question->contentFor('hi')['translation_fallback']);
    }

    public function test_missing_translation_falls_back_to_the_configured_fallback_language(): void
    {
        $quiz = Quiz::create([
            'category_id' => $this->makeCategory()->id, 'language' => null, 'title' => 'Bilingual Quiz',
            'slug' => 'bilingual-quiz-' . uniqid(), 'duration_minutes' => 5, 'passing_percentage' => 50,
            'difficulty' => 'easy', 'is_active' => true, 'sort_order' => 1,
        ]);
        $question = Question::create(['quiz_id' => $quiz->id, 'question_text' => '', 'points' => 10, 'sort_order' => 0]);
        QuestionTranslation::create(['question_id' => $question->id, 'language_code' => 'en', 'question_text' => 'English only question.']);
        $question->load('translations');

        $content = $question->contentFor('hi');

        $this->assertSame('English only question.', $content['question_text']);
        $this->assertSame('en', $content['served_language']);
        $this->assertTrue($content['translation_fallback'], 'A served language different from the requested one must be flagged as a fallback.');
    }

    public function test_correct_option_is_identical_across_languages(): void
    {
        $quiz = Quiz::create([
            'category_id' => $this->makeCategory()->id, 'language' => null, 'title' => 'Bilingual Quiz',
            'slug' => 'bilingual-quiz-' . uniqid(), 'duration_minutes' => 5, 'passing_percentage' => 50,
            'difficulty' => 'easy', 'is_active' => true, 'sort_order' => 1,
        ]);
        $question = Question::create(['quiz_id' => $quiz->id, 'question_text' => '', 'points' => 10, 'sort_order' => 0]);

        $optionB = Option::create(['question_id' => $question->id, 'option_text' => '', 'is_correct' => true]);
        Option::create(['question_id' => $question->id, 'option_text' => '', 'is_correct' => false]);

        OptionTranslation::create(['option_id' => $optionB->id, 'language_code' => 'en', 'option_text' => 'New Delhi']);
        OptionTranslation::create(['option_id' => $optionB->id, 'language_code' => 'hi', 'option_text' => 'नई दिल्ली']);
        $optionB->load(['translations', 'question.quiz']);

        $this->assertTrue($optionB->is_correct);
        $this->assertSame('New Delhi', $optionB->textFor('en'));
        $this->assertSame('नई दिल्ली', $optionB->textFor('hi'));

        // The identity of "the correct option" never changes between
        // languages — only its display text does (roadmap §2).
        $this->assertTrue($optionB->fresh()->is_correct);
    }

    public function test_has_complete_translations_detects_missing_language(): void
    {
        $quiz = Quiz::create([
            'category_id' => $this->makeCategory()->id, 'language' => null, 'title' => 'Bilingual Quiz',
            'slug' => 'bilingual-quiz-' . uniqid(), 'duration_minutes' => 5, 'passing_percentage' => 50,
            'difficulty' => 'easy', 'is_active' => true, 'sort_order' => 1,
        ]);
        $question = Question::create(['quiz_id' => $quiz->id, 'question_text' => '', 'points' => 10, 'sort_order' => 0]);
        QuestionTranslation::create(['question_id' => $question->id, 'language_code' => 'en', 'question_text' => 'English text.']);
        $question->load('translations');

        $this->assertFalse($question->hasCompleteTranslations(['en', 'hi']));

        QuestionTranslation::create(['question_id' => $question->id, 'language_code' => 'hi', 'question_text' => 'हिंदी पाठ।']);
        $question->load('translations');

        $this->assertTrue($question->hasCompleteTranslations(['en', 'hi']));
    }
}
