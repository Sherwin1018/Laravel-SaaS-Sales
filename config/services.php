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

    'paymongo' => [
        'secret' => env('PAYMONGO_SECRET_KEY'),
        'webhook_secret' => env('PAYMONGO_WEBHOOK_SECRET'),
        'payment_method_types' => array_values(array_filter(array_map('trim', explode(',', (string) env('PAYMONGO_PAYMENT_METHOD_TYPES', 'card,gcash'))))),
    ],

    'n8n' => [
        'webhook_url' => env('N8N_WEBHOOK_URL'),
        'webhook_token' => env('N8N_WEBHOOK_TOKEN'),
        'callback_bearer_token' => env('N8N_CALLBACK_BEARER_TOKEN'),
        'base_url' => env('N8N_BASE_URL'),
        'api_key' => env('N8N_API_KEY'),
        'workflow_id' => env('N8N_WORKFLOW_ID'),
        'webhook_retry_times' => (int) env('N8N_WEBHOOK_RETRY_TIMES', 1),
        'webhook_retry_delay_ms' => (int) env('N8N_WEBHOOK_RETRY_DELAY_MS', 200),
        'webhook_connect_timeout_seconds' => (int) env('N8N_WEBHOOK_CONNECT_TIMEOUT_SECONDS', 2),
        'webhook_timeout_seconds' => (int) env('N8N_WEBHOOK_TIMEOUT_SECONDS', 4),
        'send_payment_success_event' => (bool) env('N8N_SEND_PAYMENT_SUCCESS_EVENT', false),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

];
