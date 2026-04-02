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
    ];

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

    public function isOnTrial(): bool
    {
        return $this->status === 'trial' && $this->trial_ends_at instanceof Carbon;
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

        $trialEnd = $this->trial_ends_at->copy()->endOfDay();
        if (now()->greaterThan($trialEnd)) {
            return 0;
        }

        return now()->startOfDay()->diffInDays($trialEnd->copy()->startOfDay()) + 1;
    }

    public function setStatusAttribute($value): void
    {
        $this->attributes['status'] = self::normalizeStatus($value);
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
}
