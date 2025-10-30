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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | SIAKAD Integration
    |--------------------------------------------------------------------------
    */

    'siakad' => [
        'url' => env('SIAKAD_URL', 'https://siakad.polinema.ac.id'),
        'api_url' => env('SIAKAD_API_URL', env('SIAKAD_URL') . '/api'),
        'timeout' => env('SIAKAD_TIMEOUT', 30),
        'enabled' => env('SIAKAD_SSO_ENABLED', true),
        'fallback_local' => env('SIAKAD_FALLBACK_LOCAL', true), // Allow local login if SIAKAD down
        'use_portal' => env('SIAKAD_USE_PORTAL', true), // Use web scraping instead of API
        'shared_secret' => env('SIAKAD_SHARED_SECRET', 'default-secret-key-change-in-production'),
    ],

    'openrouter' => [
        'api_key' => env('OPENROUTER_API_KEY'),
        'base_url' => env('OPENROUTER_BASE_URL', 'https://openrouter.ai/api/v1'),
        'model' => env('AI_MODEL', 'deepseek/deepseek-r1:free'),
        'timeout' => env('AI_TIMEOUT', 30),
        'app_name' => env('APP_NAME', 'LMS Cerdas'),
        'app_url' => env('APP_URL', 'http://localhost'),
    ],

    'telegram' => [
        'bot_token' => env('TELEGRAM_BOT_TOKEN'),
        'bot_username' => env('TELEGRAM_BOT_USERNAME'),
    ],

    'whatsapp' => [
        'token' => env('WHATSAPP_TOKEN'),
        'phone_id' => env('WHATSAPP_PHONE_ID'),
    ],

    'spada' => [
        'url' => env('SPADA_URL', 'https://slc.polinema.ac.id/spada/'),
        'siakad_url' => env('SIAKAD_URL', 'https://siakad.polinema.ac.id/beranda'),
        'session_cookie' => env('SPADA_SESSION_COOKIE'),
        'csrf_token' => env('SPADA_CSRF_TOKEN'),
    ],

    'moodle' => [
        'url' => env('MOODLE_URL'),
        'token' => env('MOODLE_TOKEN'),
    ],

    'google_classroom' => [
        'client_id' => env('GOOGLE_CLASSROOM_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLASSROOM_CLIENT_SECRET'),
    ],

    'canvas' => [
        'url' => env('CANVAS_URL'),
        'token' => env('CANVAS_TOKEN'),
    ],

    'meilisearch' => [
        'host' => env('MEILISEARCH_HOST', 'http://127.0.0.1:7700'),
        'key' => env('MEILISEARCH_KEY'),
    ],

    'fcm' => [
        'server_key' => env('FCM_SERVER_KEY'),
        'sender_id' => env('FCM_SENDER_ID'),
    ],

];
