<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FcmToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceTokenController extends Controller
{
    /**
     * Registers (or re-owns) a device's FCM token for push notifications.
     * Keyed by the token itself so a device that logs in as a different
     * user re-points the same row rather than accumulating stale rows tied
     * to a previous account.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string', 'max:255'],
            'platform' => ['nullable', 'string', 'max:32'],
        ]);

        FcmToken::updateOrCreate(
            ['token' => $validated['token']],
            ['user_id' => $request->user()->id, 'platform' => $validated['platform'] ?? null],
        );

        return response()->json(['success' => true]);
    }
}
