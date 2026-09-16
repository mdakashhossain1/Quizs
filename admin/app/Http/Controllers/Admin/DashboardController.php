<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_users' => User::count(),
            'total_categories' => Category::count(),
            'total_quizzes' => Quiz::count(),
            'total_questions' => Question::count(),
            'total_attempts' => QuizAttempt::count(),
        ];

        $recentAttempts = QuizAttempt::with(['user', 'quiz'])
            ->latest('completed_at')
            ->limit(7)
            ->get();

        $recentUsers = User::latest()
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentAttempts', 'recentUsers'));
    }
}
