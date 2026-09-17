<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Services\AttendanceStatsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    /**
     * Today's attendance status, if the admin has marked it yet.
     * Attendance is admin-controlled only — see AttendanceStatsService and
     * Admin\AttendanceController for where it's actually set (roadmap §7.5).
     */
    public function today(Request $request): JsonResponse
    {
        $attendance = AttendanceStatsService::todayFor($request->user());

        return response()->json([
            'success' => true,
            'attendance' => $attendance ? [
                'date' => $attendance->date,
                'status' => $attendance->status,
                'note' => $attendance->note,
            ] : null,
        ]);
    }

    public function history(Request $request): JsonResponse
    {
        $history = Attendance::where('user_id', $request->user()->id)
            ->orderByDesc('date')
            ->paginate(30)
            ->through(fn (Attendance $a) => [
                'date' => $a->date,
                'status' => $a->status,
                'note' => $a->note,
            ]);

        return response()->json([
            'success' => true,
            'history' => $history,
        ]);
    }

    public function summary(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'summary' => AttendanceStatsService::summaryFor($request->user()),
        ]);
    }
}
