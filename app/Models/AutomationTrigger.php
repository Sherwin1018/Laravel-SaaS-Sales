<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutomationTrigger extends Model
{
    public const EVENTS = [
        'lead.created' => 'Lead created',
        'funnel.opt_in' => 'Funnel opt-in',
        'lead.status_changed' => 'Lead status changed',
    ];

    protected $fillable = [
        'automation_workflow_id',
        'event',
        'n8n_webhook_path',
        'filters',
        'funnel_id',
    ];

    protected $casts = [
        'filters' => 'array',
    ];

    public function automationWorkflow(): BelongsTo
    {
        return $this->belongsTo(AutomationWorkflow::class);
    }

    public function funnel(): BelongsTo
    {
        return $this->belongsTo(Funnel::class);
    }
}
