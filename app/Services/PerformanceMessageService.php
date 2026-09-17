<?php

namespace App\Services;

/**
 * Maps a quiz result's percentage score to a performance tier so the
 * frontend can render the right heading/illustration instead of always
 * saying "Congratulations!" (roadmap "Dynamic Result Message"). The
 * frontend owns the actual copy/art per tier — this only decides which one.
 */
class PerformanceMessageService
{
    public static function stateFor(float $percentage): string
    {
        $thresholds = config('quiz.performance_thresholds');

        return match (true) {
            $percentage >= $thresholds['excellent'] => 'excellent',
            $percentage >= $thresholds['good'] => 'good',
            $percentage >= $thresholds['average'] => 'average',
            default => 'low',
        };
    }
}
