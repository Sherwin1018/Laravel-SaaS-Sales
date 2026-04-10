<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FunnelTemplateStep extends Model
{
    protected $fillable = [
        'funnel_template_id',
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
        'step_tags',
        'background_color',
        'button_color',
        'layout_json',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'decimal:2',
        'template_data' => 'array',
        'step_tags' => 'array',
        'layout_json' => 'array',
    ];

    public function funnelTemplate(): BelongsTo
    {
        return $this->belongsTo(FunnelTemplate::class);
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(FunnelTemplateStepRevision::class)->orderBy('created_at')->orderBy('id');
    }
}
