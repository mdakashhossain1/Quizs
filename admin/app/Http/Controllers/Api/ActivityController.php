<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    /**
     * Record that the app is actively in use. Called on foreground and then
     * on a timer while the app stays foregrounded (see roadmap §3.3) — never
     * while backgrounded/killed, since mobile OSes don't guarantee a process
     * can run then anyway (§3.6).
     */
    public function heartbeat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'device_id' => ['required', 'string', 'max:255'],
        ]);

        $user = $request->user();
        $timeoutSeconds = config('quiz.online_timeout_seconds');
        $now = now();

        $session = UserSession::where('user_id', $user->id)
            ->where('device_id', $validated['device_id'])
            ->where('status', 'active')
            ->latest('last_active_at')
            ->first();

        if ($session && $session->last_active_at->lt($now->clone()->subSeconds($timeoutSeconds))) {
            // The gap since the last heartbeat exceeded the timeout: close
            // out that usage session as inactivity-ended and start a new one
            // below, rather than pretending activity was continuous.
            $session->forceFill([
                'session_ended_at' => $session->last_active_at,
                'status' => 'ended',
            ])->save();
            $session = null;
        }

        if (! $session) {
            $session = UserSession::create([
                'user_id' => $user->id,
                'device_id' => $validated['device_id'],
                'authenticated_at' => $now,
                'last_active_at' => $now,
                'status' => 'active',
            ]);
        } else {
            $session->forceFill(['last_active_at' => $now])->save();
        }

        $user->forceFill(['last_active_at' => $now])->save();

        return response()->json([
            'success' => true,
            'online' => true,
            'last_active_at' => $now,
            'heartbeat_interval_seconds' => config('quiz.heartbeat_interval_seconds'),
        ]);
    }

    /**
     * The authenticated user's own current online/offline status.
     */
    public function status(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'online' => $user->is_online,
            'last_active_at' => $user->last_active_at,
            'heartbeat_interval_seconds' => config('quiz.heartbeat_interval_seconds'),
            'online_timeout_seconds' => config('quiz.online_timeout_seconds'),
        ]);
    }
}
