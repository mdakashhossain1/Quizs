<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Option;
use App\Models\OptionTranslation;
use App\Models\Question;
use App\Models\QuestionTranslation;
use App\Models\Quiz;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class QuestionController extends Controller
{
    public function index(Request $request): View
    {
        $query = Question::with(['quiz', 'options', 'translations']);

        if ($request->filled('quiz_id')) {
            $query->where('quiz_id', $request->quiz_id);
        }

        // Roadmap §14: a search must find a question by either language's
        // text and still resolve to the one parent question record.
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('question_text', 'like', "%{$search}%")
                    ->orWhereHas('translations', fn ($t) => $t->where('question_text', 'like', "%{$search}%"));
            });
        }

        // Roadmap §6: surface bilingual questions still missing a translation.
        if ($request->filled('translation_status')) {
            match ($request->translation_status) {
                'hi_missing' => $query->whereHas('quiz', fn ($q) => $q->whereNull('language'))
                    ->whereDoesntHave('translations', fn ($t) => $t->where('language_code', 'hi')),
                'en_missing' => $query->whereHas('quiz', fn ($q) => $q->whereNull('language'))
                    ->whereDoesntHave('translations', fn ($t) => $t->where('language_code', 'en')),
                default => null,
            };
        }

        $questions = $query->orderBy('sort_order')->latest()->paginate(15)->withQueryString();

        if ($request->ajax()) {
            return view('admin.questions._table', compact('questions'));
        }

        $quizzes = Quiz::orderBy('title')->get();

        return view('admin.questions.index', compact('questions', 'quizzes'));
    }

    public function create(Request $request): View
    {
        $quizzes = Quiz::where('is_active', true)->orderBy('title')->get();
        $selectedQuizId = $request->query('quiz_id');
        return view('admin.questions.create', compact('quizzes', 'selectedQuizId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $quiz = Quiz::findOrFail($request->input('quiz_id'));

        return $quiz->isBilingual()
            ? $this->storeBilingual($request, $quiz)
            : $this->storeLegacy($request, $quiz);
    }

    private function storeLegacy(Request $request, Quiz $quiz): RedirectResponse
    {
        $validated = $request->validate([
            'quiz_id' => ['required', 'exists:quizzes,id'],
            'question_text' => ['required', 'string'],
            'explanation' => ['nullable', 'string'],
            'points' => ['required', 'integer', 'min:1'],
            'sort_order' => ['required', 'integer'],
            'options' => ['required', 'array', 'min:2'],
            'options.*' => ['required', 'string'],
            'correct_option' => ['required', 'integer'],
        ]);

        $this->assertCorrectOptionIsValid((int) $validated['correct_option'], $validated['options']);

        $question = Question::create([
            'quiz_id' => $validated['quiz_id'],
            'question_text' => $validated['question_text'],
            'explanation' => $validated['explanation'],
            'points' => $validated['points'],
            'sort_order' => $validated['sort_order'],
        ]);

        foreach ($validated['options'] as $index => $optionText) {
            if (trim($optionText) !== '') {
                Option::create([
                    'question_id' => $question->id,
                    'option_text' => $optionText,
                    'is_correct' => ((int) $validated['correct_option'] === (int) $index),
                ]);
            }
        }

        return redirect()->route('admin.questions.index', ['quiz_id' => $question->quiz_id])
            ->with('success', 'Question and choices added successfully.');
    }

    /**
     * English is required so a bilingual question always has at least the
     * fallback-language content (roadmap §16); Hindi may be saved
     * incomplete/draft and completed later (roadmap §7).
     */
    private function storeBilingual(Request $request, Quiz $quiz): RedirectResponse
    {
        $validated = $request->validate([
            'quiz_id' => ['required', 'exists:quizzes,id'],
            'points' => ['required', 'integer', 'min:1'],
            'sort_order' => ['required', 'integer'],
            'correct_option' => ['required', 'integer'],
            'translations.en.question_text' => ['required', 'string'],
            'translations.en.explanation' => ['nullable', 'string'],
            'translations.en.options' => ['required', 'array', 'min:2'],
            'translations.en.options.*' => ['required', 'string'],
            'translations.hi.question_text' => ['nullable', 'string'],
            'translations.hi.explanation' => ['nullable', 'string'],
            'translations.hi.options' => ['nullable', 'array'],
            'translations.hi.options.*' => ['nullable', 'string'],
        ]);

        // English is the language every bilingual option must have, so it's
        // the one that determines whether the chosen index is real.
        $this->assertCorrectOptionIsValid((int) $validated['correct_option'], $validated['translations']['en']['options']);

        $question = Question::create([
            'quiz_id' => $quiz->id,
            // Base columns are unused for a bilingual question — all text
            // lives in question_translations — but stay NOT NULL, so an
            // empty string here rather than altering that constraint.
            'question_text' => '',
            'points' => $validated['points'],
            'sort_order' => $validated['sort_order'],
        ]);

        $this->syncTranslations($question, $validated['translations']);
        $this->createBilingualOptions($question, $validated['translations'], (int) $validated['correct_option']);

        return redirect()->route('admin.questions.index', ['quiz_id' => $question->quiz_id])
            ->with('success', 'Bilingual question and choices added successfully.');
    }

    public function edit(Question $question): View
    {
        $question->load(['options.translations', 'translations', 'quiz']);
        $quizzes = Quiz::orderBy('title')->get();
        return view('admin.questions.edit', compact('question', 'quizzes'));
    }

    public function update(Request $request, Question $question): RedirectResponse
    {
        $quiz = Quiz::findOrFail($request->input('quiz_id'));

        return $quiz->isBilingual()
            ? $this->updateBilingual($request, $question, $quiz)
            : $this->updateLegacy($request, $question);
    }

    private function updateLegacy(Request $request, Question $question): RedirectResponse
    {
        $validated = $request->validate([
            'quiz_id' => ['required', 'exists:quizzes,id'],
            'question_text' => ['required', 'string'],
            'explanation' => ['nullable', 'string'],
            'points' => ['required', 'integer', 'min:1'],
            'sort_order' => ['required', 'integer'],
            'options' => ['required', 'array', 'min:2'],
            'options.*' => ['required', 'string'],
            'correct_option' => ['required', 'integer'],
        ]);

        $this->assertCorrectOptionIsValid((int) $validated['correct_option'], $validated['options']);

        $question->update([
            'quiz_id' => $validated['quiz_id'],
            'question_text' => $validated['question_text'],
            'explanation' => $validated['explanation'],
            'points' => $validated['points'],
            'sort_order' => $validated['sort_order'],
        ]);

        $this->syncOptionsByPosition($question, $validated['options'], (int) $validated['correct_option']);

        return redirect()->route('admin.questions.index', ['quiz_id' => $question->quiz_id])
            ->with('success', 'Question updated successfully.');
    }

    private function updateBilingual(Request $request, Question $question, Quiz $quiz): RedirectResponse
    {
        $validated = $request->validate([
            'quiz_id' => ['required', 'exists:quizzes,id'],
            'points' => ['required', 'integer', 'min:1'],
            'sort_order' => ['required', 'integer'],
            'correct_option' => ['required', 'integer'],
            'translations.en.question_text' => ['required', 'string'],
            'translations.en.explanation' => ['nullable', 'string'],
            'translations.en.options' => ['required', 'array', 'min:2'],
            'translations.en.options.*' => ['required', 'string'],
            'translations.hi.question_text' => ['nullable', 'string'],
            'translations.hi.explanation' => ['nullable', 'string'],
            'translations.hi.options' => ['nullable', 'array'],
            'translations.hi.options.*' => ['nullable', 'string'],
        ]);

        $this->assertCorrectOptionIsValid((int) $validated['correct_option'], $validated['translations']['en']['options']);

        $question->update([
            'quiz_id' => $quiz->id,
            'points' => $validated['points'],
            'sort_order' => $validated['sort_order'],
        ]);

        $this->syncTranslations($question, $validated['translations']);
        $this->syncBilingualOptionsByPosition($question, $validated['translations'], (int) $validated['correct_option']);

        return redirect()->route('admin.questions.index', ['quiz_id' => $question->quiz_id])
            ->with('success', 'Bilingual question updated successfully.');
    }

    /**
     * The chosen index must point at an option that will actually exist
     * once blank trailing slots are dropped — otherwise every Option ends
     * up is_correct=false and every submitted answer for this question
     * silently grades as wrong.
     */
    private function assertCorrectOptionIsValid(int $correctIndex, array $optionTexts): void
    {
        if (trim($optionTexts[$correctIndex] ?? '') === '') {
            throw ValidationException::withMessages([
                'correct_option' => 'The correct answer must point to a filled-in option.',
            ]);
        }
    }

    /** @param array{en?: array, hi?: array} $translations */
    private function syncTranslations(Question $question, array $translations): void
    {
        foreach (['en', 'hi'] as $lang) {
            $text = trim($translations[$lang]['question_text'] ?? '');
            if ($text === '') {
                // Allows saving Hindi as a draft/incomplete (roadmap §7)
                // without writing an empty placeholder translation row.
                QuestionTranslation::where('question_id', $question->id)->where('language_code', $lang)->delete();
                continue;
            }

            QuestionTranslation::updateOrCreate(
                ['question_id' => $question->id, 'language_code' => $lang],
                ['question_text' => $text, 'explanation' => $translations[$lang]['explanation'] ?? null],
            );
        }
    }

    /** Create-only — used on the initial store, where there's no history to preserve. */
    private function createBilingualOptions(Question $question, array $translations, int $correctIndex): void
    {
        $enOptions = $translations['en']['options'] ?? [];
        $hiOptions = $translations['hi']['options'] ?? [];
        $optionCount = max(count($enOptions), count($hiOptions));

        for ($i = 0; $i < $optionCount; $i++) {
            $enText = trim($enOptions[$i] ?? '');
            $hiText = trim($hiOptions[$i] ?? '');
            if ($enText === '' && $hiText === '') {
                continue;
            }

            $option = Option::create([
                'question_id' => $question->id,
                'option_text' => '',
                'is_correct' => $correctIndex === $i,
            ]);

            if ($enText !== '') {
                OptionTranslation::create(['option_id' => $option->id, 'language_code' => 'en', 'option_text' => $enText]);
            }
            if ($hiText !== '') {
                OptionTranslation::create(['option_id' => $option->id, 'language_code' => 'hi', 'option_text' => $hiText]);
            }
        }
    }

    /**
     * Updates existing Option rows in place by position instead of
     * deleting and recreating them: quiz_attempt_answers references options
     * by id with nullOnDelete, so replacing a still-used option would
     * silently erase which choice past users actually selected.
     */
    private function syncOptionsByPosition(Question $question, array $optionTexts, int $correctIndex): void
    {
        $existing = $question->options()->orderBy('id')->get()->values();

        foreach ($optionTexts as $index => $text) {
            $text = trim($text);
            $current = $existing[$index] ?? null;

            if ($text === '') {
                $current?->delete();
                continue;
            }

            if ($current) {
                $current->update(['option_text' => $text, 'is_correct' => $correctIndex === $index]);
            } else {
                Option::create([
                    'question_id' => $question->id,
                    'option_text' => $text,
                    'is_correct' => $correctIndex === $index,
                ]);
            }
        }

        foreach ($existing->slice(count($optionTexts)) as $stale) {
            $stale->delete();
        }
    }

    /** @param array{en?: array, hi?: array} $translations */
    private function syncBilingualOptionsByPosition(Question $question, array $translations, int $correctIndex): void
    {
        $enOptions = $translations['en']['options'] ?? [];
        $hiOptions = $translations['hi']['options'] ?? [];
        $optionCount = max(count($enOptions), count($hiOptions));
        $existing = $question->options()->orderBy('id')->get()->values();

        for ($i = 0; $i < $optionCount; $i++) {
            $enText = trim($enOptions[$i] ?? '');
            $hiText = trim($hiOptions[$i] ?? '');
            $current = $existing[$i] ?? null;

            if ($enText === '' && $hiText === '') {
                $current?->delete();
                continue;
            }

            $option = $current ?? Option::create(['question_id' => $question->id, 'option_text' => '', 'is_correct' => false]);
            $option->update(['is_correct' => $correctIndex === $i]);

            foreach (['en' => $enText, 'hi' => $hiText] as $lang => $text) {
                if ($text !== '') {
                    OptionTranslation::updateOrCreate(
                        ['option_id' => $option->id, 'language_code' => $lang],
                        ['option_text' => $text],
                    );
                } else {
                    OptionTranslation::where('option_id', $option->id)->where('language_code', $lang)->delete();
                }
            }
        }

        foreach ($existing->slice($optionCount) as $stale) {
            $stale->delete();
        }
    }

    public function destroy(Question $question): RedirectResponse
    {
        $quizId = $question->quiz_id;
        $question->delete();
        return redirect()->route('admin.questions.index', ['quiz_id' => $quizId])
            ->with('success', 'Question deleted successfully.');
    }
}
