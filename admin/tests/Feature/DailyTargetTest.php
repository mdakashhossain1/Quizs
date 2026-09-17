<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\User;
use App\Services\TargetService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Roadmap §6 and the Phase 10 checklist: global vs. custom target priority,
 * override removal, historical preservation across a settings change, and
 * the daily reset.
 */
class DailyTargetTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_users_without_a_custom_target_use_the_global_target(): void
    {
        TargetService::setGlobalTarget(20);
        $user = User::factory()->create(['custom_daily_target' => null]);

        $this->assertSame(20, TargetService::effectiveTargetFor($user));
    }

    public function test_custom_target_overrides_the_global_target(): void
    {
        TargetService::setGlobalTarget(20);
        $user = User::factory()->create(['custom_daily_target' => 10]);

        $this->assertSame(10, TargetService::effectiveTargetFor($user));
    }

    public function test_removing_the_custom_target_reverts_to_global(): void
    {
        TargetService::setGlobalTarget(20);
        $user = User::factory()->create(['custom_daily_target' => 10]);
        $this->assertSame(10, TargetService::effectiveTargetFor($user));

        // "Use Global Target" in the admin UI submits this exact update —
        // clearing the override, not deleting the user.
        $user->update(['custom_daily_target' => null]);

        $this->assertSame(20, TargetService::effectiveTargetFor($user->fresh()));
    }

    public function test_changing_the_global_target_does_not_rewrite_already_recorded_history(): void
    {
        TargetService::setGlobalTarget(20);
        $user = User::factory()->create();

        Carbon::setTestNow('2026-09-16 09:00:00');
        TargetService::recordCompletedQuiz($user, Carbon::now());
        $yesterday = TargetService::progressForDate($user, Carbon::parse('2026-09-16'));
        $this->assertSame(20, $yesterday->effective_target);

        Carbon::setTestNow('2026-09-17 09:00:00');
        TargetService::setGlobalTarget(30);

        $reReadYesterday = TargetService::progressForDate($user, Carbon::parse('2026-09-16'));
        $this->assertSame(20, $reReadYesterday->effective_target, 'Historical target must survive a later global-target change.');

        // Today, though, uses the new value.
        $this->assertSame(30, TargetService::effectiveTargetFor($user));
    }

    public function test_each_calendar_day_gets_its_own_fresh_progress_row(): void
    {
        TargetService::setGlobalTarget(5);
        $user = User::factory()->create();

        Carbon::setTestNow('2026-09-16 09:00:00');
        foreach (range(1, 5) as $_) {
            TargetService::recordCompletedQuiz($user, Carbon::now());
        }
        $day1 = TargetService::todayProgressFor($user);
        $this->assertSame('completed', $day1->target_status);

        Carbon::setTestNow('2026-09-17 09:00:00');
        $day2 = TargetService::todayProgressFor($user);
        $this->assertSame(0, $day2->completed_quizzes, 'The daily reset must start a new day at zero, not carry yesterday\'s count.');
        $this->assertSame('not_started', $day2->target_status);
    }

    public function test_a_past_day_that_never_reached_its_target_displays_as_not_completed(): void
    {
        TargetService::setGlobalTarget(5);
        $user = User::factory()->create();

        Carbon::setTestNow('2026-09-16 09:00:00');
        TargetService::recordCompletedQuiz($user, Carbon::now()); // 1 of 5 — never finished.

        Carbon::setTestNow('2026-09-17 09:00:00');
        $yesterday = TargetService::progressForDate($user, Carbon::parse('2026-09-16'));
        $this->assertSame('in_progress', $yesterday->target_status, 'Stored status is never rewritten to a missed value...');
        $this->assertSame('not_completed', $yesterday->display_status, '...but a past incomplete day must read as not_completed.');
    }

    public function test_api_exposes_effective_target_and_progress_for_the_authenticated_user(): void
    {
        AppSetting::set('global_daily_quiz_target', 8);
        $user = User::factory()->create();

        $today = $this->actingAs($user, 'sanctum')
            ->getJson('/api/target/today')
            ->assertOk()
            ->json('target');

        $this->assertSame(8, $today['effective_target']);
        $this->assertSame(0, $today['completed_quizzes']);
        $this->assertSame('not_started', $today['status']);
    }
}
