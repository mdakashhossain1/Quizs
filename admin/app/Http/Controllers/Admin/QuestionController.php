<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Option;
use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuestionController extends Controller
{
    public function index(Request $request): View
    {
        $query = Question::with(['quiz', 'options']);

        if ($request->filled('quiz_id')) {
            $query->where('quiz_id', $request->quiz_id);
        }

        if ($request->filled('search')) {
            $query->where('question_text', 'like', '%' . $request->search . '%');
        }

        $questions = $query->orderBy('sort_order')->latest()->paginate(15);
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
                    'is_correct' => ((int)$validated['correct_option'] === (int)$index),
                ]);
            }
        }

        return redirect()->route('admin.questions.index', ['quiz_id' => $question->quiz_id])
            ->with('success', 'Question and choices added successfully.');
    }

    public function edit(Question $question): View
    {
        $question->load('options');
        $quizzes = Quiz::orderBy('title')->get();
        return view('admin.questions.edit', compact('question', 'quizzes'));
    }

    public function update(Request $request, Question $question): RedirectResponse
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

        $question->update([
            'quiz_id' => $validated['quiz_id'],
            'question_text' => $validated['question_text'],
            'explanation' => $validated['explanation'],
            'points' => $validated['points'],
            'sort_order' => $validated['sort_order'],
        ]);

        // Recreate or update options
        $question->options()->delete();

        foreach ($validated['options'] as $index => $optionText) {
            if (trim($optionText) !== '') {
                Option::create([
                    'question_id' => $question->id,
                    'option_text' => $optionText,
                    'is_correct' => ((int)$validated['correct_option'] === (int)$index),
                ]);
            }
        }

        return redirect()->route('admin.questions.index', ['quiz_id' => $question->quiz_id])
            ->with('success', 'Question updated successfully.');
    }

    public function destroy(Question $question): RedirectResponse
    {
        $quizId = $question->quiz_id;
        $question->delete();
        return redirect()->route('admin.questions.index', ['quiz_id' => $quizId])
            ->with('success', 'Question deleted successfully.');
    }
}
