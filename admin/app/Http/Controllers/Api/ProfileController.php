<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ProfileStatsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * Consolidated profile/statistics response (roadmap §15 "Profile").
     * Backend is the single source of truth here — see ProfileStatsService.
     */
    public function stats(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'stats' => ProfileStatsService::compute($request->user()),
        ]);
    }
}
