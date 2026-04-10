<?php

return [

    /*
    |--------------------------------------------------------------------------
    | n8n Webhook Base URL
    |--------------------------------------------------------------------------
    |
    | Base URL for n8n instance (e.g. http://localhost:5678). Webhook paths
    | are appended to form full URL: {webhook_base_url}/webhook/{path}
    |
    */

    'webhook_base_url' => env('N8N_WEBHOOK_BASE_URL', 'http://localhost:5678'),

    /*
    |--------------------------------------------------------------------------
    | Webhook URL segment (webhook vs webhook-test)
    |--------------------------------------------------------------------------
    |
    | n8n uses /webhook/{path} for production and /webhook-test/{path} for test
    | mode. Set to "webhook-test" when using a test webhook on hosted n8n.
    |
    */
    'webhook_segment' => env('N8N_WEBHOOK_SEGMENT', 'webhook'),

    /*
    |--------------------------------------------------------------------------
    | Single webhook router (optional)
    |--------------------------------------------------------------------------
    |
    | When use_router is true, ALL automation events are sent to one URL:
    | {webhook_base_url}/webhook/{router_path}. The payload still contains
    | the "event" field so n8n can route internally. When false, each event
    | uses its own path from the paths array below.
    |
    */

    'use_router' => env('N8N_USE_ROUTER', false),
    'router_path' => env('N8N_ROUTER_PATH', 'saas-events'),

    /*
    |--------------------------------------------------------------------------
    | Webhook path keys per event
    |--------------------------------------------------------------------------
    |
    | Path segment after /webhook/ for each event type. Used only when
    | use_router is false. AutomationWebhookService / SendN8nWebhookJob
    | build the target URL from this or from router_path.
    |
    */

    'paths' => [
        'lead_created' => env('N8N_WEBHOOK_LEAD_CREATED', 'lead-created'),
        'funnel_opt_in' => env('N8N_WEBHOOK_FUNNEL_OPT_IN', 'funnel-opt-in'),
        'lead_status_changed' => env('N8N_WEBHOOK_LEAD_STATUS_CHANGED', 'lead-status-changed'),
        'payment_paid' => env('N8N_WEBHOOK_PAYMENT_PAID', 'payment-paid'),
        'payment_failed' => env('N8N_WEBHOOK_PAYMENT_FAILED', 'payment-failed'),
        'saas_events' => env('N8N_WEBHOOK_SAAS_EVENTS', 'saas-events'),
    ],

];
