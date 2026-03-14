<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AutomationWorkflow extends Model
{
    public const TYPE_TENANT = 'tenant';
    public const TYPE_SYSTEM = 'system';

    protected $fillable = [
        'tenant_id',
        'name',
        'type',
        'trigger_event',
        'trigger_filters',
        'action_type',
        'action_config',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'trigger_filters' => 'array',
        'action_config' => 'array',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(AutomationLog::class, 'workflow_id');
    }

    public function isTenant(): bool
    {
        return $this->type === self::TYPE_TENANT;
    }

    public function isSystem(): bool
    {
        return $this->type === self::TYPE_SYSTEM;
    }
}
