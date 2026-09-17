<?php

namespace App\Services;

use App\Mail\TargetCompletedMail;
use App\Models\AppSetting;
use App\Models\User;
use App\Models\UserDailyProgress;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * Daily quiz target resolution and progress tracking (roadmap §6).
 * The single place that knows the target-priority rule and the business-day
 * boundary, so nothing else has to reimplement either.
 */
class TargetService
{
    private const GLOBAL_TARGET_KEY = 'global_daily_quiz_target';

    public static function businessToday(): Carbon
    {
        return Carbon::now(config('quiz.business_timezone'))->startOfDay();
    }

    public static function globalTarget(): int
    {
        return (int) AppSetting::get(self::GLOBAL_TARGET_KEY, config('quiz.default_daily_quiz_target'));
    }

    public static function setGlobalTarget(int $target): void
    {
        AppSetting::set(self::GLOBAL_TARGET_KEY, $target);
    }

    /**
     * `If User Custom Target Exists -> Use Custom Target` / `Else -> Use
     * Global Target` (roadmap §6.3). A null `custom_daily_target` is exactly
     * "use global" — that's what removing an override sets it back to.
     */
    public static function effectiveTargetFor(User $user): int
    {
        return $user->custom_daily_target ?? self::globalTarget();
    }

    public static function todayProgressFor(User $user): UserDailyProgress
    {
        return self::progressForDate($user, self::businessToday());
    }

    /**
     * Get-or-create the progress row for one calendar date, freezing that
     * date's effective target into the row the first time it's touched so a
     * later target change never rewrites already-recorded history
     * (roadmap §6.6).
     */
    public static function progressForDate(User $user, Carbon $date): UserDailyProgress
    {
        return UserDailyProgress::firstOrCreate(
            ['user_id' => $user->id, 'date' => $date->toDateString()],
            [
                'effective_target' => self::effectiveTargetFor($user),
                'completed_quizzes' => 0,
                'progress_percentage' => 0,
                'target_status' => 'not_started',
            ],
        );
    }

    /**
     * Call once per quiz attempt that newly reaches `status=completed`
     * (roadmap §5.3 — starting/abandoning a quiz must not count). Any
     * topic/category counts toward the target (roadmap §6.4).
     */
    public static function recordCompletedQuiz(User $user, Carbon $completedAt): UserDailyProgress
    {
        $date = $completedAt->clone()->setTimezone(config('quiz.business_timezone'));

        // Ensures the row exists first (firstOrCreate has no lock of its
        // own), then re-reads it under lockForUpdate inside a transaction —
        // without this, two quizzes completed for the same user/day at
        // nearly the same time could both read the same completed_quizzes
        // value and the second save() would silently overwrite the first's
        // increment.
        self::progressForDate($user, $date);

        return DB::transaction(function () use ($user, $date) {
            $progress = UserDailyProgress::where('user_id', $user->id)
                ->where('date', $date->toDateString())
                ->lockForUpdate()
                ->firstOrFail();

            $progress->completed_quizzes++;
            $progress->progress_percentage = $progress->effective_target > 0
                ? min(100, round(($progress->completed_quizzes / $progress->effective_target) * 100, 2))
                : 0;

            if ($progress->effective_target > 0 && $progress->completed_quizzes >= $progress->effective_target) {
                if ($progress->target_status !== 'completed') {
                    $progress->target_status = 'completed';
                    $progress->target_completed_at = now();
                    // Award XP exactly once, at the moment this day newly
                    // reaches its target — never on later completions the
                    // same day, and never retroactively for past days.
                    ProgressionService::awardDailyTargetXp($user);
                    Mail::to($user->email)->queue(new TargetCompletedMail($user, $progress));
                }
            } else {
                $progress->target_status = 'in_progress';
            }

            $progress->save();

            return $progress;
        });
    }
}
