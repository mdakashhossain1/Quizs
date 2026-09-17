<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'firebase' => [
        // Firebase Web API key (public/client key, same one used in the
        // Flutter app's firebase_options.dart) — used to verify Google
        // sign-in ID tokens server-side via the Identity Toolkit API.
        'api_key' => env('FIREBASE_API_KEY'),
    ],

    'cron' => [
        // Shared secret for the cron-job.org webhook (App\Http\Controllers\CronController).
        // Generate one with `php artisan tinker --execute="echo Str::random(40);"`.
        'secret' => env('CRON_SECRET'),
    ],

    'fcm' => [
        // Firebase Cloud Messaging (HTTP v1 API), for push notifications
        // e.g. attendance updates. Until both are set, FcmService logs the
        // notification instead of sending it — see FcmService::isConfigured.
        'project_id' => env('FCM_PROJECT_ID'),
        // The downloaded service-account JSON (Firebase Console -> Project
        // Settings -> Service Accounts), as a single-line string directly in
        // .env — preferred on shared hosting, where a private storage path
        // isn't something you can rely on surviving a deploy.
        'credentials_json' => env('FCM_CREDENTIALS_JSON'),
        // Fallback: absolute path to that same JSON file on disk, for setups
        // that do have a reliable private storage location.
        'credentials_path' => env('FCM_CREDENTIALS_PATH'),
    ],

];
