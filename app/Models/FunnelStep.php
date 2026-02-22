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

    public const LAYOUTS = [
        'centered' => 'Centered',
        'split_left' => 'Split (Image Left)',
        'split_right' => 'Split (Image Right)',
    ];

    public const TEMPLATES = [
        'simple' => 'Simple (Title + Content)',
        'hero_features' => 'Hero + Features',
        'lead_capture' => 'Lead Capture',
        'sales_long' => 'Sales Page (Long)',
        'thank_you_next' => 'Thank You + Next Steps',
    ];

    protected $fillable = [
        'funnel_id',
        'title',
        'subtitle',
        'slug',
        'type',
        'content',
        'cta_label',
        'price',
        'position',
        'is_active',
        'hero_image_url',
        'layout_style',
        'template',
        'template_data',
        'background_color',
        'button_color',
        'layout_json',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'decimal:2',
        'template_data' => 'array',
        'layout_json' => 'array',
    ];

    public function funnel(): BelongsTo
    {
        return $this->belongsTo(Funnel::class);
    }
}
