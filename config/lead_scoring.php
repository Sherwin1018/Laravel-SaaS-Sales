<?php

return [
    'manual_events' => [
        'email_opened' => [
            'label' => 'Email Opened',
            'points' => 5,
        ],
        'link_clicked' => [
            'label' => 'Link Clicked',
            'points' => 10,
        ],
        'form_submitted' => [
            'label' => 'Form Submitted',
            'points' => 20,
        ],
    ],
    'stage_events' => [
        'contacted' => [
            'label' => 'Moved to Contacted',
            'points' => 5,
        ],
        'proposal_sent' => [
            'label' => 'Moved to Proposal Sent',
            'points' => 10,
        ],
        'closed_won' => [
            'label' => 'Moved to Closed Won',
            'points' => 50,
        ],
    ],
    'source_campaign_events' => [
        'facebook_ads' => [
            'label' => 'Facebook Ads Lead',
            'points' => 10,
        ],
        'referral' => [
            'label' => 'Referral Lead',
            'points' => 15,
        ],
        'email_campaign' => [
            'label' => 'Email Campaign Lead',
            'points' => 8,
        ],
        'webinar' => [
            'label' => 'Webinar Lead',
            'points' => 12,
        ],
    ],
];
