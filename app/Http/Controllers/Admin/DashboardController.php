<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptAnswer;
use App\Models\User;
use App\Models\UserDailyProgress;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $range = $request->query('range', '7days');
        $data = $this->getDashboardData($range);

        return view('admin.dashboard', $data);
    }

    public function analytics(Request $request): JsonResponse
    {
        $range = $request->query('range', '7days');
        $data = $this->getDashboardData($range);

        return response()->json([
            'success' => true,
            'range' => $range,
            'stats' => $data['stats'],
            'charts' => $data['charts'],
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $range = $request->query('range', '30days');
        $data = $this->getDashboardData($range);
        $chartData = $data['charts'];

        $filename = "quizs_analytics_{$range}_" . now()->format('Y-m-d_His') . ".csv";

        return response()->streamDownload(function () use ($chartData, $range): void {
            $handle = fopen('php://output', 'w');
            // UTF-8 BOM for Excel
            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['Quizs Platform Analytics Report']);
            fputcsv($handle, ['Period:', strtoupper($range)]);
            fputcsv($handle, ['Generated At:', now()->toDateTimeString()]);
            fputcsv($handle, []);

            // Summary row
            fputcsv($handle, [
                'Date / Label',
                'Quiz Attempts',
                'Completed Attempts',
                'Questions Answered',
                'Correct Answers',
                'Wrong Answers',
                'Accuracy (%)',
                'Targets Completed'
            ]);

            $labels = $chartData['timeline']['labels'] ?? [];
            $attempts = $chartData['timeline']['attempts'] ?? [];
            $completed = $chartData['timeline']['completed_attempts'] ?? [];
            $qAnswered = $chartData['timeline']['questions_answered'] ?? [];
            $qCorrect = $chartData['timeline']['questions_correct'] ?? [];
            $qWrong = $chartData['timeline']['questions_wrong'] ?? [];
            $accuracy = $chartData['timeline']['accuracy_rate'] ?? [];
            $targets = $chartData['timeline']['targets_completed'] ?? [];

            foreach ($labels as $i => $label) {
                fputcsv($handle, [
                    $label,
                    $attempts[$i] ?? 0,
                    $completed[$i] ?? 0,
                    $qAnswered[$i] ?? 0,
                    $qCorrect[$i] ?? 0,
                    $qWrong[$i] ?? 0,
                    ($accuracy[$i] ?? 0) . '%',
                    $targets[$i] ?? 0,
                ]);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['Category Breakdown']);
            fputcsv($handle, ['Category Name', 'Total Questions', 'Total Plays']);

            $catLabels = $chartData['categories']['labels'] ?? [];
            $catQuestions = $chartData['categories']['questions'] ?? [];
            $catPlays = $chartData['categories']['attempts'] ?? [];

            foreach ($catLabels as $j => $cName) {
                fputcsv($handle, [
                    $cName,
                    $catQuestions[$j] ?? 0,
                    $catPlays[$j] ?? 0,
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    private function getDashboardData(string $range): array
    {
        $onlineTimeout = (int) config('quiz.online_timeout_seconds', 120);

        $now = now();
        $todayStart = $now->copy()->startOfDay();
        $todayEnd = $now->copy()->endOfDay();
        $yesterdayStart = $now->copy()->subDay()->startOfDay();
        $yesterdayEnd = $now->copy()->subDay()->endOfDay();

        // 1. Overall & Comparison Metrics
        $totalUsers = User::count();
        $newUsersToday = User::whereBetween('created_at', [$todayStart, $todayEnd])->count();
        $newUsersYesterday = User::whereBetween('created_at', [$yesterdayStart, $yesterdayEnd])->count();
        $usersGrowthPct = $this->calcPercentageChange($newUsersToday, $newUsersYesterday);

        $onlineUsers = User::where('last_active_at', '>=', now()->subSeconds($onlineTimeout))->count();
        $totalCategories = Category::count();
        $totalQuizzes = Quiz::count();
        $totalQuestions = Question::count();

        // Quiz Attempts metrics
        $totalAttempts = QuizAttempt::count();
        $attemptsToday = QuizAttempt::whereBetween('created_at', [$todayStart, $todayEnd])->count();
        $attemptsYesterday = QuizAttempt::whereBetween('created_at', [$yesterdayStart, $yesterdayEnd])->count();
        $attemptsGrowthPct = $this->calcPercentageChange($attemptsToday, $attemptsYesterday);

        $completedAttemptsToday = QuizAttempt::where('status', 'completed')
            ->whereBetween('completed_at', [$todayStart, $todayEnd])
            ->count();

        // Questions Answered metrics (from quiz_attempts columns attempted_questions / correct_answers)
        $totalQuestionsAnswered = (int) QuizAttempt::sum('attempted_questions');
        $totalCorrectAnswers = (int) QuizAttempt::sum('correct_answers');
        $totalWrongAnswers = (int) QuizAttempt::sum('wrong_answers');

        $questionsAnsweredToday = (int) QuizAttempt::whereBetween('created_at', [$todayStart, $todayEnd])->sum('attempted_questions');
        $questionsAnsweredYesterday = (int) QuizAttempt::whereBetween('created_at', [$yesterdayStart, $yesterdayEnd])->sum('attempted_questions');
        $questionsGrowthPct = $this->calcPercentageChange($questionsAnsweredToday, $questionsAnsweredYesterday);

        $overallAccuracy = $totalQuestionsAnswered > 0 
            ? round(($totalCorrectAnswers / $totalQuestionsAnswered) * 100, 1) 
            : 0;

        $accuracyToday = $questionsAnsweredToday > 0 
            ? round(((int) QuizAttempt::whereBetween('created_at', [$todayStart, $todayEnd])->sum('correct_answers') / $questionsAnsweredToday) * 100, 1) 
            : $overallAccuracy;

        // Daily Targets today
        $targetsCompletedToday = UserDailyProgress::where('date', $todayStart->toDateString())
            ->where('target_status', 'completed')
            ->count();

        $stats = [
            'total_users' => $totalUsers,
            'new_users_today' => $newUsersToday,
            'new_users_yesterday' => $newUsersYesterday,
            'users_growth_pct' => $usersGrowthPct,

            'online_users' => $onlineUsers,
            'total_categories' => $totalCategories,
            'total_quizzes' => $totalQuizzes,
            'total_questions' => $totalQuestions,

            'total_attempts' => $totalAttempts,
            'attempts_today' => $attemptsToday,
            'attempts_yesterday' => $attemptsYesterday,
            'attempts_growth_pct' => $attemptsGrowthPct,
            'completed_attempts_today' => $completedAttemptsToday,

            'total_questions_answered' => $totalQuestionsAnswered,
            'questions_answered_today' => $questionsAnsweredToday,
            'questions_answered_yesterday' => $questionsAnsweredYesterday,
            'questions_growth_pct' => $questionsGrowthPct,

            'overall_accuracy' => $overallAccuracy,
            'accuracy_today' => $accuracyToday,
            'targets_completed_today' => $targetsCompletedToday,
        ];

        // 2. Timeline Series Data according to Range
        $timeline = $this->buildTimelineData($range);

        // 3. Category Breakdown Data
        $categoriesData = $this->buildCategoryData();

        // 4. Daily Target Distribution
        $targetsData = $this->buildTargetData($range);

        $charts = [
            'timeline' => $timeline,
            'categories' => $categoriesData,
            'targets' => $targetsData,
        ];

        $recentAttempts = QuizAttempt::with(['user', 'quiz'])
            ->latest('completed_at')
            ->limit(7)
            ->get();

        $recentUsers = User::latest()
            ->limit(5)
            ->get();

        return [
            'range' => $range,
            'stats' => $stats,
            'charts' => $charts,
            'recentAttempts' => $recentAttempts,
            'recentUsers' => $recentUsers,
        ];
    }

    private function buildTimelineData(string $range): array
    {
        $now = now();
        $labels = [];
        $buckets = [];

        switch ($range) {
            case 'today':
                // Hourly intervals for today (00:00 to now)
                for ($h = 0; $h <= 23; $h++) {
                    $start = $now->copy()->startOfDay()->addHours($h);
                    $end = $start->copy()->endOfHour();
                    $labels[] = $start->format('H:00');
                    $buckets[] = [$start, $end];
                }
                break;

            case 'yesterday':
                // Hourly intervals for yesterday
                $y = $now->copy()->subDay();
                for ($h = 0; $h <= 23; $h++) {
                    $start = $y->copy()->startOfDay()->addHours($h);
                    $end = $start->copy()->endOfHour();
                    $labels[] = $start->format('H:00');
                    $buckets[] = [$start, $end];
                }
                break;

            case '30days':
                // Last 30 days daily
                for ($i = 29; $i >= 0; $i--) {
                    $day = $now->copy()->subDays($i);
                    $labels[] = $day->format('d M');
                    $buckets[] = [$day->copy()->startOfDay(), $day->copy()->endOfDay()];
                }
                break;

            case 'year':
                // Last 12 months
                for ($m = 11; $m >= 0; $m--) {
                    $month = $now->copy()->subMonths($m);
                    $labels[] = $month->format('M Y');
                    $buckets[] = [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()];
                }
                break;

            case '7days':
            default:
                // Last 7 days daily
                for ($i = 6; $i >= 0; $i--) {
                    $day = $now->copy()->subDays($i);
                    $labels[] = $day->format('D, d M');
                    $buckets[] = [$day->copy()->startOfDay(), $day->copy()->endOfDay()];
                }
                break;
        }

        $attemptsData = [];
        $completedAttemptsData = [];
        $questionsAnsweredData = [];
        $questionsCorrectData = [];
        $questionsWrongData = [];
        $accuracyData = [];
        $targetsCompletedData = [];

        foreach ($buckets as [$start, $end]) {
            $attemptsQuery = QuizAttempt::whereBetween('created_at', [$start, $end]);
            $attemptsCount = (clone $attemptsQuery)->count();
            $completedCount = (clone $attemptsQuery)->where('status', 'completed')->count();

            $qAnswered = (int) (clone $attemptsQuery)->sum('attempted_questions');
            $qCorrect = (int) (clone $attemptsQuery)->sum('correct_answers');
            $qWrong = (int) (clone $attemptsQuery)->sum('wrong_answers');

            $acc = $qAnswered > 0 ? round(($qCorrect / $qAnswered) * 100, 1) : 0;

            $targetsCount = UserDailyProgress::whereBetween('date', [$start->toDateString(), $end->toDateString()])
                ->where('target_status', 'completed')
                ->count();

            $attemptsData[] = $attemptsCount;
            $completedAttemptsData[] = $completedCount;
            $questionsAnsweredData[] = $qAnswered;
            $questionsCorrectData[] = $qCorrect;
            $questionsWrongData[] = $qWrong;
            $accuracyData[] = $acc;
            $targetsCompletedData[] = $targetsCount;
        }

        return [
            'labels' => $labels,
            'attempts' => $attemptsData,
            'completed_attempts' => $completedAttemptsData,
            'questions_answered' => $questionsAnsweredData,
            'questions_correct' => $questionsCorrectData,
            'questions_wrong' => $questionsWrongData,
            'accuracy_rate' => $accuracyData,
            'targets_completed' => $targetsCompletedData,
        ];
    }

    private function buildCategoryData(): array
    {
        $categories = Category::all();
        $labels = [];
        $questionsCount = [];
        $attemptsCount = [];

        foreach ($categories as $cat) {
            $labels[] = $cat->name;

            // Total questions in this category
            $qCount = DB::table('quizzes')
                ->join('questions', 'quizzes.id', '=', 'questions.quiz_id')
                ->where('quizzes.category_id', $cat->id)
                ->count();
            $questionsCount[] = $qCount;

            // Total attempts in this category
            $aCount = DB::table('quiz_attempts')
                ->join('quizzes', 'quiz_attempts.quiz_id', '=', 'quizzes.id')
                ->where('quizzes.category_id', $cat->id)
                ->count();
            $attemptsCount[] = $aCount;
        }

        return [
            'labels' => $labels,
            'questions' => $questionsCount,
            'attempts' => $attemptsCount,
        ];
    }

    private function buildTargetData(string $range): array
    {
        $today = now()->toDateString();

        $completed = UserDailyProgress::where('date', $today)->where('target_status', 'completed')->count();
        $inProgress = UserDailyProgress::where('date', $today)->where('target_status', 'in_progress')->count();
        $notStarted = UserDailyProgress::where('date', $today)->where('target_status', 'not_started')->count();

        // If no progress records today, count total users as not started
        if ($completed === 0 && $inProgress === 0 && $notStarted === 0) {
            $notStarted = User::count();
        }

        return [
            'completed' => $completed,
            'in_progress' => $inProgress,
            'not_started' => $notStarted,
        ];
    }

    private function calcPercentageChange(int $current, int $previous): array
    {
        if ($previous == 0) {
            if ($current > 0) {
                return ['pct' => 100, 'direction' => 'up', 'text' => '+100% vs Yesterday'];
            }
            return ['pct' => 0, 'direction' => 'same', 'text' => '0% vs Yesterday'];
        }

        $change = (($current - $previous) / $previous) * 100;
        $rounded = round(abs($change), 1);

        if ($change > 0) {
            return ['pct' => $rounded, 'direction' => 'up', 'text' => "+{$rounded}% vs Yesterday"];
        } elseif ($change < 0) {
            return ['pct' => $rounded, 'direction' => 'down', 'text' => "-{$rounded}% vs Yesterday"];
        } else {
            return ['pct' => 0, 'direction' => 'same', 'text' => '0% vs Yesterday'];
        }
    }
}
