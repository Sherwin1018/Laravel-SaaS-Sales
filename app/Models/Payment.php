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
        'source_funnel_template_id',
        'funnel_step_id',
        'lead_id',
        'coupon_id',
        'coupon_code',
        'source_platform',
        'source_medium',
        'source_campaign',
        'source_content',
        'referrer_user_id',
        'referral_code_snapshot',
        'assigned_sales_user_id',
        'amount',
        'refund_amount',
        'non_commissionable_amount',
        'commissionable_amount',
        'gateway_fee_amount',
        'platform_share_amount',
        'template_royalty_amount',
        'affiliate_commission_amount',
        'sales_commission_amount',
        'marketing_commission_amount',
        'tenant_net_income_amount',
        'subtotal_amount',
        'discount_amount',
        'status',
        'payment_date',
        'provider',
        'provider_reference',
        'payment_method',
        'session_identifier',
        'platform_payout_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'non_commissionable_amount' => 'decimal:2',
        'commissionable_amount' => 'decimal:2',
        'gateway_fee_amount' => 'decimal:2',
        'platform_share_amount' => 'decimal:2',
        'template_royalty_amount' => 'decimal:2',
        'affiliate_commission_amount' => 'decimal:2',
        'sales_commission_amount' => 'decimal:2',
        'marketing_commission_amount' => 'decimal:2',
        'tenant_net_income_amount' => 'decimal:2',
        'subtotal_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
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

    public function sourceTemplate(): BelongsTo
    {
        return $this->belongsTo(FunnelTemplate::class, 'source_funnel_template_id');
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_user_id');
    }

    public function assignedSalesUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_sales_user_id');
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(FunnelEvent::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(FunnelReview::class);
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(PaymentReceipt::class);
    }

    public function commissionEntries(): HasMany
    {
        return $this->hasMany(CommissionEntry::class);
    }
}
