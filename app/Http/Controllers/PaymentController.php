<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Payment;
use App\Services\SubscriptionLifecycleService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Payment::with('lead')
            ->where('tenant_id', $user->tenant_id)
            ->latest('payment_date');

        if ($request->filled('status')) {
            $query->where('status', Payment::normalizeStatus($request->status));
        }

        $payments = $query->paginate(10);
        $leadOptions = Lead::where('tenant_id', $user->tenant_id)->orderBy('name')->get(['id', 'name']);
        $tenant = app(SubscriptionLifecycleService::class)->expireGracePeriodIfNeeded($user->tenant);
        $billingStateLabel = app(SubscriptionLifecycleService::class)->billingStateLabel($tenant);

        return view('payments.index', compact('payments', 'leadOptions', 'tenant', 'billingStateLabel'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'lead_id' => 'nullable|integer|exists:leads,id',
            'amount' => 'required|numeric|min:0.01',
            'status' => ['required', Rule::in(array_keys(Payment::STATUSES))],
            'payment_date' => 'required|date',
            'provider' => 'nullable|string|max:50',
            'provider_reference' => 'nullable|string|max:120',
            'payment_method' => 'nullable|string|max:50',
        ]);

        if (!empty($validated['lead_id'])) {
            $belongsToTenant = Lead::where('id', $validated['lead_id'])
                ->where('tenant_id', $user->tenant_id)
                ->exists();

            if (!$belongsToTenant) {
                abort(422, 'Selected lead is invalid.');
            }
        }

        try {
            $payment = Payment::create([
                'tenant_id' => $user->tenant_id,
                'lead_id' => $validated['lead_id'] ?? null,
                'amount' => $validated['amount'],
                'status' => $validated['status'],
                'payment_date' => $validated['payment_date'],
                'provider' => $validated['provider'] ?? null,
                'provider_reference' => $validated['provider_reference'] ?? null,
                'payment_method' => $validated['payment_method'] ?? null,
            ]);

            if ($payment->status === 'failed') {
                app(SubscriptionLifecycleService::class)->markPaymentFailed($payment);
            } elseif ($payment->status === 'paid') {
                app(SubscriptionLifecycleService::class)->restoreTenantBilling($user->tenant);
            }

            return redirect()->route('payments.index')->with('success', 'Added Successfully');
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', 'Added Failed');
        }
    }

    private function dispatchPaymentWebhookIfLeadExists(Payment $payment, string $status): void
    {
        if (!in_array($status, ['paid', 'failed'], true)) {
            return;
        }
        if (!$payment->lead_id) {
            return;
        }
        $lead = Lead::where('id', $payment->lead_id)->where('tenant_id', $payment->tenant_id)->first();
        if (!$lead) {
            return;
        }

        $event = $status === 'paid' ? 'payment.paid' : 'payment.failed';
        $service = app(AutomationWebhookService::class);

        // MVP rule: Payment paid => Closed Won (and notify n8n via lead.status_changed).
        if ($event === 'payment.paid') {
            $oldStatus = (string) ($lead->status ?? '');
            $newStatus = 'closed_won';

            if ($oldStatus !== $newStatus && !in_array($oldStatus, ['closed_lost', 'closed_won'], true)) {
                $lead->status = $newStatus;
                $lead->save();

                $lead->activities()->create([
                    'activity_type' => 'Scoring',
                    'notes' => 'Auto: Pipeline Stage updated to closed_won (+0 points)',
                ]);

                $statusPayload = $service->buildLeadStatusChangedPayload($lead, $oldStatus, $newStatus, []);
                $service->dispatchEvent('lead.status_changed', $statusPayload);
            }
        }

        $payload = $service->buildPaymentPayload($event, $lead, $payment, []);
        $service->dispatchEvent($event, $payload);
    }
}
