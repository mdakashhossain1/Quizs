<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuizAttemptController extends Controller
{
    public function index(Request $request): View
    {
        $query = QuizAttempt::with(['user', 'quiz.category']);

        if ($request->filled('quiz_id')) {
            $query->where('quiz_id', $request->quiz_id);
        }

        $attempts = $query->latest('completed_at')->paginate(15);
        $quizzes = Quiz::orderBy('title')->get();

        return view('admin.quiz-attempts.index', compact('attempts', 'quizzes'));
    }

    public function show(QuizAttempt $quizAttempt): View
    {
        $quizAttempt->load(['user', 'quiz.questions.options']);
        return view('admin.quiz-attempts.show', compact('quizAttempt'));
    }

    public function destroy(QuizAttempt $quizAttempt): RedirectResponse
    {
        $quizAttempt->delete();
        return redirect()->route('admin.quiz-attempts.index')->with('success', 'Quiz attempt log removed.');
    }
}
