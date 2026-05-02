<?php

namespace App\Http\Controllers;

use App\Models\PlatformPayout;
use App\Models\Payment;
use App\Models\Tenant;
use App\Services\N8nEmailOrchestrator;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class PlatformSettlementController extends Controller
{
    public function index(Request $request)
    {
        $status = trim((string) $request->query('status', 'unpaid'));
        $allowed = ['unpaid', 'paid', 'all'];
        $status = in_array($status, $allowed, true) ? $status : 'unpaid';
        $settlementSchema = $this->settlementSchemaStatus();

        $monthStart = now()->startOfMonth();

        $paidFunnelTotal = (float) Payment::query()
            ->where('payment_type', Payment::TYPE_FUNNEL_CHECKOUT)
            ->where('status', 'paid')
            ->sum('amount');
        $paidSubscriptionTotal = (float) Payment::query()
            ->where('payment_type', Payment::TYPE_PLATFORM_SUBSCRIPTION)
            ->where('status', 'paid')
            ->sum('amount');
        $pendingTotal = (float) Payment::query()
            ->where('status', 'pending')
            ->sum('amount');
        $unpaidLiabilityTotal = 0.0;
        $payoutsThisMonthTotal = 0.0;
        $collectedThisMonthTotal = (float) Payment::query()
            ->where('status', 'paid')
            ->where('created_at', '>=', $monthStart)
            ->sum('amount');

        $tenants = $this->emptyPaginator($request, 'page');
        $unpaidTotals = collect();
        $recentPayouts = $this->emptyPaginator($request, 'payouts_page');

        if ($settlementSchema['ready']) {
            $unpaidLiabilityTotal = (float) Payment::query()
                ->where('payment_type', Payment::TYPE_FUNNEL_CHECKOUT)
                ->where('status', 'paid')
                ->whereNull('platform_payout_id')
                ->sum('amount');

            $payoutsThisMonthTotal = (float) PlatformPayout::query()
                ->where('status', 'paid')
                ->where('paid_at', '>=', $monthStart)
                ->sum('amount');

            $tenants = Tenant::query()
                ->with('defaultPayoutAccount')
                ->when($status === 'unpaid', function ($query) {
                    $query->whereHas('payments', function ($q) {
                        $q->where('payment_type', Payment::TYPE_FUNNEL_CHECKOUT)
                            ->where('status', 'paid')
                            ->whereNull('platform_payout_id');
                    });
                })
                ->orderBy('id', 'desc')
                ->paginate(12)
                ->withQueryString();

            $tenantIds = $tenants->pluck('id')->all();
            $unpaidTotals = Payment::query()
                ->whereIn('tenant_id', $tenantIds)
                ->where('payment_type', Payment::TYPE_FUNNEL_CHECKOUT)
                ->where('status', 'paid')
                ->whereNull('platform_payout_id')
                ->selectRaw('tenant_id, COUNT(*) as paid_count, COALESCE(SUM(amount),0) as paid_total')
                ->groupBy('tenant_id')
                ->get()
                ->keyBy('tenant_id');

            $recentPayouts = PlatformPayout::query()
                ->with(['tenant:id,company_name', 'paidByUser:id,name,email'])
                ->when($status === 'paid', fn ($q) => $q->where('status', 'paid'))
                ->when($status === 'unpaid', fn ($q) => $q->where('status', 'pending'))
                ->latest('id')
                ->paginate(12, ['*'], 'payouts_page')
                ->withQueryString();
        }

        return view('platform.settlements', [
            'tenants' => $tenants,
            'unpaidTotals' => $unpaidTotals,
            'recentPayouts' => $recentPayouts,
            'statusFilter' => $status,
            'settlementSchema' => $settlementSchema,
            'metrics' => [
                'paid_funnel_total' => $paidFunnelTotal,
                'paid_subscription_total' => $paidSubscriptionTotal,
                'pending_total' => $pendingTotal,
                'unpaid_liability_total' => $unpaidLiabilityTotal,
                'payouts_this_month_total' => $payoutsThisMonthTotal,
                'collected_this_month_total' => $collectedThisMonthTotal,
                'month_label' => $monthStart->format('F Y'),
            ],
        ]);
    }

    public function store(Request $request, Tenant $tenant)
    {
        $settlementSchema = $this->settlementSchemaStatus();
        if (! $settlementSchema['ready']) {
            return redirect()
                ->route('platform.settlements.index')
                ->with('error', $settlementSchema['message']);
        }

        $validated = $request->validate([
            'amount' => 'nullable|numeric|min:0.01',
            'payment_reference' => 'nullable|string|max:160',
            'notes' => 'nullable|string|max:1000',
        ]);

        $tenant->load('defaultPayoutAccount');
        $payoutAccount = $tenant->defaultPayoutAccount;
        abort_unless($payoutAccount && $payoutAccount->isApproved(), 422, 'Tenant payout destination is not approved.');

        $unpaidPayments = Payment::query()
            ->where('tenant_id', $tenant->id)
            ->where('payment_type', Payment::TYPE_FUNNEL_CHECKOUT)
            ->where('status', 'paid')
            ->whereNull('platform_payout_id')
            ->orderBy('id')
            ->get(['id', 'amount']);

        abort_if($unpaidPayments->isEmpty(), 422, 'No unpaid earnings available for this tenant.');

        $computedTotal = (float) $unpaidPayments->sum('amount');
        $amount = isset($validated['amount']) ? (float) $validated['amount'] : $computedTotal;
        abort_if($amount <= 0, 422, 'Invalid payout amount.');
        abort_if($amount - $computedTotal > 0.00001, 422, 'Payout amount exceeds unpaid confirmed earnings.');
        $coveredPayments = $this->coveredPaymentsForAmount($unpaidPayments, $amount);
        abort_if($coveredPayments === null, 422, 'Payout amount must match the sum of complete unpaid payments starting from the oldest item.');

        $payoutAttributes = [
            'tenant_id' => $tenant->id,
            'amount' => $amount,
            'destination_type' => $payoutAccount->destination_type,
            'masked_destination' => $payoutAccount->masked_destination,
            'payment_reference' => trim((string) ($validated['payment_reference'] ?? '')) ?: null,
            'status' => 'paid',
            'paid_at' => now(),
            'paid_by' => auth()->id(),
            'notes' => trim((string) ($validated['notes'] ?? '')) ?: null,
        ];
        if (Schema::hasColumn('platform_payouts', 'destination_value_snapshot')) {
            $payoutAttributes['destination_value_snapshot'] = $payoutAccount->resolvedDestination();
        }
        if (Schema::hasColumn('platform_payouts', 'account_name_snapshot')) {
            $payoutAttributes['account_name_snapshot'] = $payoutAccount->account_name;
        }

        $payout = PlatformPayout::create($payoutAttributes);

        foreach ($coveredPayments as $payment) {
            $payment->update(['platform_payout_id' => $payout->id]);
        }

        $this->dispatchSettlementRecordedEvent($tenant, $payout, $coveredPayments, $payoutAccount);

        return redirect()
            ->route('platform.settlements.index')
            ->with('success', 'Payout recorded successfully.');
    }

    private function settlementSchemaStatus(): array
    {
        $issues = [];

        if (! Schema::hasTable('platform_payouts')) {
            $issues[] = 'Missing `platform_payouts` table.';
        }

        if (! Schema::hasColumn('payments', 'platform_payout_id')) {
            $issues[] = 'Missing `payments.platform_payout_id` column.';
        }

        return [
            'ready' => $issues === [],
            'issues' => $issues,
            'message' => $issues === []
                ? null
                : 'Settlements require the latest payout database migrations before this screen can be used safely.',
        ];
    }

    private function emptyPaginator(Request $request, string $pageName): LengthAwarePaginator
    {
        return new LengthAwarePaginator(
            [],
            0,
            12,
            LengthAwarePaginator::resolveCurrentPage($pageName),
            [
                'path' => $request->url(),
                'pageName' => $pageName,
                'query' => $request->query(),
            ]
        );
    }

    private function coveredPaymentsForAmount(Collection $payments, float $amount): ?Collection
    {
        $remaining = round($amount, 2);
        $covered = collect();

        foreach ($payments as $payment) {
            if ($remaining <= 0.00001) {
                break;
            }

            $paymentAmount = round((float) $payment->amount, 2);
            if ($paymentAmount - $remaining > 0.00001) {
                return null;
            }

            $covered->push($payment);
            $remaining = round($remaining - $paymentAmount, 2);
        }

        return $remaining <= 0.00001 ? $covered : null;
    }

    private function dispatchSettlementRecordedEvent(
        Tenant $tenant,
        PlatformPayout $payout,
        Collection $coveredPayments,
        $payoutAccount
    ): void {
        $owner = $tenant->users()
            ->whereHas('roles', fn ($query) => $query->where('slug', 'account-owner'))
            ->orderBy('id')
            ->first(['id', 'name', 'email']);

        app(N8nEmailOrchestrator::class)->dispatch('settlement_payout_recorded', array_filter([
            'event_id' => 'platform_payout:' . $payout->id,
            'idempotency_key' => 'platform_payout:' . $payout->id,
            'tenant_id' => $tenant->id,
            'tenant_name' => $tenant->company_name,
            'platform_payout_id' => $payout->id,
            'payout_amount' => (float) $payout->amount,
            'amount' => (float) $payout->amount,
            'destination_type' => $payout->destination_type,
            'masked_destination' => $payout->masked_destination,
            'account_name' => $payoutAccount?->account_name,
            'payment_reference' => $payout->payment_reference,
            'payments_count' => $coveredPayments->count(),
            'payment_ids' => $coveredPayments->pluck('id')->values()->all(),
            'paid_at' => optional($payout->paid_at)->toIso8601String(),
            'paid_by_user_id' => auth()->id(),
            'paid_by_email' => auth()->user()?->email,
            'account_owner_id' => $owner?->id,
            'account_owner_email' => $owner?->email,
            'message' => 'Platform payout recorded for ' . ($tenant->company_name ?: ('Tenant #' . $tenant->id)) . '.',
        ], fn ($value) => $value !== null && $value !== ''));
    }
}
