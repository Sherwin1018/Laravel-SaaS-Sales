<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantPayoutAccount extends Model
{
    public const STATUS_PENDING_PLATFORM_REVIEW = 'pending_platform_review';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public const STATUS_LABELS = [
        self::STATUS_PENDING_PLATFORM_REVIEW => 'Pending platform review',
        self::STATUS_APPROVED => 'Approved',
        self::STATUS_REJECTED => 'Rejected',
    ];

    protected $fillable = [
        'tenant_id',
        'destination_type',
        'account_name',
        'destination_value',
        'masked_destination',
        'provider_destination_reference',
        'is_verified',
        'verified_at',
        'verified_by',
        'verification_status',
        'reviewed_at',
        'reviewed_by',
        'review_notes',
        'is_default',
        'meta',
    ];

    protected $casts = [
        'destination_value' => 'encrypted',
        'is_verified' => 'boolean',
        'is_default' => 'boolean',
        'verified_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'meta' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function reviewStatus(): string
    {
        $status = trim((string) ($this->verification_status ?? ''));

        if ($status !== '') {
            return $status;
        }

        return $this->is_verified ? self::STATUS_APPROVED : self::STATUS_PENDING_PLATFORM_REVIEW;
    }

    public function reviewStatusLabel(): string
    {
        $status = $this->reviewStatus();

        return self::STATUS_LABELS[$status] ?? ucwords(str_replace('_', ' ', $status));
    }

    public function isPendingPlatformReview(): bool
    {
        return $this->reviewStatus() === self::STATUS_PENDING_PLATFORM_REVIEW;
    }

    public function isApproved(): bool
    {
        return $this->reviewStatus() === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->reviewStatus() === self::STATUS_REJECTED;
    }

    public function hasDestinationDetails(): bool
    {
        return trim((string) $this->account_name) !== ''
            && trim((string) $this->destination_type) !== ''
            && (
                trim((string) $this->masked_destination) !== ''
                || trim((string) $this->provider_destination_reference) !== ''
                || trim((string) ($this->destination_value ?? '')) !== ''
            );
    }

    public function resolvedDestination(): ?string
    {
        $destination = trim((string) ($this->destination_value ?? ''));
        if ($destination !== '') {
            return $destination;
        }

        $reference = trim((string) ($this->provider_destination_reference ?? ''));
        if ($reference !== '') {
            return $reference;
        }

        $masked = trim((string) ($this->masked_destination ?? ''));

        return $masked !== '' ? $masked : null;
    }
}
