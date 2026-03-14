<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutomationSequenceStep extends Model
{
    protected $fillable = [
        'sequence_id',
        'step_order',
        'type',
        'config',
        // Legacy columns kept for compatibility with older schema
        'body',
    ];

    protected $casts = [
        'config' => 'array',
    ];

    public function sequence(): BelongsTo
    {
        return $this->belongsTo(AutomationSequence::class);
    }
}
