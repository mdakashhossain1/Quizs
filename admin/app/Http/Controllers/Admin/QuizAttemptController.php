<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
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

        if ($request->filled('category_id')) {
            $categoryId = $request->category_id;
            $query->whereHas('quiz', fn ($q) => $q->where('category_id', $categoryId));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('user')) {
            $search = $request->user;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('started_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('started_at', '<=', $request->date_to);
        }

        $attempts = $query->latest('started_at')->paginate(15)->withQueryString();
        $quizzes = Quiz::orderBy('title')->get();
        $categories = Category::orderBy('name')->get();

        return view('admin.quiz-attempts.index', compact('attempts', 'quizzes', 'categories'));
    }

    public function show(QuizAttempt $quizAttempt): View
    {
        $quizAttempt->load([
            'user',
            'quiz.questions' => fn ($q) => $q->orderBy('sort_order'),
            'quiz.questions.options',
            'answers',
        ]);

        return view('admin.quiz-attempts.show', compact('quizAttempt'));
    }

    public function destroy(QuizAttempt $quizAttempt): RedirectResponse
    {
        $quizAttempt->delete();
        return redirect()->route('admin.quiz-attempts.index')->with('success', 'Quiz attempt log removed.');
    }
}
