<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Plan extends Model
{
    protected $fillable = [
        'code',
        'name',
        'price',
        'period',
        'summary',
        'features',
        'spotlight',
        'is_active',
        'sort_order',
        'max_users',
        'max_leads',
        'max_funnels',
        'max_workflows',
        'max_monthly_messages',
        'automation_enabled',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'features' => 'array',
        'is_active' => 'boolean',
        'automation_enabled' => 'boolean',
    ];

    public function allowsUnlimited(string $attribute): bool
    {
        return $this->{$attribute} === null;
    }

    public static function resolveForSubscription(?string $subscriptionPlan): ?self
    {
        $normalized = Str::of((string) $subscriptionPlan)->trim()->lower()->toString();
        if ($normalized === '') {
            return null;
        }

        return self::query()
            ->where(function ($query) use ($normalized) {
                $query->whereRaw('LOWER(code) = ?', [$normalized])
                    ->orWhereRaw('LOWER(name) = ?', [$normalized]);
            })
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->first();
    }
}
