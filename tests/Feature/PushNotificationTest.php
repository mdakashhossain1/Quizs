<?php

namespace Tests\Feature;

use App\Models\PushNotification;
use App\Models\User;
use App\Services\PushNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * push_notification_prd.md: admin-composed notifications fan out into
 * per-user recipient rows (the single source for both delivery and the
 * in-app inbox), attendance's existing push now flows through the same
 * pipeline, and the API feed only ever exposes a user's own notifications.
 */
class PushNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        // The thumbnail-upload test writes a real file to the public
        // uploads directory (not Storage::fake(), so the URL assertion
        // reflects the actual production path) — clean it up afterward.
        File::deleteDirectory(public_path('uploads/notifications'));
        parent::tearDown();
    }

    public function test_sending_to_all_users_creates_a_recipient_for_every_eligible_user(): void
    {
        User::factory()->admin()->create();
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        User::factory()->create(['is_active' => false]);

        $notification = PushNotificationService::send([
            'title' => 'New Quiz Available',
            'body' => 'A new Science quiz is now available.',
            'thumbnail_url' => null,
            'target_type' => 'all',
            'destination_type' => 'none',
            'destination_id' => null,
        ]);

        $this->assertSame(2, $notification->recipients()->count());
        $this->assertTrue($notification->recipients()->where('user_id', $userA->id)->exists());
        $this->assertTrue($notification->recipients()->where('user_id', $userB->id)->exists());
    }

    public function test_sending_to_selected_users_only_notifies_those_users(): void
    {
        $selected = User::factory()->create();
        User::factory()->create();

        $notification = PushNotificationService::send([
            'title' => 'Selected Notice',
            'body' => 'Just for you.',
            'thumbnail_url' => null,
            'target_type' => 'single',
            'target_user_ids' => [$selected->id],
            'destination_type' => 'profile',
            'destination_id' => null,
        ]);

        $this->assertSame(1, $notification->recipients()->count());
        $this->assertSame($selected->id, $notification->recipients()->first()->user_id);
    }

    public function test_admin_can_send_a_notification_with_a_thumbnail_upload(): void
    {
        $admin = User::factory()->admin()->create();
        $recipient = User::factory()->create();

        $this->actingAs($admin, 'web')->post('/admin/push-notifications', [
            'title' => 'New Quiz Available',
            'body' => 'A new Mathematics quiz has been added.',
            'thumbnail' => UploadedFile::fake()->image('quiz.jpg'),
            'audience' => 'specific',
            'user_ids' => [$recipient->id],
            'destination_type' => 'none',
        ])->assertRedirect(route('admin.push-notifications.index'));

        $notification = PushNotification::firstOrFail();
        $this->assertNotNull($notification->thumbnail_url);
        $this->assertStringContainsString('/uploads/notifications/', $notification->thumbnail_url);
    }

    public function test_non_admin_cannot_send_push_notifications(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'web')
            ->post('/admin/push-notifications', ['title' => 'x', 'body' => 'y', 'audience' => 'all', 'destination_type' => 'none'])
            ->assertRedirect(route('admin.login'));
    }

    public function test_attendance_updates_flow_through_the_same_notification_pipeline(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $this->actingAs($admin, 'web')->post('/admin/attendance', [
            'date' => now()->toDateString(),
            'statuses' => [$user->id => 'present'],
        ]);

        $notification = PushNotification::where('destination_type', 'attendance')->first();
        $this->assertNotNull($notification, 'Attendance marking must create a push_notifications record, not just call FcmService directly.');
        $this->assertTrue($notification->recipients()->where('user_id', $user->id)->exists());
    }

    public function test_notification_feed_only_returns_the_authenticated_users_own_notifications(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        PushNotificationService::send([
            'title' => 'For A', 'body' => 'body', 'thumbnail_url' => null,
            'target_type' => 'single', 'target_user_ids' => [$userA->id],
            'destination_type' => 'none', 'destination_id' => null,
        ]);
        PushNotificationService::send([
            'title' => 'For B', 'body' => 'body', 'thumbnail_url' => null,
            'target_type' => 'single', 'target_user_ids' => [$userB->id],
            'destination_type' => 'none', 'destination_id' => null,
        ]);

        $notifications = $this->actingAs($userA, 'sanctum')
            ->getJson('/api/notifications')
            ->assertOk()
            ->json('notifications');

        $this->assertCount(1, $notifications);
        $this->assertSame('For A', $notifications[0]['title']);
        $this->assertFalse($notifications[0]['is_read']);
    }

    public function test_marking_a_notification_read_only_affects_the_owning_users_recipient_row(): void
    {
        $user = User::factory()->create();
        $notification = PushNotificationService::send([
            'title' => 'Hello', 'body' => 'body', 'thumbnail_url' => null,
            'target_type' => 'single', 'target_user_ids' => [$user->id],
            'destination_type' => 'none', 'destination_id' => null,
        ]);
        $recipientId = $notification->recipients()->first()->id;

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/notifications/{$recipientId}/read")
            ->assertOk();

        $this->assertNotNull($notification->recipients()->first()->fresh()->read_at);
    }

    public function test_a_user_cannot_mark_another_users_notification_as_read(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $notification = PushNotificationService::send([
            'title' => 'Hello', 'body' => 'body', 'thumbnail_url' => null,
            'target_type' => 'single', 'target_user_ids' => [$owner->id],
            'destination_type' => 'none', 'destination_id' => null,
        ]);
        $recipientId = $notification->recipients()->first()->id;

        $this->actingAs($intruder, 'sanctum')
            ->postJson("/api/notifications/{$recipientId}/read")
            ->assertStatus(403);
    }
}
