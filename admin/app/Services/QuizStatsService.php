<?php

namespace App\Services;

use App\Models\QuizAttempt;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Quiz-card statistics (roadmap §10-11). Computed in one batched query per
 * metric regardless of how many quiz ids are asked for, so rendering a list
 * of quiz cards doesn't run N+1 queries.
 */
class QuizStatsService
{
    /**
     * @param  iterable<int>  $quizIds
     * @return array<int, array{played_count: int, completion_rate: float}>
     */
    public static function statsForMany(iterable $quizIds): array
    {
        $ids = collect($quizIds)->unique()->values();
        if ($ids->isEmpty()) {
            return [];
        }

        // "Started" = any attempt at all (in_progress or completed) —
        // Phase 4 always creates the attempt row the moment a quiz opens.
        $started = QuizAttempt::whereIn('quiz_id', $ids)
            ->select('quiz_id', DB::raw('COUNT(DISTINCT user_id) as unique_users'))
            ->groupBy('quiz_id')
            ->pluck('unique_users', 'quiz_id');

        // "Played" (roadmap §10.1) = unique users who successfully completed.
        $completed = QuizAttempt::whereIn('quiz_id', $ids)
            ->where('status', 'completed')
            ->select('quiz_id', DB::raw('COUNT(DISTINCT user_id) as unique_users'))
            ->groupBy('quiz_id')
            ->pluck('unique_users', 'quiz_id');

        return $ids->mapWithKeys(function (int $id) use ($started, $completed) {
            $startedCount = (int) ($started[$id] ?? 0);
            $completedCount = (int) ($completed[$id] ?? 0);

            return [$id => [
                'played_count' => $completedCount,
                // Roadmap §11.2: no division by zero, nobody started -> 0%.
                'completion_rate' => $startedCount > 0
                    ? round(($completedCount / $startedCount) * 100, 1)
                    : 0.0,
            ]];
        })->all();
    }

    public static function statsFor(int $quizId): array
    {
        return self::statsForMany([$quizId])[$quizId] ?? ['played_count' => 0, 'completion_rate' => 0.0];
    }

    /**
     * Attaches played_count/completion_rate to each quiz in the collection.
     * @param  Collection  $quizzes
     */
    public static function attachToQuizzes(Collection $quizzes): Collection
    {
        $stats = self::statsForMany($quizzes->pluck('id'));

        return $quizzes->each(function ($quiz) use ($stats) {
            $quizStats = $stats[$quiz->id] ?? ['played_count' => 0, 'completion_rate' => 0.0];
            $quiz->played_count = $quizStats['played_count'];
            $quiz->completion_rate = $quizStats['completion_rate'];
        });
    }
}
