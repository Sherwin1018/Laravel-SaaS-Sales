<?php

namespace App\Http\Controllers;

use App\Models\Funnel;
use App\Models\FinanceAuditLog;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\PaymentReceipt;
use App\Models\Tenant;
use App\Services\CommissionService;
use App\Services\FinanceAuditService;
use App\Services\FunnelTrackingService;
use App\Services\ReceiptVerificationService;
use App\Services\SubscriptionLifecycleService;
use App\Support\TenantPlanEnforcer;
use App\Support\TenantPayoutReadiness;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $receiptFilter = trim((string) $request->query('receipts_filter', ''));
        $tenant = app(SubscriptionLifecycleService::class)->expireGracePeriodIfNeeded($user->tenant);
        app(CommissionService::class)->syncTenant($tenant);

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
        $billingStateLabel = app(SubscriptionLifecycleService::class)->billingStateLabel($tenant);
        $receiptOptions = Payment::query()
            ->where('tenant_id', $user->tenant_id)
            ->latest('payment_date')
            ->limit(100)
            ->get(['id', 'amount', 'payment_type', 'status', 'payment_date', 'provider_reference']);
        $receiptsBase = PaymentReceipt::query()
            ->with(['payment:id,amount,payment_date,payment_type,status', 'uploader:id,name', 'reviewer:id,name'])
            ->where('tenant_id', $user->tenant_id)
            ->when($receiptFilter === 'manual_pending', function ($query) {
                $query->where('provider', 'manual_transfer')->where('status', PaymentReceipt::STATUS_PENDING);
            })
            ->latest('id')
            ->paginate(10, ['*'], 'receipts_page')
            ->withQueryString();

        $receipts = $receiptsBase;
        $receiptStats = [
            'pending' => PaymentReceipt::query()->where('tenant_id', $user->tenant_id)->where('status', PaymentReceipt::STATUS_PENDING)->count(),
            'auto_approved' => PaymentReceipt::query()->where('tenant_id', $user->tenant_id)->where('status', PaymentReceipt::STATUS_AUTO_APPROVED)->count(),
            'approved' => PaymentReceipt::query()->where('tenant_id', $user->tenant_id)->where('status', PaymentReceipt::STATUS_APPROVED)->count(),
            'rejected' => PaymentReceipt::query()->where('tenant_id', $user->tenant_id)->where('status', PaymentReceipt::STATUS_REJECTED)->count(),
            'manual_pending' => PaymentReceipt::query()
                ->where('tenant_id', $user->tenant_id)
                ->where('provider', 'manual_transfer')
                ->where('status', PaymentReceipt::STATUS_PENDING)
                ->count(),
        ];
        $commissionSummary = [
            'held_total' => (float) $tenant->commissionEntries()->where('status', 'held')->sum('commission_amount'),
            'payable_total' => (float) $tenant->commissionEntries()->where('status', 'payable')->sum('commission_amount'),
        ];
        $planUsage = app(TenantPlanEnforcer::class)->usageSummary($tenant);
        $recentAuditLogs = FinanceAuditLog::query()
            ->with('actor:id,name')
            ->where('tenant_id', $user->tenant_id)
            ->latest('occurred_at')
            ->latest('id')
            ->limit(8)
            ->get();

        return view('payments.index', compact(
            'platformStats',
            'funnelStats',
            'platformSubscriptions',
            'funnelSales',
            'leadOptions',
            'funnelOptions',
            'tenant',
            'billingStateLabel',
            'receiptOptions',
            'receipts',
            'receiptStats',
            'commissionSummary',
            'planUsage',
            'recentAuditLogs',
            'receiptFilter'
        ));
    }

    public function adminReceiptsIndex(Request $request)
    {
        $tenantId = (int) $request->query('tenant_id', 0);
        $tenantOptions = Tenant::query()
            ->orderBy('company_name')
            ->get(['id', 'company_name']);

        $receiptOptions = Payment::query()
            ->with('tenant:id,company_name')
            ->when($tenantId > 0, fn ($query) => $query->where('tenant_id', $tenantId))
            ->latest('payment_date')
            ->latest('id')
            ->limit(150)
            ->get(['id', 'tenant_id', 'amount', 'payment_type', 'status', 'payment_date', 'provider_reference']);

        $receipts = PaymentReceipt::query()
            ->with([
                'tenant:id,company_name',
                'payment:id,tenant_id,amount,payment_date,payment_type,status',
                'uploader:id,name',
                'reviewer:id,name',
            ])
            ->when($tenantId > 0, fn ($query) => $query->where('tenant_id', $tenantId))
            ->latest('id')
            ->paginate(15);

        $receiptStatsBase = PaymentReceipt::query()
            ->when($tenantId > 0, fn ($query) => $query->where('tenant_id', $tenantId));

        $receiptStats = [
            'pending' => (clone $receiptStatsBase)->where('status', PaymentReceipt::STATUS_PENDING)->count(),
            'auto_approved' => (clone $receiptStatsBase)->where('status', PaymentReceipt::STATUS_AUTO_APPROVED)->count(),
            'approved' => (clone $receiptStatsBase)->where('status', PaymentReceipt::STATUS_APPROVED)->count(),
            'rejected' => (clone $receiptStatsBase)->where('status', PaymentReceipt::STATUS_REJECTED)->count(),
        ];

        $recentAuditLogs = FinanceAuditLog::query()
            ->with(['actor:id,name', 'tenant:id,company_name'])
            ->when($tenantId > 0, fn ($query) => $query->where('tenant_id', $tenantId))
            ->latest('occurred_at')
            ->latest('id')
            ->limit(12)
            ->get();

        return view('admin.receipts.index', compact(
            'tenantId',
            'tenantOptions',
            'receiptOptions',
            'receipts',
            'receiptStats',
            'recentAuditLogs'
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

            $monetizationDecision = app(TenantPayoutReadiness::class)->monetizationDecisionForTenant($user->tenant);
            if (! $monetizationDecision['allowed']) {
                return redirect()->back()->withInput()->with('error', $monetizationDecision['message']);
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
                app(SubscriptionLifecycleService::class)->restoreTenantBilling($user->tenant, $payment);
            } elseif ($payment->isFunnelSale() && $payment->status === 'paid') {
                app(CommissionService::class)->syncPayment($payment);
            } elseif ($payment->isFunnelSale() && $payment->status === 'failed') {
                app(CommissionService::class)->reverseForPayment($payment, 'manual_payment_failed');
            }

            app(FinanceAuditService::class)->record(
                'payment_recorded',
                'Manual payment record saved.',
                $user,
                $user->tenant,
                $payment,
                null,
                [
                    'payment_type' => $payment->payment_type,
                    'status' => $payment->status,
                    'amount' => (float) $payment->amount,
                    'provider' => $payment->provider,
                    'provider_reference' => $payment->provider_reference,
                ]
            );

            return redirect()->route('payments.index')->with('success', 'Added Successfully');
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', 'Added Failed');
        }
    }

    public function storeReceipt(Request $request, ReceiptVerificationService $verification)
    {
        $user = auth()->user();
        [$payment, $receipt] = $this->createReceiptFromRequest($request, $verification, $user, false);

        if (in_array($receipt->status, [PaymentReceipt::STATUS_AUTO_APPROVED, PaymentReceipt::STATUS_APPROVED], true)) {
            $this->markPaymentConfirmedFromReceipt($payment->fresh(), 'receipt_upload_auto_approved', $user, $receipt);
        }

        return redirect()->route('payments.index')->with('success', 'Added Successfully');
    }

    public function adminStoreReceipt(Request $request, ReceiptVerificationService $verification)
    {
        $user = auth()->user();
        [$payment, $receipt] = $this->createReceiptFromRequest($request, $verification, $user, true);

        if (in_array($receipt->status, [PaymentReceipt::STATUS_AUTO_APPROVED, PaymentReceipt::STATUS_APPROVED], true)) {
            $this->markPaymentConfirmedFromReceipt($payment->fresh(), 'receipt_upload_auto_approved', $user, $receipt);
        }

        return redirect()->route('admin.receipts.index')->with('success', 'Added Successfully');
    }

    public function reviewReceipt(Request $request, PaymentReceipt $receipt, ReceiptVerificationService $verification)
    {
        $user = auth()->user();
        $this->authorizeReceiptReview($user, $receipt, false);

        $validated = $request->validate([
            'decision' => 'required|in:approve,reject',
            'notes' => 'nullable|string|max:500',
        ]);

        $receipt = $verification->review($receipt, $validated['decision'], $user, $validated['notes'] ?? null);
        app(FinanceAuditService::class)->record(
            'receipt_reviewed',
            'Receipt was reviewed manually.',
            $user,
            $receipt->tenant,
            $receipt->payment,
            $receipt,
            [
                'decision' => $validated['decision'],
                'status' => $receipt->status,
                'notes' => $validated['notes'] ?? null,
            ]
        );
        if ($validated['decision'] === 'approve') {
            $this->markPaymentConfirmedFromReceipt($receipt->payment()->firstOrFail(), 'receipt_review_approved', $user, $receipt);
        }

        return redirect()->route('payments.index')->with('success', 'Edited Successfully');
    }

    public function adminReviewReceipt(Request $request, PaymentReceipt $receipt, ReceiptVerificationService $verification)
    {
        $user = auth()->user();
        $this->authorizeReceiptReview($user, $receipt, true);

        $validated = $request->validate([
            'decision' => 'required|in:approve,reject',
            'notes' => 'nullable|string|max:500',
        ]);

        $receipt = $verification->review($receipt, $validated['decision'], $user, $validated['notes'] ?? null);
        app(FinanceAuditService::class)->record(
            'receipt_reviewed',
            'Receipt was reviewed by super admin.',
            $user,
            $receipt->tenant,
            $receipt->payment,
            $receipt,
            [
                'decision' => $validated['decision'],
                'status' => $receipt->status,
                'notes' => $validated['notes'] ?? null,
                'review_scope' => 'super_admin',
            ]
        );
        if ($validated['decision'] === 'approve') {
            $this->markPaymentConfirmedFromReceipt($receipt->payment()->firstOrFail(), 'receipt_review_approved', $user, $receipt);
        }

        return redirect()->route('admin.receipts.index')->with('success', 'Edited Successfully');
    }

    private function markPaymentConfirmedFromReceipt(
        Payment $payment,
        string $source,
        ?\App\Models\User $actor = null,
        ?PaymentReceipt $receipt = null
    ): void
    {
        if ($payment->status !== 'paid') {
            $payment->update([
                'status' => 'paid',
                'payment_date' => $payment->payment_date ?: now()->toDateString(),
            ]);
        }

        if ($payment->isPlatformSubscription()) {
            app(SubscriptionLifecycleService::class)->restoreTenantBilling($payment->tenant, $payment);
            app(FinanceAuditService::class)->record(
                'payment_confirmed_from_receipt',
                'Platform subscription payment confirmed from receipt.',
                $actor,
                $payment->tenant,
                $payment,
                $receipt,
                ['source' => $source]
            );

            return;
        }

        app(CommissionService::class)->syncPayment($payment->fresh());
        app(FinanceAuditService::class)->record(
            'payment_confirmed_from_receipt',
            'Funnel payment confirmed from receipt.',
            $actor,
            $payment->tenant,
            $payment,
            $receipt,
            ['source' => $source]
        );
        try {
            app(FunnelTrackingService::class)->trackPaymentPaid($payment->fresh(), ['source' => $source]);
        } catch (\Throwable) {
            // Tracking is best-effort for manual receipt confirmation.
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function dispatchAutomationEvent(string $eventName, array $payload): void
    {
        try {
            app(\App\Services\N8nEmailOrchestrator::class)->dispatch($eventName, $payload);
        } catch (\Throwable) {
            // Best-effort dispatch only.
        }
    }

    /**
     * @return array{0: Payment, 1: PaymentReceipt}
     */
    private function createReceiptFromRequest(
        Request $request,
        ReceiptVerificationService $verification,
        \App\Models\User $user,
        bool $crossTenant = false
    ): array {
        $validated = $request->validate([
            'payment_id' => 'required|integer|exists:payments,id',
            'receipt_amount' => 'nullable|numeric|min:0.01',
            'receipt_date' => 'nullable|date',
            'provider' => 'nullable|string|max:50',
            'reference_number' => 'nullable|string|max:160',
            'receipt_file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'notes' => 'nullable|string|max:500',
        ]);

        $payment = $this->resolveReceiptPayment($user, (int) $validated['payment_id'], $crossTenant);
        $path = $request->file('receipt_file')->store('payment-receipts', 'public');

        $receipt = PaymentReceipt::create([
            'tenant_id' => $payment->tenant_id,
            'payment_id' => $payment->id,
            'uploaded_by' => $user->id,
            'receipt_amount' => $validated['receipt_amount'] ?? null,
            'receipt_date' => $validated['receipt_date'] ?? null,
            'provider' => $validated['provider'] ?? null,
            'reference_number' => $validated['reference_number'] ?? null,
            'receipt_path' => $path,
            'notes' => $validated['notes'] ?? null,
            'status' => PaymentReceipt::STATUS_PENDING,
            'automation_status' => 'pending',
        ]);

        $this->dispatchAutomationEvent('receipt_uploaded', [
            'tenant_id' => $payment->tenant_id,
            'payment_id' => $payment->id,
            'receipt_id' => $receipt->id,
            'uploaded_by' => $user->id,
        ]);
        app(FinanceAuditService::class)->record(
            'receipt_uploaded',
            $crossTenant ? 'Receipt uploaded by super admin.' : 'Receipt uploaded.',
            $user,
            $payment->tenant,
            $payment,
            $receipt,
            [
                'payment_type' => $payment->payment_type,
                'cross_tenant' => $crossTenant,
            ]
        );

        $receipt = $verification->evaluate($receipt);
        if ($receipt->status === PaymentReceipt::STATUS_AUTO_APPROVED) {
            app(FinanceAuditService::class)->record(
                'receipt_auto_approved',
                'Receipt passed automated verification.',
                $user,
                $payment->tenant,
                $payment,
                $receipt,
                [
                    'automation_status' => $receipt->automation_status,
                    'automation_reason' => $receipt->automation_reason,
                ]
            );
        }

        return [$payment, $receipt];
    }

    private function resolveReceiptPayment(\App\Models\User $user, int $paymentId, bool $crossTenant = false): Payment
    {
        return Payment::query()
            ->when(! $crossTenant, fn ($query) => $query->where('tenant_id', $user->tenant_id))
            ->findOrFail($paymentId);
    }

    private function authorizeReceiptReview(\App\Models\User $user, PaymentReceipt $receipt, bool $crossTenant = false): void
    {
        if ($crossTenant) {
            abort_unless($user->hasRole('super-admin'), 403);

            return;
        }

        abort_unless($receipt->tenant_id === $user->tenant_id, 403);
        abort_unless($user->hasAnyRole(['finance', 'account-owner']), 403);
    }
}
