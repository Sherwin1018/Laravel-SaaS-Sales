<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookReceipt extends Model
{
    protected $fillable = [
        'provider',
        'event_id',
        'event_type',
        'payload_hash',
        'status',
        'attempts',
        'processed_at',
        'last_error',
        'meta',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
        'meta' => 'array',
    ];
}
