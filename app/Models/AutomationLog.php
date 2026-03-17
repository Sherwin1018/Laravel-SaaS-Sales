<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutomationLog extends Model
{
    protected $fillable = [
        'tenant_id',
        'workflow_id',
        'event',
        'status',
        'error_message',
        'payload',
        'ran_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'ran_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(AutomationWorkflow::class, 'workflow_id');
    }

    /**
     * Record an automation run for tenant logs. Call from job/listener when automation executes.
     */
    public static function record(
        int $tenantId,
        ?int $workflowId,
        string $event,
        string $status,
        ?string $errorMessage = null,
        ?array $payload = null
    ): self {
        return self::create([
            'tenant_id' => $tenantId,
            'workflow_id' => $workflowId,
            'event' => $event,
            'status' => $status,
            'error_message' => $errorMessage,
            'payload' => $payload,
            'ran_at' => now(),
        ]);
    }
}
