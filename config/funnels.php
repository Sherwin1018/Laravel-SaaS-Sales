<?php

return [
    'checkout_abandoned_after_seconds' => (int) env('FUNNEL_CHECKOUT_ABANDONED_AFTER_SECONDS', 86400),
    'paid_confirmation_email_enabled' => (bool) env('FUNNEL_PAID_CONFIRMATION_EMAIL_ENABLED', true),
    'lbc_delivery_cutoff_hour' => (int) env('FUNNEL_LBC_DELIVERY_CUTOFF_HOUR', 15),
    'lbc_delivery_windows' => [
        'metro_manila' => ['min_business_days' => 1, 'max_business_days' => 2],
        'luzon' => ['min_business_days' => 2, 'max_business_days' => 4],
        'visayas' => ['min_business_days' => 4, 'max_business_days' => 6],
        'mindanao' => ['min_business_days' => 5, 'max_business_days' => 7],
    ],
];
