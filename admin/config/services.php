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

    'fcm' => [
        // Firebase Cloud Messaging (HTTP v1 API), for push notifications
        // e.g. attendance updates. Until both are set, FcmService logs the
        // notification instead of sending it — see FcmService::isConfigured.
        'project_id' => env('FCM_PROJECT_ID'),
        // Absolute path to the downloaded service-account JSON credentials
        // (Firebase Console -> Project Settings -> Service Accounts).
        'credentials_path' => env('FCM_CREDENTIALS_PATH'),
    ],

];
