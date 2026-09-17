<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserProgression;
use App\Services\ActiveUsersService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * leaderboard_achievement_dynamic_roadmap.md Part B: the Achievement page's
 * consolidated fetch, and the Active Users / Active This Month counts,
 * which must stay distinct from each other and from Online Now.
 */
class AchievementTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_active_users_and_active_this_month_are_independent_windows(): void
    {
        Carbon::setTestNow('2026-09-16 12:00:00');

        // Active in the rolling window (last 30 days) but NOT this calendar month.
        User::factory()->create(['last_active_at' => Carbon::parse('2026-08-25')]);
        // Active this calendar month.
        User::factory()->create(['last_active_at' => Carbon::parse('2026-09-10')]);
        // Outside both windows entirely.
        User::factory()->create(['last_active_at' => Carbon::parse('2026-01-01')]);
        // Never active at all.
        User::factory()->create(['last_active_at' => null]);

        $summary = ActiveUsersService::summary();

        $this->assertSame(2, $summary['active_users'], 'Rolling 30-day window covers both Aug 25 and Sep 10.');
        $this->assertSame(1, $summary['active_this_month'], 'Only the Sep 10 user is active in the current calendar month.');
    }

    public function test_admins_are_excluded_from_active_user_counts(): void
    {
        User::factory()->admin()->create(['last_active_at' => now()]);
        User::factory()->create(['last_active_at' => now()]);

        $summary = ActiveUsersService::summary();

        $this->assertSame(1, $summary['active_users']);
        $this->assertSame(1, $summary['active_this_month']);
    }

    public function test_achievement_endpoint_returns_consolidated_profile_active_users_and_ranking(): void
    {
        $user = User::factory()->create(['last_active_at' => now()]);
        UserProgression::create(['user_id' => $user->id, 'current_level' => 3, 'xp' => 250]);

        $achievement = $this->actingAs($user, 'sanctum')
            ->getJson('/api/achievement')
            ->assertOk()
            ->json('achievement');

        $this->assertSame($user->name, $achievement['profile']['name']);
        $this->assertSame(3, $achievement['profile']['level']['level']);
        $this->assertArrayHasKey('today_target', $achievement['profile']);
        $this->assertArrayHasKey('active_users', $achievement);
        $this->assertArrayHasKey('active_this_month', $achievement['active_users']);
        $this->assertSame(1, $achievement['ranking']['your_rank']);
        $this->assertSame(1, $achievement['ranking']['total_eligible_users']);
        $this->assertCount(1, $achievement['ranking']['top']);
    }

    public function test_achievement_ranking_matches_admin_visible_data(): void
    {
        // §25: admin-visible ranking must be the exact same value the app shows.
        $user = User::factory()->create();
        $apiRank = $this->actingAs($user, 'sanctum')
            ->getJson('/api/achievement')->json('achievement.ranking.your_rank');
        $profileRank = $this->actingAs($user, 'sanctum')
            ->getJson('/api/profile/stats')->json('stats.rank');

        $this->assertSame($profileRank, $apiRank, 'Achievement rank and Profile rank must never disagree.');
    }
}
