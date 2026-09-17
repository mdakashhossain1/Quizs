<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ActiveUsersService;
use App\Services\ProfileStatsService;
use App\Services\RankingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AchievementController extends Controller
{
    /**
     * Single consolidated fetch for the Achievement page (roadmap §24):
     * profile summary (same source as Profile screen — §26 "Data
     * Consistency"), active-user counts, and the global ranking list with
     * the current user's own position.
     */
    public function summary(Request $request): JsonResponse
    {
        $user = $request->user();
        $profileStats = ProfileStatsService::compute($user);
        $ranking = RankingService::summaryFor($user);

        return response()->json([
            'success' => true,
            'achievement' => [
                'profile' => [
                    'name' => $user->name,
                    'avatar' => $user->avatar,
                    'level' => $profileStats['level'],
                    'accuracy' => $profileStats['accuracy'],
                    'today_target' => $profileStats['today_target'],
                ],
                'active_users' => ActiveUsersService::summary(),
                // Global ranking only — never the per-quiz ranking from
                // QuizRankingService (roadmap Part C).
                'ranking' => $ranking,
            ],
        ]);
    }
}
