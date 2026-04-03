<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    public const TYPE_PLATFORM_SUBSCRIPTION = 'platform_subscription';
    public const TYPE_FUNNEL_CHECKOUT = 'funnel_checkout';

    public const STATUSES = [
        'pending' => 'Pending',
        'paid' => 'Paid',
        'failed' => 'Failed',
    ];

    public const TYPES = [
        self::TYPE_PLATFORM_SUBSCRIPTION => 'Platform Subscription',
        self::TYPE_FUNNEL_CHECKOUT => 'Funnel Sale',
    ];

    protected $fillable = [
        'tenant_id',
        'payment_type',
        'funnel_id',
        'funnel_step_id',
        'lead_id',
        'amount',
        'status',
        'payment_date',
        'provider',
        'provider_reference',
        'payment_method',
        'session_identifier',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function setStatusAttribute($value): void
    {
        $this->attributes['status'] = self::normalizeStatus($value);
    }

    public function setPaymentTypeAttribute($value): void
    {
        $this->attributes['payment_type'] = self::normalizeType($value);
    }

    public static function normalizeStatus(mixed $value): string
    {
        $normalized = mb_strtolower(trim((string) $value));

        return array_key_exists($normalized, self::STATUSES) ? $normalized : $normalized;
    }

    public static function normalizeType(mixed $value): string
    {
        $normalized = mb_strtolower(trim((string) $value));

        return array_key_exists($normalized, self::TYPES)
            ? $normalized
            : self::TYPE_PLATFORM_SUBSCRIPTION;
    }

    public function scopePlatformSubscriptions($query)
    {
        return $query->where('payment_type', self::TYPE_PLATFORM_SUBSCRIPTION);
    }

    public function scopeFunnelSales($query)
    {
        return $query->where('payment_type', self::TYPE_FUNNEL_CHECKOUT);
    }

    public function isPlatformSubscription(): bool
    {
        return $this->payment_type === self::TYPE_PLATFORM_SUBSCRIPTION;
    }

    public function isFunnelSale(): bool
    {
        return $this->payment_type === self::TYPE_FUNNEL_CHECKOUT;
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

    public function events(): HasMany
    {
        return $this->hasMany(FunnelEvent::class);
    }
}
