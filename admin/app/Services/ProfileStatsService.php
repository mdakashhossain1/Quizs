<?php

namespace App\Services;

use App\Models\QuizAttempt;
use App\Models\QuizAttemptAnswer;
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
        $todayDate = $businessNow->toDateString();

        $completedAttempts = QuizAttempt::where('user_id', $user->id)
            ->where('status', 'completed');

        // Today's completed quiz attempts
        $todayCompletedAttempts = (clone $completedAttempts)
            ->whereDate('completed_at', $todayDate);

        $todayCompletedRight = (int) (clone $todayCompletedAttempts)->sum('correct_answers');
        $todayCompletedWrong = (int) (clone $todayCompletedAttempts)->sum('wrong_answers');

        // Also include any questions answered in ongoing/in-progress attempts today
        $inProgressAttemptIds = QuizAttempt::where('user_id', $user->id)
            ->where('status', 'in_progress')
            ->pluck('id');

        $inProgressAnswersToday = QuizAttemptAnswer::whereIn('quiz_attempt_id', $inProgressAttemptIds)
            ->whereDate('answered_at', $todayDate)
            ->with('selectedOption')
            ->get();

        $inProgressRightToday = $inProgressAnswersToday->filter(function ($a) {
            return (bool) (
                $a->is_correct
                || ($a->selectedOption && $a->selectedOption->is_correct)
                || ($a->correct_option_id && (int) $a->selected_option_id === (int) $a->correct_option_id)
            );
        })->count();
        $inProgressWrongToday = $inProgressAnswersToday->count() - $inProgressRightToday;

        $right = $todayCompletedRight + $inProgressRightToday;
        $wrong = $todayCompletedWrong + $inProgressWrongToday;

        // Total questions performed/attempted today (roadmap §8 / user requirement:
        // "quiz played me humne kitna question perform kiya uska number aana chahiye")
        $quizPlayed = $right + $wrong;

        // Lifetime attempts for overall accuracy
        $lifetimeRight = (int) (clone $completedAttempts)->sum('correct_answers') + $inProgressRightToday;
        $lifetimeWrong = (int) (clone $completedAttempts)->sum('wrong_answers') + $inProgressWrongToday;
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

        $inProgressAnswersMonth = QuizAttemptAnswer::whereIn('quiz_attempt_id', $inProgressAttemptIds)
            ->whereYear('answered_at', $businessNow->year)
            ->whereMonth('answered_at', $businessNow->month)
            ->count();

        $thisMonthTotal = $thisMonthQuestions + $inProgressAnswersMonth;

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
            'this_month' => $thisMonthTotal,
            'rank' => RankingService::rankFor($user),
        ];
    }
}
