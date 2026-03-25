<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FunnelStepRevision extends Model
{
    protected $fillable = [
        'funnel_step_id',
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
        return $this->belongsTo(FunnelStep::class, 'funnel_step_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
