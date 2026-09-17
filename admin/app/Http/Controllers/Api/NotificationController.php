<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PushNotificationRecipient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The Flutter-facing feed for push_notification_prd.md's in-app inbox — the
 * same PushNotification/PushNotificationRecipient records that drove the
 * device push are what populate this list, so there is no separate/duplicate
 * notification source to drift out of sync.
 */
class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $recipients = PushNotificationRecipient::where('user_id', $request->user()->id)
            ->with('notification')
            ->latest()
            ->limit(50)
            ->get()
            ->filter(fn (PushNotificationRecipient $r) => $r->notification !== null);

        return response()->json([
            'success' => true,
            'notifications' => $recipients->map(fn (PushNotificationRecipient $r) => [
                'id' => $r->id,
                'title' => $r->notification->title,
                'body' => $r->notification->body,
                'image_url' => $r->notification->thumbnail_url,
                'destination_type' => $r->notification->destination_type,
                'destination_id' => $r->notification->destination_id,
                'created_at' => $r->notification->created_at,
                'is_read' => $r->read_at !== null,
            ])->values(),
        ]);
    }

    public function markRead(Request $request, PushNotificationRecipient $recipient): JsonResponse
    {
        abort_if($recipient->user_id !== $request->user()->id, 403);

        if (! $recipient->read_at) {
            $recipient->update(['read_at' => now()]);
        }

        return response()->json(['success' => true]);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        PushNotificationRecipient::where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }
}
