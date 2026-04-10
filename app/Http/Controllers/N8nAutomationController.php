<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Payment;
use App\Models\Tenant;
use App\Models\User;
use App\Services\SubscriptionLifecycleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class N8nAutomationController extends Controller
{
    public function leadActivity(Request $request)
    {
        $this->authorizeN8n($request);

        $validated = $request->validate([
            'tenant_id' => 'required|integer|exists:tenants,id',
            'lead_id' => 'required|integer|exists:leads,id',
            'activity_type' => 'required|string|max:100',
            'notes' => 'nullable|string|max:2000',
        ]);

        $lead = Lead::withoutGlobalScope('tenant')
            ->where('id', $validated['lead_id'])
            ->where('tenant_id', $validated['tenant_id'])
            ->firstOrFail();

        $activity = $lead->activities()->create([
            'activity_type' => $validated['activity_type'],
            'notes' => $validated['notes'] ?? '',
        ]);

        return response()->json(['ok' => true, 'id' => $activity->id]);
    }

    public function leadScore(Request $request)
    {
        $this->authorizeN8n($request);

        $validated = $request->validate([
            'tenant_id' => 'required|integer|exists:tenants,id',
            'lead_id' => 'required|integer|exists:leads,id',
            'event' => 'required|string|max:120',
            'points' => 'nullable|integer|min:0|max:10000',
        ]);

        $lead = Lead::withoutGlobalScope('tenant')
            ->where('id', $validated['lead_id'])
            ->where('tenant_id', $validated['tenant_id'])
            ->firstOrFail();

        $points = (int) ($validated['points'] ?? 0);
        if ($points > 0) {
            $lead->increment('score', $points);
        }

        $lead->activities()->create([
            'activity_type' => 'Scoring',
            'notes' => $validated['event'] . " (+{$points} points)",
        ]);

        $lead->refresh();

        return response()->json([
            'ok' => true,
            'lead_id' => $lead->id,
            'score' => (int) $lead->score,
        ]);
    }

    public function sendEmail(Request $request)
    {
        $this->authorizeN8n($request);

        $validated = $request->validate([
            'tenant_id' => 'required|integer|exists:tenants,id',
            'lead_id' => 'nullable|integer|exists:leads,id',
            'user_id' => 'nullable|integer|exists:users,id',
            'template' => 'required|string|max:160',
            'invoice_id' => 'nullable|string|max:120',
        ]);

        $tenantId = (int) $validated['tenant_id'];
        $recipient = null;
        $lead = null;

        if (! empty($validated['lead_id'])) {
            $lead = Lead::withoutGlobalScope('tenant')
                ->where('id', (int) $validated['lead_id'])
                ->where('tenant_id', $tenantId)
                ->first();
            if ($lead?->email) {
                $recipient = $lead->email;
            }
        }

        if (! $recipient && ! empty($validated['user_id'])) {
            $user = User::query()
                ->where('id', (int) $validated['user_id'])
                ->where('tenant_id', $tenantId)
                ->first();
            if ($user?->email) {
                $recipient = $user->email;
            }
        }

        if (! $recipient) {
            $owner = User::query()
                ->where('tenant_id', $tenantId)
                ->where('role', 'account-owner')
                ->orderBy('id')
                ->first();
            $recipient = $owner?->email;
        }

        if (! $recipient) {
            throw ValidationException::withMessages([
                'recipient' => 'Could not resolve recipient email for this request.',
            ]);
        }

        $template = (string) $validated['template'];
        $subject = 'Automation Notice: ' . str_replace('_', ' ', $template);
        $body = "Template: {$template}\nTenant ID: {$tenantId}";
        if (! empty($validated['invoice_id'])) {
            $body .= "\nInvoice ID: " . $validated['invoice_id'];
        }
        if ($lead) {
            $body .= "\nLead: {$lead->name} ({$lead->email})";
        }

        try {
            Mail::raw($body, function ($message) use ($recipient, $subject) {
                $message->to($recipient)->subject($subject);
            });
        } catch (\Throwable $e) {
            Log::warning('N8N send-email fallback logging due to mail failure.', [
                'recipient' => $recipient,
                'template' => $template,
                'error' => $e->getMessage(),
            ]);
        }

        if ($lead) {
            $lead->activities()->create([
                'activity_type' => 'Email Sent',
                'notes' => 'Template: ' . $template,
            ]);
        }

        return response()->json([
            'ok' => true,
            'recipient' => $recipient,
            'template' => $template,
        ]);
    }

    public function sendSms(Request $request)
    {
        $this->authorizeN8n($request);

        $validated = $request->validate([
            'tenant_id' => 'required|integer|exists:tenants,id',
            'lead_id' => 'nullable|integer|exists:leads,id',
            'template' => 'required|string|max:160',
            'invoice_id' => 'nullable|string|max:120',
        ]);

        $tenantId = (int) $validated['tenant_id'];
        $phone = null;
        $lead = null;

        if (! empty($validated['lead_id'])) {
            $lead = Lead::withoutGlobalScope('tenant')
                ->where('id', (int) $validated['lead_id'])
                ->where('tenant_id', $tenantId)
                ->first();
            $phone = $lead?->phone;
        }

        Log::info('N8N SMS simulation dispatch.', [
            'tenant_id' => $tenantId,
            'lead_id' => $lead?->id,
            'phone' => $phone,
            'template' => $validated['template'],
            'invoice_id' => $validated['invoice_id'] ?? null,
        ]);

        if ($lead) {
            $lead->activities()->create([
                'activity_type' => 'SMS Sent',
                'notes' => 'Template: ' . $validated['template'],
            ]);
        }

        return response()->json([
            'ok' => true,
            'simulated' => true,
            'phone' => $phone,
        ]);
    }

    public function agentTask(Request $request)
    {
        $this->authorizeN8n($request);

        $validated = $request->validate([
            'tenant_id' => 'required|integer|exists:tenants,id',
            'lead_id' => 'required|integer|exists:leads,id',
            'title' => 'required|string|max:255',
            'priority' => 'nullable|string|max:50',
        ]);

        $lead = Lead::withoutGlobalScope('tenant')
            ->where('id', $validated['lead_id'])
            ->where('tenant_id', $validated['tenant_id'])
            ->firstOrFail();

        $lead->activities()->create([
            'activity_type' => 'Task',
            'notes' => $validated['title'] . ' (Priority: ' . ($validated['priority'] ?? 'normal') . ')',
        ]);

        return response()->json(['ok' => true]);
    }

    public function invoiceStatus(Request $request)
    {
        $this->authorizeN8n($request);

        $validated = $request->validate([
            'tenant_id' => 'required|integer|exists:tenants,id',
            'invoice_id' => 'nullable|string|max:120',
        ]);

        $query = Payment::query()
            ->where('tenant_id', (int) $validated['tenant_id'])
            ->platformSubscriptions()
            ->latest('id');

        $invoiceId = (string) ($validated['invoice_id'] ?? '');
        if ($invoiceId !== '') {
            $query->where(function ($q) use ($invoiceId) {
                $q->where('provider_reference', $invoiceId);
                if (is_numeric($invoiceId)) {
                    $q->orWhere('id', (int) $invoiceId);
                }
            });
        }

        $payment = $query->first();

        return response()->json([
            'ok' => true,
            'status' => $payment?->status ?? 'unknown',
            'invoice_id' => $invoiceId !== '' ? $invoiceId : null,
            'payment_id' => $payment?->id,
        ]);
    }

    public function suspendSubscription(Request $request)
    {
        $this->authorizeN8n($request);

        $validated = $request->validate([
            'tenant_id' => 'required|integer|exists:tenants,id',
            'reason' => 'nullable|string|max:255',
            'invoice_id' => 'nullable|string|max:120',
        ]);

        $tenant = Tenant::query()->findOrFail((int) $validated['tenant_id']);
        $tenant->update([
            'status' => 'inactive',
            'billing_status' => 'inactive',
        ]);

        Log::warning('Tenant suspended from N8N automation endpoint.', [
            'tenant_id' => $tenant->id,
            'invoice_id' => $validated['invoice_id'] ?? null,
            'reason' => $validated['reason'] ?? null,
        ]);

        return response()->json(['ok' => true, 'tenant_status' => $tenant->fresh()->status]);
    }

    public function paymentRecovered(Request $request)
    {
        $this->authorizeN8n($request);

        $validated = $request->validate([
            'tenant_id' => 'required|integer|exists:tenants,id',
            'invoice_id' => 'nullable|string|max:120',
        ]);

        $tenant = Tenant::query()->findOrFail((int) $validated['tenant_id']);
        $tenant = app(SubscriptionLifecycleService::class)->restoreTenantBilling($tenant);

        return response()->json([
            'ok' => true,
            'tenant_status' => $tenant->status,
            'billing_status' => $tenant->billing_status,
        ]);
    }

    public function automationLog(Request $request)
    {
        $this->authorizeN8n($request);

        $validated = $request->validate([
            'tenant_id' => 'required|integer|exists:tenants,id',
            'event_name' => 'required|string|max:120',
            'payload' => 'nullable|string',
        ]);

        Log::info('N8N automation-log endpoint called.', $validated);

        return response()->json(['ok' => true]);
    }

    public function analyticsDaily(Request $request)
    {
        $this->authorizeN8n($request);

        $tenantCount = Tenant::query()->count();
        $leadCount = Lead::withoutGlobalScope('tenant')->count();
        $paidCount = Payment::query()->where('status', 'paid')->count();
        $failedCount = Payment::query()->where('status', 'failed')->count();
        $mrr = (float) Payment::query()
            ->platformSubscriptions()
            ->where('status', 'paid')
            ->whereDate('payment_date', '>=', now()->startOfMonth())
            ->sum('amount');

        return response()->json([
            'ok' => true,
            'date' => now()->toDateString(),
            'tenants' => $tenantCount,
            'leads' => $leadCount,
            'payments_paid' => $paidCount,
            'payments_failed' => $failedCount,
            'mrr' => $mrr,
        ]);
    }

    public function analyticsStore(Request $request)
    {
        $this->authorizeN8n($request);

        $validated = $request->validate([
            'date' => 'required|date',
            'snapshot' => 'required|string',
        ]);

        Cache::put('n8n.analytics.snapshot.' . $validated['date'], $validated['snapshot'], now()->addDays(60));

        return response()->json(['ok' => true]);
    }

    public function sendOwnerDigest(Request $request)
    {
        $this->authorizeN8n($request);

        $validated = $request->validate([
            'date' => 'nullable|date',
            'audience' => 'nullable|string|max:80',
            'include_upgrade_nudges' => 'nullable',
        ]);

        $audience = (string) ($validated['audience'] ?? 'all_owners');
        $users = User::query()->where('role', 'account-owner');
        if ($audience === 'trial_owners') {
            $users->whereHas('tenant', function ($query) {
                $query->where('status', 'trial');
            });
        }

        $sent = 0;
        foreach ($users->get(['email']) as $user) {
            if (! $user->email) {
                continue;
            }
            try {
                Mail::raw(
                    'Owner digest generated for ' . ($validated['date'] ?? now()->toDateString()),
                    function ($message) use ($user) {
                        $message->to($user->email)->subject('Owner Digest');
                    }
                );
            } catch (\Throwable $e) {
                Log::warning('Owner digest email fallback logging due to mail failure.', [
                    'email' => $user->email,
                    'error' => $e->getMessage(),
                ]);
            }
            $sent++;
        }

        return response()->json(['ok' => true, 'sent' => $sent]);
    }

    public function trialInactiveRecovery(Request $request)
    {
        $this->authorizeN8n($request);

        $validated = $request->validate([
            'tenant_id' => 'required|integer|exists:tenants,id',
            'user_id' => 'nullable|integer|exists:users,id',
            'reason' => 'nullable|string|max:120',
        ]);

        Log::info('Trial inactive recovery signal received.', $validated);

        return response()->json(['ok' => true]);
    }

    public function runInactiveTrialRecovery(Request $request)
    {
        $this->authorizeN8n($request);

        $validated = $request->validate([
            'date' => 'nullable|date',
        ]);

        $processed = 0;
        $tenants = Tenant::query()
            ->where('status', 'trial')
            ->whereNotNull('trial_ends_at')
            ->whereDate('trial_ends_at', '<=', now()->addDays(2)->toDateString())
            ->get(['id']);

        foreach ($tenants as $tenant) {
            Log::info('Trial recovery candidate.', [
                'tenant_id' => $tenant->id,
                'run_date' => $validated['date'] ?? now()->toDateString(),
            ]);
            $processed++;
        }

        return response()->json(['ok' => true, 'processed' => $processed]);
    }

    private function authorizeN8n(Request $request): void
    {
        $callbackToken = (string) config('services.n8n.callback_bearer_token');
        $webhookToken = (string) config('services.n8n.webhook_token');
        $expected = $callbackToken !== '' ? $callbackToken : $webhookToken;
        if ($expected === '') {
            return;
        }

        $authorization = (string) $request->header('Authorization');
        $received = trim(str_ireplace('Bearer', '', $authorization));
        if (! hash_equals($expected, $received)) {
            abort(401, 'Unauthorized');
        }
    }
}

