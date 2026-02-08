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

    /*
    |--------------------------------------------------------------------------
    | Payment Services
    |--------------------------------------------------------------------------
    */

    'payme' => [
        'merchant_id' => env('PAYME_MERCHANT_ID'),
        'key' => env('PAYME_KEY'),
        'test_mode' => env('PAYME_TEST_MODE', true),
    ],

    'click' => [
        'merchant_id' => env('CLICK_MERCHANT_ID'),
        'service_id' => env('CLICK_SERVICE_ID'),
        'secret_key' => env('CLICK_SECRET_KEY'),
        'test_mode' => env('CLICK_TEST_MODE', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Telegram Bot
    |--------------------------------------------------------------------------
    */

    'telegram' => [
        'bot_token' => env('TELEGRAM_BOT_TOKEN'),
        'bot_username' => env('TELEGRAM_BOT_USERNAME'),
        'webhook_url' => env('TELEGRAM_WEBHOOK_URL'),
        'admin_chat_id' => env('TELEGRAM_ADMIN_CHAT_ID'),

        // OPS group (for new order notifications)
        'ops_bot_token' => env('TELEGRAM_OPS_BOT_TOKEN', env('TELEGRAM_BOT_TOKEN')),
        'ops_group_id' => env('TELEGRAM_OPS_GROUP_ID', env('TELEGRAM_ADMIN_CHAT_ID')),

        // Therapist group
        'therapist_bot_token' => env('TELEGRAM_THERAPIST_BOT_TOKEN'),
        'therapist_group_id' => env('TELEGRAM_THERAPIST_GROUP_ID'),
    ],

    /*
    |--------------------------------------------------------------------------
    | SMS Provider (Eskiz.uz)
    |--------------------------------------------------------------------------
    */

    'eskiz' => [
        'email' => env('ESKIZ_EMAIL'),
        'password' => env('ESKIZ_PASSWORD'),
        'sender' => env('ESKIZ_SENDER', 'HomeMassage'),
        'base_url' => env('ESKIZ_BASE_URL', 'https://notify.eskiz.uz/api'),
        'token_cache_key' => 'eskiz_auth_token',
        'token_ttl' => 86400, // 24 hours
        'skip_send' => env('ESKIZ_SKIP_SEND', false),
    ],

];
