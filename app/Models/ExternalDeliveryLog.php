<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExternalDeliveryLog extends Model
{
    protected $fillable = [
        'tenant_id',
        'user_id',
        'lead_id',
        'channel',
        'event_name',
        'recipient',
        'provider',
        'status',
        'response_code',
        'error_message',
        'idempotency_key',
        'is_billable',
        'meta',
        'sent_at',
    ];

    protected $casts = [
        'is_billable' => 'boolean',
        'meta' => 'array',
        'sent_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }
}
