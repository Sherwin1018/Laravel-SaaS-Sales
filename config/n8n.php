<?php

return [

    /*
    |--------------------------------------------------------------------------
    | n8n Webhook Base URL
    |--------------------------------------------------------------------------
    | Base URL of your n8n instance (no trailing slash). Used to build
    | webhook URLs for lead.created, funnel.opt_in, lead.status_changed.
    */
    'webhook_base_url' => env('N8N_WEBHOOK_BASE_URL', 'http://localhost:5678'),

    /*
    |--------------------------------------------------------------------------
    | Webhook Paths (production: /webhook/...)
    |--------------------------------------------------------------------------
    | Path segment only (no leading slash). Full URL = base_url + '/webhook/' + path.
    | Override via env if your n8n workflows use different paths.
    */
    'paths' => [
        'lead_created' => env('N8N_WEBHOOK_LEAD_CREATED', 'lead-created'),
        'funnel_opt_in' => env('N8N_WEBHOOK_FUNNEL_OPT_IN', 'funnel-opt-in'),
        'lead_status_changed' => env('N8N_WEBHOOK_LEAD_STATUS_CHANGED', 'lead-status-changed'),
    ],

];
