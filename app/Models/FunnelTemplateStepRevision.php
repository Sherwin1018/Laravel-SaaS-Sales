<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FunnelTemplateStepRevision extends Model
{
    protected $fillable = [
        'funnel_template_step_id',
        'user_id',
        'layout_json',
        'background_color',
        'version_type',
        'label',
    ];

    protected $casts = [
        'layout_json' => 'array',
    ];

    public function step(): BelongsTo
    {
        return $this->belongsTo(FunnelTemplateStep::class, 'funnel_template_step_id');
    }
}
