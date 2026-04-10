<?php

namespace App\Http\Controllers;

use App\Models\Funnel;
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

        $baseQuery = Payment::with(['lead', 'funnel', 'step'])
            ->where('tenant_id', $user->tenant_id)
            ->latest('payment_date');

        $platformStats = $this->buildPaymentStats(
            Payment::query()
                ->where('tenant_id', $user->tenant_id)
                ->platformSubscriptions()
        );

        $funnelStats = $this->buildPaymentStats(
            Payment::query()
                ->where('tenant_id', $user->tenant_id)
                ->funnelSales()
        );

        $platformSubscriptions = (clone $baseQuery)
            ->platformSubscriptions()
            ->paginate(10, ['*'], 'subscriptions_page');

        $funnelSales = (clone $baseQuery)
            ->funnelSales()
            ->paginate(10, ['*'], 'sales_page');

        $leadOptions = Lead::where('tenant_id', $user->tenant_id)->orderBy('name')->get(['id', 'name']);
        $funnelOptions = Funnel::where('tenant_id', $user->tenant_id)
            ->with(['steps' => function ($query) {
                $query->where('is_active', true)
                    ->whereIn('type', ['checkout', 'upsell', 'downsell'])
                    ->orderBy('position');
            }])
            ->orderBy('name')
            ->get(['id', 'name']);
        $tenant = app(SubscriptionLifecycleService::class)->expireGracePeriodIfNeeded($user->tenant);
        $billingStateLabel = app(SubscriptionLifecycleService::class)->billingStateLabel($tenant);

        return view('payments.index', compact(
            'platformStats',
            'funnelStats',
            'platformSubscriptions',
            'funnelSales',
            'leadOptions',
            'funnelOptions',
            'tenant',
            'billingStateLabel'
        ));
    }

    private function buildPaymentStats($query): array
    {
        $payments = (clone $query)->get(['amount', 'status']);

        $paidTotal = (float) $payments->where('status', 'paid')->sum('amount');
        $pendingTotal = (float) $payments->where('status', 'pending')->sum('amount');
        $failedTotal = (float) $payments->where('status', 'failed')->sum('amount');
        $outstandingCount = $payments->where('status', 'pending')->count();

        return [
            'paid_total' => $paidTotal,
            'pending_total' => $pendingTotal,
            'failed_total' => $failedTotal,
            'outstanding_count' => $outstandingCount,
            'outstanding_amount' => $pendingTotal,
        ];
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'payment_type' => ['required', Rule::in(array_keys(Payment::TYPES))],
            'lead_id' => 'nullable|integer|exists:leads,id',
            'funnel_id' => 'nullable|integer|exists:funnels,id',
            'funnel_step_id' => 'nullable|integer|exists:funnel_steps,id',
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

        $paymentType = Payment::normalizeType($validated['payment_type']);
        $funnelId = null;
        $funnelStepId = null;

        if ($paymentType === Payment::TYPE_FUNNEL_CHECKOUT) {
            if (empty($validated['funnel_id']) || empty($validated['funnel_step_id'])) {
                abort(422, 'Funnel and funnel step are required for funnel sales.');
            }

            $funnelExists = Funnel::where('id', $validated['funnel_id'])
                ->where('tenant_id', $user->tenant_id)
                ->exists();

            if (! $funnelExists) {
                abort(422, 'Selected funnel is invalid.');
            }

            $stepExists = Funnel::where('tenant_id', $user->tenant_id)
                ->where('id', $validated['funnel_id'])
                ->whereHas('steps', function ($query) use ($validated) {
                    $query->where('funnel_steps.id', $validated['funnel_step_id']);
                })
                ->exists();

            if (! $stepExists) {
                abort(422, 'Selected funnel step is invalid.');
            }

            $funnelId = (int) $validated['funnel_id'];
            $funnelStepId = (int) $validated['funnel_step_id'];
        }

        try {
            $payment = Payment::create([
                'tenant_id' => $user->tenant_id,
                'payment_type' => $paymentType,
                'funnel_id' => $funnelId,
                'funnel_step_id' => $funnelStepId,
                'lead_id' => $validated['lead_id'] ?? null,
                'amount' => $validated['amount'],
                'status' => $validated['status'],
                'payment_date' => $validated['payment_date'],
                'provider' => $validated['provider'] ?? null,
                'provider_reference' => $validated['provider_reference'] ?? null,
                'payment_method' => $validated['payment_method'] ?? null,
            ]);

            if ($payment->isPlatformSubscription() && $payment->status === 'failed') {
                app(SubscriptionLifecycleService::class)->markPaymentFailed($payment);
            } elseif ($payment->isPlatformSubscription() && $payment->status === 'paid') {
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
