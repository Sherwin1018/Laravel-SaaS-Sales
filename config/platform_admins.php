<?php

return [
    'setup_link_expires_hours' => (int) env('PLATFORM_ADMIN_SETUP_LINK_EXPIRES_HOURS', 24),

    'accounts' => [
        [
            'role_slug' => 'super-admin',
            'role_key' => 'super_admin',
            'name' => env('PLATFORM_SUPER_ADMIN_NAME', 'Super Admin'),
            'email' => env('PLATFORM_SUPER_ADMIN_EMAIL', 'superadmin@gmail.com'),
        ],
        [
            'role_slug' => 'payout-admin',
            'role_key' => 'payout_admin',
            'name' => env('PLATFORM_PAYOUT_ADMIN_NAME', 'Platform Finance Admin'),
            'email' => env('PLATFORM_PAYOUT_ADMIN_EMAIL', 'platform.finance.admin@gmail.com'),
        ],
    ],
];
