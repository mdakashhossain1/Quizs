<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Roadmap §3 and the Phase 10 checklist: online/offline is derived from
 * heartbeat recency, not from login state — "app killed without logout" and
 * "internet disconnected" both reduce, server-side, to "no further
 * heartbeat arrives," which is exactly what the timeout test below covers.
 */
class ActivityTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_heartbeat_marks_user_online_and_creates_a_session(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/activity/heartbeat', ['device_id' => 'device-1'])
            ->assertOk()
            ->assertJsonPath('online', true);

        $this->assertTrue($user->fresh()->is_online);
        $this->assertSame(1, UserSession::where('user_id', $user->id)->count());
    }

    public function test_heartbeat_timeout_transitions_user_offline_and_closes_the_stale_session(): void
    {
        $user = User::factory()->create();
        $timeout = config('quiz.online_timeout_seconds');

        Carbon::setTestNow('2026-09-16 10:00:00');
        $this->actingAs($user, 'sanctum')
            ->postJson('/api/activity/heartbeat', ['device_id' => 'device-1'])
            ->assertOk();

        // No heartbeat arrives for longer than the timeout — this is the
        // server-observable equivalent of "app killed" or "internet
        // disconnected": the client simply stops calling in.
        Carbon::setTestNow(Carbon::parse('2026-09-16 10:00:00')->addSeconds($timeout + 30));
        $this->assertFalse($user->fresh()->is_online, 'Online -> Offline transition must happen without any explicit logout.');

        // The next heartbeat (e.g. app reopened) closes the stale session as
        // inactivity-ended and opens a fresh one, rather than pretending
        // activity was continuous across the gap.
        $this->actingAs($user, 'sanctum')
            ->postJson('/api/activity/heartbeat', ['device_id' => 'device-1'])
            ->assertOk();

        $sessions = UserSession::where('user_id', $user->id)->orderBy('id')->get();
        $this->assertCount(2, $sessions);
        $this->assertSame('ended', $sessions[0]->status);
        $this->assertNotNull($sessions[0]->session_ended_at);
        $this->assertNull($sessions[0]->explicit_logout_at);
        $this->assertSame('active', $sessions[1]->status);
    }

    public function test_explicit_logout_closes_the_session_with_a_logout_timestamp_not_a_timeout(): void
    {
        $user = User::factory()->create(['password' => bcrypt('secret123')]);
        $token = $this->postJson('/api/auth/login', ['email' => $user->email, 'password' => 'secret123'])->json('token');

        $this->withToken($token)->postJson('/api/activity/heartbeat', ['device_id' => 'device-1'])->assertOk();
        $this->withToken($token)->postJson('/api/auth/logout', ['device_id' => 'device-1'])->assertOk();

        $session = UserSession::where('user_id', $user->id)->first();
        $this->assertSame('ended', $session->status);
        $this->assertNotNull($session->explicit_logout_at);
        $this->assertNull($session->session_ended_at);
    }
}
