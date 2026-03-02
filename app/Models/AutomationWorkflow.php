<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AutomationWorkflow extends Model
{
    public const TYPES = [
        'sequence' => 'Email/SMS sequence',
        'workflow' => 'Event workflow',
    ];

    public const STATUSES = [
        'active' => 'Active',
        'draft' => 'Draft',
        'inactive' => 'Inactive',
    ];

    protected $fillable = [
        'tenant_id',
        'name',
        'type',
        'trigger_tag',
        'is_active',
        'status',
        'n8n_workflow_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function triggers(): HasMany
    {
        return $this->hasMany(AutomationTrigger::class, 'automation_workflow_id');
    }

    public function sequenceSteps(): HasMany
    {
        return $this->hasMany(AutomationSequenceStep::class, 'automation_workflow_id')->orderBy('position');
    }

    /**
     * Display status: use status column when set, otherwise derive from is_active (backward compat).
     */
    public function getDisplayStatusAttribute(): string
    {
        if ($this->status !== null && $this->status !== '') {
            return $this->status;
        }
        return $this->is_active ? 'active' : 'inactive';
    }
}
