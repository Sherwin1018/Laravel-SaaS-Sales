<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AutomationSequence;
use App\Models\AutomationSequenceStep;
use App\Models\AutomationWorkflow;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Internal API for n8n to request tenant workflow actions.
 * Protected by X-Automation-Token when AUTOMATION_RUNNER_TOKEN is set.
 */
class TenantAutomationRunController extends Controller
{
    public function run(Request $request): JsonResponse
    {
        $token = env('AUTOMATION_RUNNER_TOKEN');
        if ($token !== null && $token !== '') {
            $headerToken = $request->header('X-Automation-Token');
            if ($headerToken !== $token) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }
        }

        $event = $request->input('event') ?? $request->input('body.event');
        $tenantIdInput = $request->input('tenant_id') ?? $request->input('body.tenant_id') ?? $request->input('payload.tenant_id');
        $payloadInput = $request->input('payload') ?? $request->input('body');

        if (empty($event) || !is_string($event)) {
            return response()->json(['message' => 'The event field is required and must be a string.', 'errors' => ['event' => ['The event field is required.']]], 422);
        }
        $event = trim($event);
        if (strlen($event) > 100) {
            return response()->json(['message' => 'Validation failed.', 'errors' => ['event' => ['The event must not exceed 100 characters.']]], 422);
        }

        $tenantId = $this->normalizeTenantId($tenantIdInput);
        if ($tenantId < 1) {
            return response()->json(['message' => 'The tenant_id field is required and must be a number (integer).', 'errors' => ['tenant_id' => ['The tenant_id field must be a number.']]], 422);
        }

        $payload = $this->normalizePayload($payloadInput);

        $actions = $this->buildActions($tenantId, $event, $payload);

        $fromEmail = $this->resolveTenantFromEmail($tenantId);

        return response()->json([
            'actions' => $actions,
            'from_email' => $fromEmail,
        ]);
    }

    /**
     * Accept tenant_id as int, numeric string, or array with id/tenant_id key.
     */
    private function normalizeTenantId(mixed $value): int
    {
        if (is_numeric($value)) {
            return (int) $value;
        }
        if (is_array($value)) {
            $v = $value['tenant_id'] ?? $value['id'] ?? null;
            return is_numeric($v) ? (int) $v : 0;
        }
        if (is_object($value)) {
            $v = $value->tenant_id ?? $value->id ?? null;
            return is_numeric($v) ? (int) $v : 0;
        }
        return 0;
    }

    /**
     * Accept payload as array or JSON string; always return array.
     */
    private function normalizePayload(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }
        return [];
    }

    /**
     * Resolve "From" email for this tenant (SaaS: real tenant email).
     * 1. Tenant's automation_from_email (Profile → Automation From Email) if set.
     * 2. Else the tenant's account owner user email.
     * 3. Else null (n8n can use its own fallback).
     */
    private function resolveTenantFromEmail(int $tenantId): ?string
    {
        $tenant = Tenant::find($tenantId);
        if (!$tenant) {
            return null;
        }
        $from = trim((string) ($tenant->automation_from_email ?? ''));
        if ($from !== '' && filter_var($from, FILTER_VALIDATE_EMAIL)) {
            return $from;
        }
        $owner = User::where('tenant_id', $tenantId)
            ->whereHas('roles', fn ($q) => $q->where('slug', 'account-owner'))
            ->first();
        if ($owner && $owner->email) {
            $email = trim((string) $owner->email);
            return $email !== '' ? $email : null;
        }
        return null;
    }

    /**
     * Query tenant workflows matching event and build actions for n8n.
     */
    private function buildActions(int $tenantId, string $event, array $payload): array
    {
        $query = AutomationWorkflow::where('tenant_id', $tenantId)
            ->where('trigger_event', $event)
            ->where('is_active', true)
            ->where('type', AutomationWorkflow::TYPE_TENANT);

        // Optional funnel scoping for funnel.opt_in based on trigger_filters->funnel_id
        if ($event === 'funnel.opt_in') {
            $payloadFunnelId = 0;
            if (isset($payload['funnel_id']) && is_numeric($payload['funnel_id'])) {
                $payloadFunnelId = (int) $payload['funnel_id'];
            } elseif (isset($payload['body']['funnel_id']) && is_numeric($payload['body']['funnel_id'])) {
                $payloadFunnelId = (int) $payload['body']['funnel_id'];
            }

            if ($payloadFunnelId > 0) {
                $query->where(function ($q) use ($payloadFunnelId) {
                    $q->whereNull('trigger_filters->funnel_id')
                        ->orWhere('trigger_filters->funnel_id', $payloadFunnelId);
                });
            }
        }

        $workflows = $query->get();

        $actions = [];
        foreach ($workflows as $workflow) {
            if ($workflow->action_type === 'send_email') {
                $action = $this->buildEmailAction($workflow);
                if ($action !== null) {
                    $actions[] = $action;
                }
            } elseif ($workflow->action_type === 'start_sequence') {
                $action = $this->buildSequenceAction($workflow, $tenantId);
                if ($action !== null) {
                    $actions[] = $action;
                }
            } elseif ($workflow->action_type === 'notify_sales') {
                $action = $this->buildNotifySalesAction($workflow);
                if ($action !== null) {
                    $actions[] = $action;
                }
            }
        }

        return $actions;
    }

    /**
     * Build one notify_sales action for n8n (internal notification to assigned agent).
     */
    private function buildNotifySalesAction(AutomationWorkflow $workflow): array
    {
        return [
            'workflow_id' => $workflow->id,
            'type' => 'notify_sales',
        ];
    }

    /**
     * Build one send_email action from workflow action_config.
     */
    private function buildEmailAction(AutomationWorkflow $workflow): ?array
    {
        $config = $workflow->action_config ?? [];
        $recipient = $config['recipient'] ?? 'lead.email';
        if (!in_array($recipient, ['lead.email', 'assigned_agent.email'], true)) {
            $recipient = 'lead.email';
        }

        return [
            'workflow_id' => $workflow->id,
            'type' => 'send_email',
            'to' => $recipient,
            'subject' => $config['subject'] ?? '',
            'body' => $config['body'] ?? '',
        ];
    }

    /**
     * Load a sequence for the tenant if it exists, is active, and belongs to the tenant.
     */
    private function loadTenantSequence(int $tenantId, int $sequenceId): ?AutomationSequence
    {
        $sequence = AutomationSequence::where('tenant_id', $tenantId)
            ->where('id', $sequenceId)
            ->where('is_active', true)
            ->with('steps')
            ->first();

        return $sequence;
    }

    /**
     * Build one start_sequence action: load sequence and compile steps for n8n.
     */
    private function buildSequenceAction(AutomationWorkflow $workflow, int $tenantId): ?array
    {
        $config = $workflow->action_config ?? [];
        $sequenceId = isset($config['sequence_id']) ? (int) $config['sequence_id'] : 0;
        if ($sequenceId < 1) {
            return null;
        }

        $sequence = $this->loadTenantSequence($tenantId, $sequenceId);
        if ($sequence === null || $sequence->steps->isEmpty()) {
            return null;
        }

        $compiledSteps = [];
        foreach ($sequence->steps as $step) {
            $compiled = $this->compileSequenceStep($step);
            if ($compiled !== null) {
                $compiledSteps[] = $compiled;
            }
        }

        if (empty($compiledSteps)) {
            return null;
        }

        return [
            'workflow_id' => $workflow->id,
            'type' => 'start_sequence',
            'sequence_id' => $sequence->id,
            'sequence_name' => $sequence->name,
            'steps' => $compiledSteps,
        ];
    }

    /**
     * Compile a single sequence step into the response shape. Returns null for unsupported types.
     */
    private function compileSequenceStep(AutomationSequenceStep $step): ?array
    {
        $config = $step->config ?? [];
        $base = [
            'step_order' => (int) $step->step_order,
            'type' => $step->type,
        ];

        if ($step->type === 'email') {
            $recipient = $config['recipient'] ?? 'lead.email';
            if (!in_array($recipient, ['lead.email', 'assigned_agent.email'], true)) {
                $recipient = 'lead.email';
            }
            return array_merge($base, [
                'recipient' => $recipient,
                'subject' => $config['subject'] ?? '',
                'body' => $config['body'] ?? '',
            ]);
        }

        if ($step->type === 'delay') {
            return array_merge($base, [
                'duration' => (int) ($config['duration'] ?? 1),
                'unit' => in_array($config['unit'] ?? '', ['minutes', 'hours', 'days'], true)
                    ? $config['unit']
                    : 'days',
            ]);
        }

        if ($step->type === 'sms') {
            $recipient = $config['recipient'] ?? 'lead.phone';
            // For now we only support lead.phone; normalize anything else back to lead.phone
            if ($recipient !== 'lead.phone') {
                $recipient = 'lead.phone';
            }
            return array_merge($base, [
                'recipient' => $recipient,
                'body' => $config['body'] ?? '',
            ]);
        }

        return null;
    }
}
