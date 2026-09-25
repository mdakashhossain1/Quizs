<?php

namespace Tests\Feature;

use App\Models\OfferwallTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfferwallAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_combined_filters_include_the_whole_end_date_and_only_matching_users(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['name' => 'Offerwall Tester']);
        $other = User::factory()->create();

        foreach ([
            [$user, 'matching-start', '2026-09-20 00:00:00'],
            [$user, 'matching-end', '2026-09-25 23:59:59'],
            [$user, 'too-early', '2026-09-19 23:59:59'],
            [$user, 'too-late', '2026-09-26 00:00:00'],
            [$other, 'wrong-user', '2026-09-23 12:00:00'],
        ] as [$owner, $reference, $date]) {
            OfferwallTransaction::create([
                'user_id' => $owner->id,
                'provider' => 'test',
                'transaction_id' => $reference,
                'transaction_key' => hash('sha256', $reference),
                'status' => 'completed',
                'completed_at' => $date,
            ]);
        }

        $this->actingAs($admin)->get(route('admin.offerwall.index', [
            'search' => 'Offerwall Tester', 'user_id' => $user->id,
            'from' => '2026-09-20', 'to' => '2026-09-25',
        ]))->assertOk()->assertSee('matching-start')->assertSee('matching-end')
            ->assertDontSee('too-early')->assertDontSee('too-late')->assertDontSee('wrong-user');
    }

    public function test_end_date_alone_and_empty_results_render(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.offerwall.index', ['to' => '2026-09-25']))
            ->assertOk()->assertSee('No Offerwall records match these filters.');
    }

    public function test_invalid_range_is_rejected(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.offerwall.index', ['from' => '2026-09-25', 'to' => '2026-09-20']))
            ->assertSessionHasErrors('to');
    }

    public function test_guests_and_non_admins_cannot_view_records(): void
    {
        $this->get(route('admin.offerwall.index'))->assertRedirect(route('admin.login'));
        $this->actingAs(User::factory()->create())->get(route('admin.offerwall.index'))
            ->assertRedirect(route('admin.login'));
    }
}
