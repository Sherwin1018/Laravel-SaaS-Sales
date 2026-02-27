<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutomationSequenceStep extends Model
{
    protected $fillable = [
        'automation_workflow_id',
        'position',
        'channel',
        'sender_name',
        'subject',
        'body',
        'delay_minutes',
    ];

    protected $casts = [
        'position' => 'integer',
        'delay_minutes' => 'integer',
    ];

    public function automationWorkflow(): BelongsTo
    {
        return $this->belongsTo(AutomationWorkflow::class);
    }
}
