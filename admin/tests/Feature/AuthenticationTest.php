<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Roadmap §2, §19 and the Phase 10 checklist: first login, persistent-token
 * validation, explicit logout, and the temp-password gate.
 */
class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Laravel's `RequestGuard` (which Sanctum's token guard is built on)
     * memoizes the resolved user for the lifetime of the guard instance —
     * see vendor/laravel/framework/.../Auth/RequestGuard.php. That instance
     * survives across multiple simulated requests within one test method
     * (only a fresh test *method* rebuilds the app), so a test that revokes
     * a token and then expects the very next request to be unauthenticated
     * must force the guard to forget its cached resolution first, or it
     * will silently keep returning the previously-resolved user regardless
     * of the token actually sent. This has no effect on real traffic, where
     * every request gets a fresh guard instance in the first place.
     */
    private function forgetAuthGuards(): void
    {
        $this->app['auth']->forgetGuards();
    }

    public function test_admin_created_account_forces_password_change_on_first_login(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('TempPass123'),
            'must_change_password' => true,
        ]);

        $login = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'TempPass123',
        ])->assertOk();

        $login->assertJsonPath('must_change_password', true);
        $token = $login->json('token');

        // The restricted token must not reach quiz/profile endpoints yet.
        $this->forgetAuthGuards();
        $this->withToken($token)
            ->postJson('/api/quizzes/1/start')
            ->assertStatus(403)
            ->assertJsonPath('must_change_password', true);

        // But /auth/me still works, so the app can show who's mid-flow.
        $this->forgetAuthGuards();
        $this->withToken($token)->getJson('/api/auth/me')->assertOk();

        // Setting a new password lifts the restriction and issues a full token.
        $this->forgetAuthGuards();
        $changed = $this->withToken($token)
            ->postJson('/api/auth/force-change-password', ['new_password' => 'NewPass456'])
            ->assertOk();
        $this->assertFalse($changed->json('must_change_password'));

        $fullToken = $changed->json('token');
        $this->assertFalse(User::find($user->id)->must_change_password);

        // The old restricted token was revoked as part of the swap.
        $this->forgetAuthGuards();
        $this->withToken($token)->getJson('/api/auth/me')->assertStatus(401);
        $this->forgetAuthGuards();
        $this->withToken($fullToken)->getJson('/api/auth/me')->assertOk();
    }

    public function test_login_accepts_either_email_or_admin_assigned_login_id(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('secret123'),
            'login_id' => 'player123',
        ]);

        $this->postJson('/api/auth/login', ['email' => $user->email, 'password' => 'secret123'])->assertOk();
        $this->postJson('/api/auth/login', ['email' => 'player123', 'password' => 'secret123'])->assertOk();
        $this->postJson('/api/auth/login', ['email' => 'someone-else', 'password' => 'secret123'])->assertStatus(401);
    }

    public function test_invalid_or_revoked_token_is_rejected(): void
    {
        $this->withToken('not-a-real-token')->getJson('/api/auth/me')->assertStatus(401);
    }

    public function test_explicit_logout_revokes_the_token(): void
    {
        $user = User::factory()->create(['password' => bcrypt('secret123')]);
        $token = $this->postJson('/api/auth/login', [
            'email' => $user->email, 'password' => 'secret123',
        ])->json('token');

        $this->withToken($token)->getJson('/api/auth/me')->assertOk();
        $this->forgetAuthGuards();
        $this->withToken($token)->postJson('/api/auth/logout')->assertOk();
        $this->forgetAuthGuards();
        $this->withToken($token)->getJson('/api/auth/me')->assertStatus(401);
    }

    public function test_deactivated_account_cannot_log_in(): void
    {
        $user = User::factory()->create(['password' => bcrypt('secret123'), 'is_active' => false]);

        $this->postJson('/api/auth/login', ['email' => $user->email, 'password' => 'secret123'])
            ->assertStatus(403);
    }
}
