<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserProgression;
use Illuminate\Support\Facades\DB;

/**
 * Accumulated Level/XP from daily-target performance (roadmap §8.2).
 * XP only ever goes up here — missing a target simply means no XP is
 * awarded that day, it never removes or corrupts previously-earned XP.
 */
class ProgressionService
{
    public static function forUser(User $user): array
    {
        $progression = self::progressionFor($user);

        return array_merge(
            LevelService::forXp($progression->xp),
            ['completed_target_days' => $progression->completed_target_days],
        );
    }

    /**
     * Call exactly once per day that newly reaches its daily-target
     * completion (see TargetService::recordCompletedQuiz) — never for a day
     * that was already completed, so repeat quiz completions on an
     * already-met day don't inflate XP.
     */
    public static function awardDailyTargetXp(User $user): void
    {
        // Same lock-then-read pattern as TargetService::recordCompletedQuiz
        // — without it, two daily-target completions racing for the same
        // user (e.g. two devices) could both read the same xp value and
        // the second save() would overwrite the first's award.
        self::progressionFor($user);

        DB::transaction(function () use ($user) {
            $progression = UserProgression::where('user_id', $user->id)->lockForUpdate()->firstOrFail();

            $progression->xp += config('quiz.xp_per_completed_daily_target');
            $progression->completed_target_days += 1;
            $progression->current_level = LevelService::forXp($progression->xp)['level'];
            $progression->save();
        });
    }

    /**
     * NOTE: the default values must be passed explicitly here rather than
     * relying on the migration's DB-level column defaults — Eloquent does
     * not hydrate DB-applied defaults into the in-memory model after
     * insert, so a bare `firstOrCreate(['user_id' => ...])` would leave
     * `xp`/`current_level` as null on a freshly created row.
     */
    private static function progressionFor(User $user): UserProgression
    {
        return UserProgression::firstOrCreate(
            ['user_id' => $user->id],
            ['current_level' => 1, 'xp' => 0, 'completed_target_days' => 0],
        );
    }
}
