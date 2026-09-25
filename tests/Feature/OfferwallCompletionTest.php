<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\OfferwallService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class OfferwallCompletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_retries_preserve_the_original_completion_and_do_not_change_user_score(): void
    {
        $user = User::factory()->create(['score' => 123]);
        $service = app(OfferwallService::class);
        $first = $service->recordCompletion(
            $user, 'test', 'transaction-1', 'offer-1', 'Example offer',
            Carbon::parse('2026-09-20 12:00:00'), ['event' => 'completed'],
        );
        $retry = $service->recordCompletion($user, 'test', 'transaction-1');

        $this->assertSame($first->id, $retry->id);
        $this->assertSame('2026-09-20 12:00:00', $retry->completed_at->toDateTimeString());
        $this->assertSame('Example offer', $retry->offer_name);
        $this->assertSame(123, $user->fresh()->score);
        $this->assertDatabaseCount('offerwall_transactions', 1);
        $this->assertDatabaseCount('user_progression', 0);
        $this->assertDatabaseCount('user_daily_progress', 0);
    }

    public function test_transaction_identifiers_are_scoped_to_the_provider(): void
    {
        $user = User::factory()->create();
        $service = app(OfferwallService::class);
        $service->recordCompletion($user, 'first', 'same-reference');
        $service->recordCompletion($user, 'second', 'same-reference');
        $this->assertDatabaseCount('offerwall_transactions', 2);
    }

    public function test_retry_cannot_reassign_a_transaction(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $service = app(OfferwallService::class);
        $service->recordCompletion($user, 'test', 'same-reference');

        try {
            $service->recordCompletion($other, 'test', 'same-reference');
            $this->fail('Conflicting attribution must be rejected.');
        } catch (ValidationException) {
            $this->assertDatabaseHas('offerwall_transactions', ['user_id' => $user->id]);
            $this->assertDatabaseCount('offerwall_transactions', 1);
        }
    }

    public function test_case_distinct_provider_references_remain_distinct(): void
    {
        $user = User::factory()->create();
        $service = app(OfferwallService::class);
        $service->recordCompletion($user, 'test', 'reference-A');
        $service->recordCompletion($user, 'test', 'reference-a');
        $this->assertDatabaseCount('offerwall_transactions', 2);
    }
}
