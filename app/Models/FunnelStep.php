<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FunnelStep extends Model
{
    public const TYPES = [
        'landing' => 'Landing Page',
        'opt_in' => 'Opt-in Page',
        'sales' => 'Sales Page',
        'checkout' => 'Checkout Page',
        'upsell' => 'Upsell Page',
        'downsell' => 'Downsell Page',
        'thank_you' => 'Thank You Page',
    ];

    protected $fillable = [
        'funnel_id',
        'title',
        'slug',
        'type',
        'content',
        'cta_label',
        'price',
        'position',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'decimal:2',
    ];

    public function funnel(): BelongsTo
    {
        return $this->belongsTo(Funnel::class);
    }
}
