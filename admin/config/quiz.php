<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Activity Tracking
    |--------------------------------------------------------------------------
    |
    | How often the app is expected to ping /activity/heartbeat while in the
    | foreground, and how long without a heartbeat before a user is
    | considered offline. Kept as developer/system configuration rather than
    | an admin-editable setting per the product roadmap.
    |
    */

    'heartbeat_interval_seconds' => (int) env('HEARTBEAT_INTERVAL_SECONDS', 45),

    'online_timeout_seconds' => (int) env('ONLINE_TIMEOUT_SECONDS', 120),

    /*
    |--------------------------------------------------------------------------
    | Business Day / Timezone
    |--------------------------------------------------------------------------
    |
    | "Today" and "this month" for daily targets and monthly stats are always
    | computed in this timezone, regardless of the server's or a device's
    | local timezone, so every user's day boundary lines up consistently.
    |
    */

    'business_timezone' => env('BUSINESS_TIMEZONE', env('APP_TIMEZONE', 'Asia/Kolkata')),

    /*
    |--------------------------------------------------------------------------
    | Daily Quiz Target
    |--------------------------------------------------------------------------
    |
    | Fallback used only if the admin has never saved a global target via
    | Settings (app_settings.global_daily_quiz_target). Once saved, the
    | admin-configured value always takes precedence over this constant.
    |
    */

    'default_daily_quiz_target' => (int) env('DEFAULT_DAILY_QUIZ_TARGET', 20),

    /*
    |--------------------------------------------------------------------------
    | Level / XP Progression
    |--------------------------------------------------------------------------
    |
    | PLACEHOLDER VALUES — the roadmap explicitly says the exact XP economy
    | (XP per completed daily target, XP required per level, whether
    | over-target or partial quizzes grant anything extra) must not be
    | invented and is still to be finalized by the product. These two
    | numbers plus App\Services\LevelService are the only things to change
    | to retune the curve — nothing else in the app depends on their values.
    |
    */

    'xp_per_completed_daily_target' => (int) env('XP_PER_COMPLETED_DAILY_TARGET', 10),

    'xp_per_level' => (int) env('XP_PER_LEVEL', 100),

    /*
    |--------------------------------------------------------------------------
    | Global Ranking (Achievement page)
    |--------------------------------------------------------------------------
    |
    | The leaderboard/achievement roadmap gives a concrete recommended
    | hierarchy for this — level, then accumulated XP, then accuracy, then
    | completed quizzes, then a deterministic tie-break — implemented as-is
    | in App\Services\RankingService. It's still explicitly a starting point
    | ("weights ... should be configurable so it can be refined later"), not
    | a final formula; change the comparison in RankingService to retune it.
    | This is deliberately separate from QuizRankingService, which ranks a
    | single quiz's own participants by that quiz's score — the two must
    | never be conflated (roadmap Part C).
    |
    */

    'active_users_window_days' => (int) env('ACTIVE_USERS_WINDOW_DAYS', 30),

    /*
    |--------------------------------------------------------------------------
    | Quiz Result Performance Message
    |--------------------------------------------------------------------------
    |
    | PLACEHOLDER — the leaderboard/achievement roadmap explicitly leaves the
    | exact wording and thresholds configurable rather than fixed. These are
    | percentage-of-total-questions cutoffs; anything below 'average' is
    | 'low'. Change only these numbers (and the copy in
    | lib/screens/results_screen.dart) to retune it.
    |
    */

    'performance_thresholds' => [
        'excellent' => (int) env('PERFORMANCE_EXCELLENT_THRESHOLD', 90),
        'good' => (int) env('PERFORMANCE_GOOD_THRESHOLD', 70),
        'average' => (int) env('PERFORMANCE_AVERAGE_THRESHOLD', 50),
    ],

    /*
    |--------------------------------------------------------------------------
    | Bilingual Question Translations
    |--------------------------------------------------------------------------
    |
    | When a bilingual quiz's question has no translation for the app's
    | requested language, this is the language served instead
    | (bilingual_question_management_prd.md §16) — chosen because English
    | content is entered first in the admin's translation form, so it's the
    | one translation a bilingual question is most likely to have.
    |
    */

    'fallback_language' => env('FALLBACK_LANGUAGE', 'en'),

    'supported_languages' => ['en', 'hi'],

];
