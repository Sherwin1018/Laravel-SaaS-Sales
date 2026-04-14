<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    public const SCOPE_PLATFORM = 'platform';
    public const SCOPE_TENANT = 'tenant';

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_EXHAUSTED = 'exhausted';

    public const DISCOUNT_FIXED = 'fixed';
    public const DISCOUNT_PERCENT = 'percent';

    public const USAGE_SINGLE = 'single_use';
    public const USAGE_MULTI = 'multi_use';

    protected $fillable = [
        'tenant_id',
        'created_by',
        'scope_type',
        'code',
        'title',
        'description',
        'status',
        'discount_type',
        'discount_value',
        'usage_mode',
        'max_total_uses',
        'max_uses_per_user',
        'times_used',
        'starts_at',
        'ends_at',
        'used_at',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignedTenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class, 'coupon_assignments')
            ->withTimestamps();
    }

    public function funnels(): BelongsToMany
    {
        return $this->belongsToMany(Funnel::class, 'coupon_funnels')
            ->withTimestamps();
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(CouponRedemption::class);
    }

    public function setCodeAttribute($value): void
    {
        $this->attributes['code'] = self::normalizeCode($value);
    }

    public static function normalizeCode(mixed $value): string
    {
        $normalized = strtoupper(trim((string) $value));
        $normalized = preg_replace('/[^A-Z0-9]/', '', $normalized) ?? '';

        return mb_substr($normalized, 0, 40);
    }

    public function scopePlatform($query)
    {
        return $query->where('scope_type', self::SCOPE_PLATFORM);
    }

    public function scopeTenantOwned($query)
    {
        return $query->where('scope_type', self::SCOPE_TENANT);
    }

    public function scopeVisibleToTenant($query, int $tenantId)
    {
        return $query->where(function ($builder) use ($tenantId) {
            $builder->where(function ($tenantOwned) use ($tenantId) {
                $tenantOwned->where('scope_type', self::SCOPE_TENANT)
                    ->where('tenant_id', $tenantId);
            })->orWhere(function ($platformOwned) use ($tenantId) {
                $platformOwned->where('scope_type', self::SCOPE_PLATFORM)
                    ->whereHas('assignedTenants', fn ($assignment) => $assignment->where('tenants.id', $tenantId));
            });
        });
    }
}
