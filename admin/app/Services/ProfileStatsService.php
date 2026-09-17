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

        $quizPlayed = (clone $completedAttempts)->count();
        $right = (int) (clone $completedAttempts)->sum('correct_answers');
        $wrong = (int) (clone $completedAttempts)->sum('wrong_answers');
        $answered = $right + $wrong;

        // Roadmap §8.3: unanswered questions are excluded from the
        // denominator unless the product later defines them as wrong.
        $accuracy = $answered > 0 ? round(($right / $answered) * 100, 2) : 0.0;

        $thisMonth = (clone $completedAttempts)
            ->whereYear('completed_at', $businessNow->year)
            ->whereMonth('completed_at', $businessNow->month)
            ->count();

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
            'this_month' => $thisMonth,
            'rank' => RankingService::rankFor($user),
        ];
    }
}
