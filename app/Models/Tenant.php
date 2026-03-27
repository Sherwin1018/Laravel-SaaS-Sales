<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Tenant extends Model
{
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
}
