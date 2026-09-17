<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * Roadmap §16 — deliberately distinct from Online Now (heartbeat/session
 * recency from ActivityController): these are activity-window counts, not
 * "currently connected right now."
 */
class ActiveUsersService
{
    public static function summary(): array
    {
        return [
            'active_users' => self::activeUsersCount(),
            'active_this_month' => self::activeThisMonthCount(),
        ];
    }

    /**
     * Unique users with at least one heartbeat within the configured
     * rolling window (config('quiz.active_users_window_days')).
     */
    public static function activeUsersCount(): int
    {
        $windowDays = config('quiz.active_users_window_days');

        return User::eligible()
            ->where('last_active_at', '>=', now()->subDays($windowDays))
            ->count();
    }

    /**
     * Unique users with qualifying activity during the current business-
     * timezone calendar month — a fixed, resets-monthly count, unrelated to
     * the rolling window above.
     */
    public static function activeThisMonthCount(): int
    {
        $businessNow = Carbon::now(config('quiz.business_timezone'));

        return User::eligible()
            ->whereYear('last_active_at', $businessNow->year)
            ->whereMonth('last_active_at', $businessNow->month)
            ->count();
    }
}
