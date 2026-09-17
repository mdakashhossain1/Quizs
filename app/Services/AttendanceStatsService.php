<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Support\Carbon;

class AttendanceStatsService
{
    public static function todayFor(User $user): ?Attendance
    {
        $today = Carbon::now(config('quiz.business_timezone'))->toDateString();

        return Attendance::where('user_id', $user->id)->where('date', $today)->first();
    }

    /**
     * Present/absent/leave day counts and an attendance percentage.
     * Percentage = present / (present + absent + leave) — the roadmap
     * doesn't specify how "leave" should be weighted, so this is the
     * simplest, least-assumption reading; change it here if the product
     * defines something else (e.g. excluding leave from the denominator).
     */
    public static function summaryFor(User $user): array
    {
        $counts = Attendance::where('user_id', $user->id)
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $present = (int) ($counts['present'] ?? 0);
        $absent = (int) ($counts['absent'] ?? 0);
        $leave = (int) ($counts['leave'] ?? 0);
        $total = $present + $absent + $leave;

        return [
            'present_days' => $present,
            'absent_days' => $absent,
            'leave_days' => $leave,
            'attendance_percentage' => $total > 0 ? round($present / $total * 100, 2) : 0,
        ];
    }
}
