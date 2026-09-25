<?php

namespace Tests\Feature;

use App\Models\OfferwallTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Laravel\Sanctum\Sanctum;
use RuntimeException;
use Tests\TestCase;

class OfferwallApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'offerwall.enabled' => true,
            'offerwall.sdk_key' => 'test-sdk-key',
            'offerwall.placement' => '#WebOfferwall',
            'offerwall.callback_secret' => 'test-server-secret',
            'offerwall.callback_mode' => 'hmac_sha256',
        ]);
    }

    private function sendCallback(array $payload, ?string $signature = null): TestResponse
    {
        $body = json_encode($payload, JSON_THROW_ON_ERROR);

        return $this->call('POST', '/api/offerwall/callback/unity', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_TAPJOY_SIGNATURE' => $signature ?? hash_hmac('sha256', $body, 'test-server-secret'),
        ], $body);
    }

    private function payload(User $user): array
    {
        return [
            'id' => 'completion-123',
            'user' => ['id' => (string) $user->id],
            'timestamp' => now()->subHour()->timestamp,
            'offer' => ['name' => 'Example offer', 'task' => ['name' => 'Complete level 1']],
            'currency' => ['reward' => 100],
        ];
    }

    public function test_launch_requires_authentication(): void
    {
        $this->postJson('/api/offerwall/launch')->assertUnauthorized();
    }

    public function test_launch_uses_authenticated_id_and_does_not_record_a_completion(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $this->postJson('/api/offerwall/launch', ['user_id' => 999])->assertOk()
            ->assertJsonPath('url', 'https://rewards.unity.com/owp/web/link/test-sdk-key/u/'.$user->id.'?event_name=%23WebOfferwall')
            ->assertDontSee('test-server-secret');
        $this->assertDatabaseCount('offerwall_transactions', 0);
    }

    public function test_launch_handles_disabled_or_missing_configuration(): void
    {
        Sanctum::actingAs(User::factory()->create());
        config(['offerwall.enabled' => false]);
        $this->postJson('/api/offerwall/launch')->assertStatus(503);
        config(['offerwall.enabled' => true, 'offerwall.callback_secret' => '']);
        $this->postJson('/api/offerwall/launch')->assertStatus(503);
    }

    public function test_inactive_accounts_and_temporary_passwords_cannot_launch(): void
    {
        $user = User::factory()->create(['is_active' => false]);
        Sanctum::actingAs($user);
        $this->postJson('/api/offerwall/launch')->assertForbidden();
        $user->update(['is_active' => true, 'must_change_password' => true]);
        $this->postJson('/api/offerwall/launch')->assertForbidden()
            ->assertJsonPath('must_change_password', true);
    }

    public function test_signed_completion_retries_are_acknowledged_without_rewards(): void
    {
        $user = User::factory()->create(['score' => 50]);
        $payload = $this->payload($user);
        $this->sendCallback($payload)->assertOk()->assertSee('OK');
        $this->sendCallback($payload)->assertOk();

        $transaction = OfferwallTransaction::sole();
        $this->assertSame($user->id, $transaction->user_id);
        $this->assertSame('Example offer', $transaction->offer_name);
        $this->assertSame($payload['timestamp'], $transaction->completed_at->timestamp);
        $this->assertArrayNotHasKey('currency', $transaction->provider_payload);
        $this->assertSame(50, $user->fresh()->score);
        $this->assertDatabaseCount('user_progression', 0);
        $this->assertDatabaseCount('user_daily_progress', 0);
    }

    public function test_bad_missing_and_tampered_signatures_are_rejected(): void
    {
        $payload = $this->payload(User::factory()->create());
        $this->sendCallback($payload, '')->assertForbidden();
        $this->sendCallback($payload, str_repeat('a', 64))->assertForbidden();
        $signature = hash_hmac('sha256', json_encode($payload), 'test-server-secret');
        $payload['offer']['name'] = 'Tampered offer';
        $this->sendCallback($payload, $signature)->assertForbidden();
        $this->assertDatabaseCount('offerwall_transactions', 0);
    }

    public function test_unknown_and_noncanonical_users_are_rejected(): void
    {
        $payload = $this->payload(User::factory()->create());
        foreach (['0'.$payload['user']['id'], '999999', '1.0', '1e0', ' 1'] as $id) {
            $payload['user']['id'] = $id;
            $this->sendCallback($payload)->assertForbidden();
        }
        $this->assertDatabaseCount('offerwall_transactions', 0);
    }

    public function test_invalid_signed_payload_is_not_recorded(): void
    {
        $payload = $this->payload(User::factory()->create());
        unset($payload['id']);
        $this->sendCallback($payload)->assertForbidden();
        $payload['id'] = 'future';
        $payload['timestamp'] = now()->addDay()->timestamp;
        $this->sendCallback($payload)->assertForbidden();
        $this->assertDatabaseCount('offerwall_transactions', 0);
    }

    public function test_signed_retry_cannot_move_completion_to_another_user(): void
    {
        $user = User::factory()->create();
        $payload = $this->payload($user);
        $this->sendCallback($payload)->assertOk();
        $payload['user']['id'] = (string) User::factory()->create()->id;
        $this->sendCallback($payload)->assertForbidden();
        $this->assertSame($user->id, OfferwallTransaction::sole()->user_id);
    }

    public function test_callbacks_still_reconcile_when_new_launches_are_disabled(): void
    {
        config(['offerwall.enabled' => false]);
        $this->sendCallback($this->payload(User::factory()->create()))->assertOk();
    }

    public function test_legacy_callbacks_require_explicit_mode_and_verified_fields(): void
    {
        $user = User::factory()->create();
        $params = ['id' => 'legacy-1', 'snuid' => (string) $user->id, 'currency' => '100'];
        $params['verifier'] = md5('legacy-1:'.$user->id.':100:test-server-secret');
        $url = '/api/offerwall/callback/unity?'.http_build_query($params);
        $this->getJson($url)->assertForbidden();

        config(['offerwall.callback_mode' => 'legacy_md5']);
        $this->getJson($url)->assertOk();
        $this->getJson($url)->assertOk();
        $this->assertDatabaseCount('offerwall_transactions', 1);
        $this->assertSame('received_at', OfferwallTransaction::sole()->provider_payload['time_source']);

        $params['currency'] = '101';
        $this->getJson('/api/offerwall/callback/unity?'.http_build_query($params))->assertForbidden();
        $this->sendCallback($this->payload($user))->assertForbidden();
    }

    public function test_database_failure_does_not_acknowledge_completion(): void
    {
        OfferwallTransaction::creating(fn () => throw new RuntimeException('Database unavailable'));
        try {
            $this->sendCallback($this->payload(User::factory()->create()))->assertStatus(500);
            $this->assertDatabaseCount('offerwall_transactions', 0);
        } finally {
            OfferwallTransaction::flushEventListeners();
        }
    }
}
