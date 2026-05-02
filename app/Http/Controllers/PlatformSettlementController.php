<?php

namespace App\Http\Controllers;

use App\Models\PlatformPayout;
use App\Models\Tenant;
use App\Models\TenantPayoutAccount;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class PlatformSettlementController extends Controller
{
    public function index(Request $request)
    {
        $status = trim((string) $request->query('status', 'unpaid'));
        $allowed = ['unpaid', 'paid', 'all'];
        $status = in_array($status, $allowed, true) ? $status : 'unpaid';

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
        $unpaidLiabilityTotal = (float) Payment::query()
            ->where('payment_type', Payment::TYPE_FUNNEL_CHECKOUT)
            ->where('status', 'paid')
            ->whereNull('platform_payout_id')
            ->sum('amount');

        $payoutsThisMonthTotal = (float) PlatformPayout::query()
            ->where('status', 'paid')
            ->where('paid_at', '>=', $monthStart)
            ->sum('amount');
        $collectedThisMonthTotal = (float) Payment::query()
            ->where('status', 'paid')
            ->where('created_at', '>=', $monthStart)
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

        return view('platform.settlements', [
            'tenants' => $tenants,
            'unpaidTotals' => $unpaidTotals,
            'recentPayouts' => $recentPayouts,
            'statusFilter' => $status,
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

        $payout = PlatformPayout::create([
            'tenant_id' => $tenant->id,
            'amount' => $amount,
            'destination_type' => $payoutAccount->destination_type,
            'masked_destination' => $payoutAccount->masked_destination,
            'payment_reference' => trim((string) ($validated['payment_reference'] ?? '')) ?: null,
            'status' => 'paid',
            'paid_at' => now(),
            'paid_by' => auth()->id(),
            'notes' => trim((string) ($validated['notes'] ?? '')) ?: null,
        ]);

        // Mark oldest payments as paid out until we reach amount.
        $remaining = $amount;
        foreach ($unpaidPayments as $payment) {
            if ($remaining <= 0) {
                break;
            }

            $paymentAmount = (float) $payment->amount;
            $payment->update(['platform_payout_id' => $payout->id]);
            $remaining -= $paymentAmount;
        }

        return redirect()
            ->route('platform.settlements.index')
            ->with('success', 'Payout recorded successfully.');
    }
}
