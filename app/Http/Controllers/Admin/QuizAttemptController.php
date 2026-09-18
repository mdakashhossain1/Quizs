<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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

    /**
     * Numbers-only result summary as a PDF — deliberately loads none of the
     * question/option/answer relations `show()` uses, so there's no way for
     * per-question detail to leak into this export.
     */
    public function summaryPdf(QuizAttempt $quizAttempt): Response
    {
        $quizAttempt->load(['user', 'quiz']);

        $pdf = Pdf::loadView('admin.quiz-attempts.summary-pdf', compact('quizAttempt'));

        return $pdf->download("quiz-attempt-{$quizAttempt->id}-summary.pdf");
    }

    /**
     * Same numbers-only summary as CSV.
     */
    public function summaryCsv(QuizAttempt $quizAttempt): Response
    {
        $quizAttempt->load(['user', 'quiz']);

        $rows = [
            ['User', $quizAttempt->user->name ?? 'Unknown'],
            ['Email', $quizAttempt->user->email ?? '-'],
            ['Quiz', $quizAttempt->quiz->title ?? 'Deleted Quiz'],
            ['Attempt ID', $quizAttempt->id],
            ['Status', ucfirst(str_replace('_', ' ', $quizAttempt->status))],
            ['Played At', $quizAttempt->completed_at?->format('Y-m-d H:i') ?? $quizAttempt->started_at?->format('Y-m-d H:i') ?? '-'],
            [],
            ['Field', 'Value'],
            ['Total Questions', $quizAttempt->total_questions],
            ['Correct Answers', $quizAttempt->correct_answers],
            ['Wrong Answers', $quizAttempt->wrong_answers],
            ['Unanswered', $quizAttempt->unanswered_questions],
            ['Score', $quizAttempt->score],
            ['Accuracy (%)', $quizAttempt->accuracy],
        ];

        $handle = fopen('php://temp', 'w+');
        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=quiz-attempt-{$quizAttempt->id}-summary.csv",
        ]);
    }
}
