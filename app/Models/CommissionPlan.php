<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommissionPlan extends Model
{
    protected $fillable = [
        'tenant_id',
        'name',
        'is_active',
        'is_default',
        'gateway_fee_rate',
        'platform_fee_rate',
        'sales_agent_rate',
        'marketing_manager_rate',
        'affiliate_sale_rate',
        'platform_referral_rate',
        'hold_days',
        'sales_attribution_model',
        'marketing_attribution_model',
        'default_marketing_manager_user_id',
        'config',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'gateway_fee_rate' => 'decimal:2',
        'platform_fee_rate' => 'decimal:2',
        'sales_agent_rate' => 'decimal:2',
        'marketing_manager_rate' => 'decimal:2',
        'affiliate_sale_rate' => 'decimal:2',
        'platform_referral_rate' => 'decimal:2',
        'hold_days' => 'integer',
        'config' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function defaultMarketingManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'default_marketing_manager_user_id');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(CommissionEntry::class);
    }
}
