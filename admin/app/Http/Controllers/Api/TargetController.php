<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserDailyProgress;
use App\Services\TargetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TargetController extends Controller
{
    /**
     * Today's effective target and progress for the authenticated user.
     */
    public function today(Request $request): JsonResponse
    {
        $progress = TargetService::todayProgressFor($request->user());

        return response()->json([
            'success' => true,
            'target' => $this->progressPayload($progress),
        ]);
    }

    /**
     * Historical daily progress, most recent first. Each row keeps the
     * target that was effective on that date (roadmap §6.6), so this never
     * gets rewritten by a later global/custom target change.
     */
    public function history(Request $request): JsonResponse
    {
        $history = UserDailyProgress::where('user_id', $request->user()->id)
            ->orderByDesc('date')
            ->paginate(30);

        $history->getCollection()->transform(fn (UserDailyProgress $p) => $this->progressPayload($p));

        return response()->json([
            'success' => true,
            'history' => $history,
        ]);
    }

    private function progressPayload(UserDailyProgress $progress): array
    {
        return [
            'date' => $progress->date,
            'effective_target' => $progress->effective_target,
            'completed_quizzes' => $progress->completed_quizzes,
            'remaining' => $progress->remaining,
            'progress_percentage' => $progress->progress_percentage,
            'status' => $progress->display_status,
        ];
    }
}
