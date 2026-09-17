<?php

namespace Tests\Feature;

use App\Mail\NewAccountMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * An SMTP failure while creating/resetting a user must not crash the
 * request — the temporary password is only known at that moment, so a 500
 * here would leave an account nobody can recover a password for.
 */
class AdminUserAccountEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_account_creation_succeeds_and_shows_the_password_even_if_email_delivery_fails(): void
    {
        $admin = User::factory()->admin()->create();

        Mail::shouldReceive('to')
            ->once()
            ->andReturnSelf();
        Mail::shouldReceive('send')
            ->once()
            ->with(\Mockery::type(NewAccountMail::class))
            ->andThrow(new \RuntimeException('Connection could not be established with host.'));

        $response = $this->actingAs($admin, 'web')->post(route('admin.users.store'), [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'login_id' => null,
            'role' => 'user',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');
        $this->assertStringContainsString('email delivery failed', session('success'));
        $this->assertTrue(User::where('email', 'newuser@example.com')->exists());
    }

    public function test_password_reset_succeeds_and_shows_the_password_even_if_email_delivery_fails(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        Mail::shouldReceive('to')->once()->andReturnSelf();
        Mail::shouldReceive('send')->once()->andThrow(new \RuntimeException('Connection could not be established with host.'));

        $response = $this->actingAs($admin, 'web')->post(route('admin.users.reset-password', $user));

        $response->assertRedirect(route('admin.users.index'));
        $this->assertStringContainsString('email delivery failed', session('success'));
        $this->assertTrue($user->fresh()->must_change_password);
    }
}
