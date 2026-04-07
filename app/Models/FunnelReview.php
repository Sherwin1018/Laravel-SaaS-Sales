<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FunnelReview extends Model
{
    public const STATUSES = [
        'pending' => 'Pending',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
    ];

    protected $fillable = [
        'tenant_id',
        'funnel_id',
        'funnel_step_id',
        'lead_id',
        'payment_id',
        'approved_by',
        'customer_name',
        'customer_email',
        'rating',
        'review_text',
        'status',
        'is_public',
        'source',
        'meta',
        'approved_at',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'meta' => 'array',
        'approved_at' => 'datetime',
    ];

    public function setStatusAttribute($value): void
    {
        $normalized = strtolower(trim((string) $value));
        $this->attributes['status'] = array_key_exists($normalized, self::STATUSES) ? $normalized : 'pending';
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function funnel(): BelongsTo
    {
        return $this->belongsTo(Funnel::class);
    }

    public function step(): BelongsTo
    {
        return $this->belongsTo(FunnelStep::class, 'funnel_step_id');
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
