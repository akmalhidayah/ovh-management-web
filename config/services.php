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

    'woms_organization' => [
        'base_url' => env('WOMS_ORGANIZATION_BASE_URL', 'https://workshop.dept-pmms.com'),
        'token' => env('WOMS_ORGANIZATION_TOKEN'),
        'sync_enabled' => env('WOMS_ORGANIZATION_SYNC_ENABLED', false),
        'sync_interval_minutes' => env('WOMS_ORGANIZATION_SYNC_INTERVAL_MINUTES', 30),
        'timeout_seconds' => env('WOMS_ORGANIZATION_TIMEOUT_SECONDS', 15),
        'max_reduction_percent' => env('WOMS_ORGANIZATION_MAX_REDUCTION_PERCENT', 25),
        'lock_seconds' => env('WOMS_ORGANIZATION_LOCK_SECONDS', 300),
    ],

];
