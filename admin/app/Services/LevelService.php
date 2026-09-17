<?php

namespace App\Services;

/**
 * The XP curve, isolated so it can be replaced without touching anything
 * else (roadmap §22 — "exact level thresholds can be finalized as a
 * separate configurable algorithm"). Current default is a flat XP-per-level
 * placeholder (config('quiz.xp_per_level')), not a finalized business rule.
 */
class LevelService
{
    /**
     * @return array{level: int, xp: int, xp_into_level: int, xp_for_next_level: int}
     */
    public static function forXp(int $xp): array
    {
        $xpPerLevel = max(1, config('quiz.xp_per_level'));
        $level = intdiv($xp, $xpPerLevel) + 1;
        $xpIntoLevel = $xp % $xpPerLevel;

        return [
            'level' => $level,
            'xp' => $xp,
            'xp_into_level' => $xpIntoLevel,
            'xp_for_next_level' => $xpPerLevel,
        ];
    }
}
