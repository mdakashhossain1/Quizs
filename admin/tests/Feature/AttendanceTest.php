<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\FcmToken;
use App\Models\User;
use App\Services\AttendanceStatsService;
use App\Services\FcmService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * Roadmap §7 and the Phase 10 checklist: admin-only marking, mark vs.
 * update, the resulting push-notification attempt, and the summary math.
 */
class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_mark_and_later_update_attendance(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $this->actingAs($admin, 'web')->post('/admin/attendance', [
            'date' => '2026-09-16',
            'statuses' => [$user->id => 'present'],
            'notes' => [$user->id => 'On time'],
        ])->assertRedirect();

        $attendance = Attendance::where('user_id', $user->id)->where('date', '2026-09-16')->first();
        $this->assertSame('present', $attendance->status);
        $this->assertSame($admin->id, $attendance->marked_by);

        // An update (changed status) overwrites the same row rather than
        // creating a second one for the same day.
        $this->actingAs($admin, 'web')->post('/admin/attendance', [
            'date' => '2026-09-16',
            'statuses' => [$user->id => 'absent'],
        ])->assertRedirect();

        $this->assertSame(1, Attendance::where('user_id', $user->id)->count());
        $this->assertSame('absent', $attendance->fresh()->status);
    }

    public function test_re_marking_the_same_status_is_not_treated_as_a_change(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        FcmToken::create(['user_id' => $user->id, 'token' => 'tok-1']);

        Http::preventStrayRequests();
        Log::spy();

        $this->actingAs($admin, 'web')->post('/admin/attendance', [
            'date' => '2026-09-16', 'statuses' => [$user->id => 'present'],
        ]);
        $this->clearAfterResponseCallbacks();
        $this->actingAs($admin, 'web')->post('/admin/attendance', [
            'date' => '2026-09-16', 'statuses' => [$user->id => 'present'],
        ]);

        // Only the first call is a real status change -> only one
        // notification attempt (logged, since no FCM credentials are
        // configured in tests).
        Log::shouldHaveReceived('info')
            ->withArgs(fn ($message) => $message === 'FCM not configured; logging notification instead of sending')
            ->once();
    }

    public function test_attendance_change_triggers_a_push_notification_attempt(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        FcmToken::create(['user_id' => $user->id, 'token' => 'tok-1']);

        $this->assertFalse(FcmService::isConfigured());
        Log::spy();

        $this->actingAs($admin, 'web')->post('/admin/attendance', [
            'date' => '2026-09-16', 'statuses' => [$user->id => 'present'],
        ]);

        Log::shouldHaveReceived('info')->withArgs(function ($message, $context) {
            return $message === 'FCM not configured; logging notification instead of sending'
                && str_contains($context['body'], 'Present');
        })->once();
    }

    public function test_attendance_summary_percentage(): void
    {
        $user = User::factory()->create();
        Attendance::create(['user_id' => $user->id, 'date' => '2026-09-14', 'status' => 'present']);
        Attendance::create(['user_id' => $user->id, 'date' => '2026-09-15', 'status' => 'present']);
        Attendance::create(['user_id' => $user->id, 'date' => '2026-09-16', 'status' => 'absent']);
        Attendance::create(['user_id' => $user->id, 'date' => '2026-09-17', 'status' => 'leave']);

        $summary = AttendanceStatsService::summaryFor($user);

        $this->assertSame(2, $summary['present_days']);
        $this->assertSame(1, $summary['absent_days']);
        $this->assertSame(1, $summary['leave_days']);
        $this->assertSame(50.0, $summary['attendance_percentage']);
    }

    public function test_a_user_can_only_see_their_own_attendance(): void
    {
        $me = User::factory()->create();
        $someoneElse = User::factory()->create();
        Attendance::create(['user_id' => $someoneElse->id, 'date' => now()->toDateString(), 'status' => 'present']);

        $today = $this->actingAs($me, 'sanctum')
            ->getJson('/api/attendance/today')
            ->assertOk()
            ->json('attendance');

        $this->assertNull($today, 'Another user\'s attendance must never leak into this endpoint.');
    }

    /**
     * AttendanceController defers its push sends via dispatch(...)
     * ->afterResponse(). In production each HTTP request boots a fresh
     * Application, so this never accumulates; but one PHPUnit test method
     * reuses a single Application across multiple simulated requests, and
     * Illuminate\Foundation\Application::terminate() never clears
     * $terminatingCallbacks — so a callback registered by an earlier
     * request in the same test would otherwise fire again on every
     * subsequent request's termination too.
     */
    private function clearAfterResponseCallbacks(): void
    {
        $property = new \ReflectionProperty(app(), 'terminatingCallbacks');
        $property->setAccessible(true);
        $property->setValue(app(), []);
    }
}
