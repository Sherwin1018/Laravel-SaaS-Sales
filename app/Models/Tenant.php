<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Tenant extends Model
{
    public const STATUSES = [
        'active' => 'Active',
        'inactive' => 'Inactive',
        'trial' => 'Trial',
    ];

    public const BILLING_STATUSES = [
        'current' => 'Current',
        'overdue' => 'Overdue',
        'inactive' => 'Inactive',
        'trial' => 'Trial',
    ];

    protected $fillable = [
        'company_name',
        'logo_path',
        'subscription_plan',
        'status',
        'billing_status',
        'billing_grace_ends_at',
        'last_payment_failed_at',
        'subscription_activated_at',
        'theme_primary_color',
        'theme_accent_color',
        'theme_sidebar_bg',
        'theme_sidebar_text',
        'trial_starts_at',
        'trial_ends_at',
    ];

    protected $casts = [
        'trial_starts_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'billing_grace_ends_at' => 'datetime',
        'last_payment_failed_at' => 'datetime',
        'subscription_activated_at' => 'datetime',
    ];

    public function setStatusAttribute($value): void
    {
        $this->attributes['status'] = self::normalizeStatus($value);
    }

    public function setBillingStatusAttribute($value): void
    {
        $this->attributes['billing_status'] = self::normalizeBillingStatus($value);
    }

    public static function normalizeStatus(mixed $value): string
    {
        $normalized = mb_strtolower(trim((string) $value));

        return array_key_exists($normalized, self::STATUSES) ? $normalized : $normalized;
    }

    public static function normalizeBillingStatus(mixed $value): string
    {
        $normalized = mb_strtolower(trim((string) $value));

        return array_key_exists($normalized, self::BILLING_STATUSES) ? $normalized : $normalized;
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function leads()
    {
        return $this->hasMany(Lead::class);
    }

    public function funnels()
    {
        return $this->hasMany(Funnel::class);
    }

    public function coupons()
    {
        return $this->hasMany(Coupon::class);
    }

    public function customFields()
    {
        return $this->hasMany(TenantCustomField::class)->orderBy('sort_order');
    }

    public function isOnTrial(): bool
    {
        return $this->status === 'trial' && $this->trial_ends_at instanceof Carbon;
    }

    public function isInactive(): bool
    {
        return $this->status === 'inactive';
    }

    public function isOverdue(): bool
    {
        return $this->billing_status === 'overdue';
    }

    public function isTrialExpired(): bool
    {
        return $this->isOnTrial() && now()->greaterThan($this->trial_ends_at);
    }

    public function trialDaysRemaining(): int
    {
        if (! $this->isOnTrial()) {
            return 0;
        }

        $trialEnd = $this->trial_ends_at->copy();
        $currentTime = now();
        if ($currentTime->greaterThan($trialEnd)) {
            return 0;
        }

        return $currentTime->startOfDay()->diffInDays($trialEnd->copy()->startOfDay()) + 1;
    }

    public function billingGraceDaysRemaining(): int
    {
        if (! $this->billing_grace_ends_at) {
            return 0;
        }

        if (now()->greaterThan($this->billing_grace_ends_at)) {
            return 0;
        }

        return now()->startOfDay()->diffInDays($this->billing_grace_ends_at->copy()->startOfDay()) + 1;
    }
}
