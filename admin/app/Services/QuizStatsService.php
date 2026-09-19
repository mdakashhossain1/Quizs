<?php

namespace App\Services;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Quiz-card statistics.
 *
 * Played count = unique users who successfully completed this quiz (lifetime).
 * Completion rate (Progress Bar) = percentage of questions answered by the
 * logged-in user TODAY (within the 24-hour daily window in Asia/Kolkata).
 * Automatically resets each day.
 */
class QuizStatsService
{
    /**
     * @param  iterable<int>  $quizIds
     * @param  User|null  $user
     * @return array<int, array{played_count: int, completion_rate: float, today_answered: int, total_questions: int}>
     */
    public static function statsForMany(iterable $quizIds, ?User $user = null): array
    {
        $ids = collect($quizIds)->unique()->values();
        if ($ids->isEmpty()) {
            return [];
        }

        // "Played" = unique users who successfully completed this quiz.
        $completed = QuizAttempt::whereIn('quiz_id', $ids)
            ->where('status', 'completed')
            ->select('quiz_id', DB::raw('COUNT(DISTINCT user_id) as unique_users'))
            ->groupBy('quiz_id')
            ->pluck('unique_users', 'quiz_id');

        $businessNow = Carbon::now(config('quiz.business_timezone'));
        $today = $businessNow->toDateString();

        // Get total question count for each quiz
        $quizzes = Quiz::whereIn('id', $ids)->withCount('questions')->get()->keyBy('id');

        // Fetch logged-in user's quiz attempts today for these quizzes (24-hour daily window)
        $userAttemptsToday = collect();
        if ($user) {
            $userAttemptsToday = QuizAttempt::where('user_id', $user->id)
                ->whereIn('quiz_id', $ids)
                ->where(function ($q) use ($today) {
                    $q->whereDate('completed_at', $today)
                      ->orWhereDate('started_at', $today)
                      ->orWhereDate('created_at', $today)
                      ->orWhereHas('answers', fn ($ans) => $ans->whereDate('answered_at', $today));
                })
                ->withCount('answers')
                ->get()
                ->groupBy('quiz_id');
        }

        return $ids->mapWithKeys(function (int $id) use ($completed, $quizzes, $user, $userAttemptsToday) {
            $completedCount = (int) ($completed[$id] ?? 0);
            $totalQuestions = (int) ($quizzes[$id]->questions_count ?? 0);

            $todayAnswered = 0;
            if ($user && isset($userAttemptsToday[$id])) {
                foreach ($userAttemptsToday[$id] as $attempt) {
                    if ($attempt->status === 'completed') {
                        $count = $totalQuestions > 0 ? $totalQuestions : max((int) $attempt->attempted_questions, $attempt->answers_count);
                    } else {
                        $count = max((int) $attempt->attempted_questions, $attempt->answers_count);
                    }
                    if ($count > $todayAnswered) {
                        $todayAnswered = $count;
                    }
                }
            }

            // Daily questions-answered progress percentage (0.0% to 100.0%)
            $progressRate = $totalQuestions > 0
                ? round((min($todayAnswered, $totalQuestions) / $totalQuestions) * 100, 1)
                : ($todayAnswered > 0 ? 100.0 : 0.0);

            return [$id => [
                'played_count' => $completedCount,
                'completion_rate' => $progressRate,
                'today_answered' => $todayAnswered,
                'total_questions' => $totalQuestions,
            ]];
        })->all();
    }

    public static function statsFor(int $quizId, ?User $user = null): array
    {
        return self::statsForMany([$quizId], $user)[$quizId] ?? [
            'played_count' => 0,
            'completion_rate' => 0.0,
            'today_answered' => 0,
            'total_questions' => 0,
        ];
    }

    /**
     * Attaches played_count and daily completion_rate to each quiz in the collection.
     * @param  Collection  $quizzes
     * @param  User|null  $user
     */
    public static function attachToQuizzes(Collection $quizzes, ?User $user = null): Collection
    {
        $stats = self::statsForMany($quizzes->pluck('id'), $user);

        return $quizzes->each(function ($quiz) use ($stats) {
            $quizStats = $stats[$quiz->id] ?? [
                'played_count' => 0,
                'completion_rate' => 0.0,
                'today_answered' => 0,
                'total_questions' => 0,
            ];
            $quiz->played_count = $quizStats['played_count'];
            $quiz->completion_rate = $quizStats['completion_rate'];
            $quiz->today_answered = $quizStats['today_answered'];
        });
    }
}
