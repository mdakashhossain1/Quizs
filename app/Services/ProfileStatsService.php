<?php

namespace App\Services;

use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * The one place that computes profile statistics, used by both the mobile
 * app's /profile/stats endpoint and the admin user-activity page — so the
 * two surfaces can never drift apart (roadmap §9.5, §14).
 */
class ProfileStatsService
{
    public static function compute(User $user): array
    {
        $businessNow = Carbon::now(config('quiz.business_timezone'));

        $completedAttempts = QuizAttempt::where('user_id', $user->id)
            ->where('status', 'completed');

        // Today's completed quiz attempts (daily stats)
        $todayAttempts = (clone $completedAttempts)
            ->whereDate('completed_at', $businessNow->toDateString());

        $quizPlayed = (clone $todayAttempts)->count();
        $right = (int) (clone $todayAttempts)->sum('correct_answers');
        $wrong = (int) (clone $todayAttempts)->sum('wrong_answers');

        // Lifetime attempts for overall accuracy
        $lifetimeRight = (int) (clone $completedAttempts)->sum('correct_answers');
        $lifetimeWrong = (int) (clone $completedAttempts)->sum('wrong_answers');
        $lifetimeAnswered = $lifetimeRight + $lifetimeWrong;

        // Roadmap §8.3: unanswered questions are excluded from the
        // denominator unless the product later defines them as wrong.
        $accuracy = $lifetimeAnswered > 0 ? round(($lifetimeRight / $lifetimeAnswered) * 100, 2) : 0.0;

        // Total questions answered in the current business month
        $thisMonthAttempts = (clone $completedAttempts)
            ->whereYear('completed_at', $businessNow->year)
            ->whereMonth('completed_at', $businessNow->month);

        $thisMonthRight = (int) (clone $thisMonthAttempts)->sum('correct_answers');
        $thisMonthWrong = (int) (clone $thisMonthAttempts)->sum('wrong_answers');
        $thisMonthQuestions = (int) (clone $thisMonthAttempts)->sum('attempted_questions');
        if ($thisMonthQuestions < ($thisMonthRight + $thisMonthWrong)) {
            $thisMonthQuestions = $thisMonthRight + $thisMonthWrong;
        }

        $todayProgress = TargetService::todayProgressFor($user);

        return [
            'level' => ProgressionService::forUser($user),
            'today_target' => [
                'effective_target' => $todayProgress->effective_target,
                'completed_quizzes' => $todayProgress->completed_quizzes,
                'remaining' => $todayProgress->remaining,
                'progress_percentage' => $todayProgress->progress_percentage,
                'status' => $todayProgress->display_status,
            ],
            'accuracy' => $accuracy,
            'quiz_played' => $quizPlayed,
            'right' => $right,
            'wrong' => $wrong,
            'this_month' => $thisMonthQuestions,
            'rank' => RankingService::rankFor($user),
        ];
    }
}
