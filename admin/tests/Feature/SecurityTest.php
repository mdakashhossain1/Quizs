<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Roadmap §19.17-18 and the Phase 10 checklist: protected endpoints require
 * auth, admin pages require the admin role, and users can't reach each
 * other's private data.
 */
class SecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_protected_api_routes_reject_unauthenticated_requests(): void
    {
        $this->getJson('/api/auth/me')->assertStatus(401);
        $this->getJson('/api/profile/stats')->assertStatus(401);
        $this->getJson('/api/target/today')->assertStatus(401);
        $this->getJson('/api/attendance/today')->assertStatus(401);
        $this->postJson('/api/activity/heartbeat', ['device_id' => 'x'])->assertStatus(401);
    }

    /**
     * getJson()/postJson() above auto-send "Accept: application/json",
     * which alone made every protected api/* route return a clean 401 even
     * before bootstrap/app.php's shouldRenderJsonWhen()/redirectGuestsTo()
     * fix existed. A real client that doesn't send that header (plain curl,
     * some HTTP libraries, webhooks) must still get 401 JSON, not a 500 from
     * Laravel's default redirect-to-a-nonexistent-'login'-route fallback —
     * caught only by running the real app end-to-end, not by these
     * JSON-flavored test helpers.
     */
    public function test_protected_api_routes_return_clean_401_without_an_accept_header(): void
    {
        $this->get('/api/auth/me')->assertStatus(401)->assertJson(['message' => 'Unauthenticated.']);
        $this->get('/api/profile/stats')->assertStatus(401);
        $this->get('/api/achievement')->assertStatus(401);
        $this->get('/api/notifications')->assertStatus(401);
        $this->post('/api/activity/heartbeat', ['device_id' => 'x'])->assertStatus(401);
    }

    public function test_admin_web_routes_reject_a_non_admin_user(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user, 'web')
            ->get('/admin/dashboard')
            ->assertRedirect(route('admin.login'));

        // AdminMiddleware also logs the impostor out of the web guard.
        $this->assertGuest('web');
    }

    public function test_admin_web_routes_reject_an_inactive_admin(): void
    {
        $admin = User::factory()->admin()->create(['is_active' => false]);

        $this->actingAs($admin, 'web')
            ->get('/admin/dashboard')
            ->assertRedirect(route('admin.login'));
    }

    public function test_temp_password_account_is_blocked_from_quiz_and_profile_routes(): void
    {
        $user = User::factory()->create(['must_change_password' => true]);

        $this->actingAs($user, 'sanctum')->postJson('/api/quizzes/1/start')->assertStatus(403);
        $this->actingAs($user, 'sanctum')->postJson('/api/auth/update-profile', ['name' => 'x'])->assertStatus(403);

        // But identity/exit routes stay open so the app can show the
        // force-change-password screen and let the user back out.
        $this->actingAs($user, 'sanctum')->getJson('/api/auth/me')->assertOk();
        $this->actingAs($user, 'sanctum')->postJson('/api/auth/logout')->assertOk();
    }

    public function test_cannot_delete_own_admin_account(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin, 'web')
            ->delete("/admin/users/{$admin->id}")
            ->assertRedirect();

        $this->assertNotNull($admin->fresh(), 'An admin must not be able to delete their own account.');
    }
}
