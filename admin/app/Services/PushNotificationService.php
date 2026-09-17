<?php

namespace App\Services;

use App\Models\PushNotification;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Server-driven push notifications (push_notification_prd.md): the single
 * place that resolves an audience, fans a notification out into per-user
 * recipient rows (so the in-app inbox and admin read-tracking share one
 * source of truth), and sends the actual device push through FcmService.
 */
class PushNotificationService
{
    /**
     * @param array{title: string, body: string, thumbnail_url: ?string, target_type: string, target_user_ids?: list<int>, destination_type: string, destination_id: ?int} $data
     */
    public static function send(array $data, ?User $sender = null): PushNotification
    {
        $recipients = self::resolveRecipients($data['target_type'], $data['target_user_ids'] ?? []);

        $notification = PushNotification::create([
            'title' => $data['title'],
            'body' => $data['body'],
            'thumbnail_url' => $data['thumbnail_url'] ?? null,
            'target_type' => $data['target_type'],
            'destination_type' => $data['destination_type'] ?? 'none',
            'destination_id' => $data['destination_id'] ?? null,
            'created_by' => $sender?->id,
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        foreach ($recipients as $user) {
            $notification->recipients()->create(['user_id' => $user->id]);

            FcmService::sendToUser(
                $user,
                $notification->title,
                $notification->body,
                [
                    'notification_id' => (string) $notification->id,
                    'destination_type' => $notification->destination_type,
                    'destination_id' => $notification->destination_id,
                ],
                $notification->thumbnail_url,
            );
        }

        return $notification;
    }

    /**
     * @param list<int> $selectedUserIds
     * @return Collection<int, User>
     */
    private static function resolveRecipients(string $targetType, array $selectedUserIds): Collection
    {
        return match ($targetType) {
            'all' => User::eligible()->get(),
            'single', 'selected' => User::eligible()->whereIn('id', $selectedUserIds)->get(),
            default => collect(),
        };
    }
}
