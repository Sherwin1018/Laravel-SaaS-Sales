<?php

namespace App\Services;

use App\Models\CommissionEntry;
use App\Models\CommissionPlan;
use App\Models\Funnel;
use App\Models\Payment;
use App\Models\PaymentReceipt;
use App\Models\Tenant;
use App\Models\User;
use App\Support\XlsxWorkbookBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OwnerReportService
{
    public function __construct(
        private CommissionService $commissions,
    ) {
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function build(Tenant $tenant, array $filters = []): array
    {
        $this->commissions->syncTenant($tenant);
        $plan = $this->commissions->resolvePlanForTenant($tenant);

        $paymentsQuery = $this->basePaymentQuery($tenant, $filters);
        $paidPaymentsQuery = (clone $paymentsQuery)->where('payments.status', 'paid');

        $paidPayments = (clone $paidPaymentsQuery)->get([
            'payments.id',
            'payments.amount',
            'payments.payment_date',
            'payments.payment_type',
        ]);

        $grossPaidRevenue = round((float) $paidPayments->sum('amount'), 2);
        $gatewayFees = round($paidPayments->sum(fn (Payment $payment) => $this->commissions->estimateGatewayFee((float) $payment->amount, $plan)), 2);
        $platformFees = round($paidPayments->sum(fn (Payment $payment) => $this->commissions->estimatePlatformFee((float) $payment->amount, $plan)), 2);
        $netEligibleRevenue = round(max(0, $grossPaidRevenue - $gatewayFees - $platformFees), 2);

        $commissionEntries = $this->baseCommissionQuery($tenant, $filters)->get([
            'commission_entries.id',
            'commission_entries.commission_type',
            'commission_entries.commission_role',
            'commission_entries.commission_amount',
            'commission_entries.status',
            'commission_entries.user_id',
            'commission_entries.payment_id',
        ]);
        $activeCommissionEntries = $commissionEntries->reject(fn (CommissionEntry $entry) => in_array($entry->status, [
            CommissionEntry::STATUS_REVERSED,
            CommissionEntry::STATUS_CANCELLED,
        ], true));

        $salesCommissionTotal = round((float) $activeCommissionEntries->where('commission_type', 'sales_agent')->sum('commission_amount'), 2);
        $marketingCommissionTotal = round((float) $activeCommissionEntries->where('commission_type', 'marketing_manager')->sum('commission_amount'), 2);
        $ownerResidualTotal = round(max(0, $netEligibleRevenue - $salesCommissionTotal - $marketingCommissionTotal), 2);

        $receiptsQuery = $this->baseReceiptQuery($tenant, $filters);
        $pendingReceiptCount = (clone $receiptsQuery)
            ->whereIn('payment_receipts.status', [PaymentReceipt::STATUS_PENDING, PaymentReceipt::STATUS_REJECTED])
            ->count();
        $autoApprovedReceiptCount = (clone $receiptsQuery)
            ->where('payment_receipts.status', PaymentReceipt::STATUS_AUTO_APPROVED)
            ->count();

        $trend = $this->monthlyRevenueTrend($tenant, $filters);
        $topFunnels = $this->topFunnels($tenant, $filters);
        $topCampaigns = $this->topCampaigns($tenant, $filters);
        $recentReceipts = (clone $receiptsQuery)
            ->with(['payment:id,amount,payment_date,status,payment_type', 'uploader:id,name', 'reviewer:id,name'])
            ->latest('payment_receipts.id')
            ->limit(8)
            ->get();
        $recentCommissions = $this->baseCommissionQuery($tenant, $filters)
            ->with(['user:id,name', 'payment:id,amount,payment_date'])
            ->latest('commission_entries.id')
            ->limit(10)
            ->get();
        $recentPayments = (clone $paymentsQuery)
            ->with(['funnel:id,name', 'lead:id,name,source_campaign'])
            ->latest('payments.payment_date')
            ->latest('payments.id')
            ->limit(10)
            ->get();

        return [
            'filters' => $this->normalizedFilters($filters),
            'plan' => $plan,
            'payout_account' => $tenant->defaultPayoutAccount,
            'subscription' => [
                'status' => $tenant->status,
                'billing_status' => $tenant->billing_status,
                'trial_ends_at' => $tenant->trial_ends_at,
                'billing_grace_ends_at' => $tenant->billing_grace_ends_at,
                'subscription_activated_at' => $tenant->subscription_activated_at,
                'trial_days_remaining' => $tenant->trialDaysRemaining(),
                'grace_days_remaining' => $tenant->billingGraceDaysRemaining(),
            ],
            'totals' => [
                'gross_paid_revenue' => $grossPaidRevenue,
                'gateway_fees' => $gatewayFees,
                'platform_fees' => $platformFees,
                'net_eligible_revenue' => $netEligibleRevenue,
                'sales_commission_total' => $salesCommissionTotal,
                'marketing_commission_total' => $marketingCommissionTotal,
                'owner_residual_total' => $ownerResidualTotal,
                'payable_commissions_total' => round((float) $activeCommissionEntries->where('status', CommissionEntry::STATUS_PAYABLE)->sum('commission_amount'), 2),
                'held_commissions_total' => round((float) $activeCommissionEntries->where('status', CommissionEntry::STATUS_HELD)->sum('commission_amount'), 2),
                'paid_commissions_total' => round((float) $activeCommissionEntries->where('status', CommissionEntry::STATUS_PAID)->sum('commission_amount'), 2),
                'pending_receipt_count' => $pendingReceiptCount,
                'auto_approved_receipt_count' => $autoApprovedReceiptCount,
            ],
            'trend' => $trend,
            'top_funnels' => $topFunnels,
            'top_campaigns' => $topCampaigns,
            'recent_receipts' => $recentReceipts,
            'recent_commissions' => $recentCommissions,
            'recent_payments' => $recentPayments,
            'status_breakdown' => [
                'payments_paid' => (clone $paymentsQuery)->where('payments.status', 'paid')->count(),
                'payments_pending' => (clone $paymentsQuery)->where('payments.status', 'pending')->count(),
                'payments_failed' => (clone $paymentsQuery)->where('payments.status', 'failed')->count(),
                'receipts_pending' => $pendingReceiptCount,
                'commissions_payable' => $activeCommissionEntries->where('status', CommissionEntry::STATUS_PAYABLE)->count(),
                'commissions_held' => $activeCommissionEntries->where('status', CommissionEntry::STATUS_HELD)->count(),
            ],
            'funnel_options' => Funnel::query()
                ->where('tenant_id', $tenant->id)
                ->orderBy('name')
                ->get(['id', 'name']),
            'marketing_manager_options' => User::query()
                ->where('tenant_id', $tenant->id)
                ->whereHas('roles', fn (Builder $query) => $query->where('slug', 'marketing-manager'))
                ->orderBy('name')
                ->get(['id', 'name']),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function export(Tenant $tenant, array $filters = []): StreamedResponse
    {
        $summary = $this->build($tenant, $filters);
        $rows = [];

        $payments = $this->basePaymentQuery($tenant, $filters)
            ->with(['funnel:id,name', 'lead:id,name,source_campaign'])
            ->latest('payments.payment_date')
            ->latest('payments.id')
            ->get();

        foreach ($payments as $payment) {
            $basisAmount = $this->commissions->calculateNetEligibleAmount($payment, $summary['plan']);
            $salesCommission = (float) $payment->commissionEntries()
                ->whereNotIn('status', [CommissionEntry::STATUS_REVERSED, CommissionEntry::STATUS_CANCELLED])
                ->where('commission_type', 'sales_agent')
                ->sum('commission_amount');
            $marketingCommission = (float) $payment->commissionEntries()
                ->whereNotIn('status', [CommissionEntry::STATUS_REVERSED, CommissionEntry::STATUS_CANCELLED])
                ->where('commission_type', 'marketing_manager')
                ->sum('commission_amount');

            $rows[] = [
                ['type' => 'String', 'value' => optional($payment->payment_date)->format('Y-m-d') ?? '-'],
                ['type' => 'String', 'value' => ucfirst(str_replace('_', ' ', $payment->payment_type))],
                ['type' => 'String', 'value' => $payment->funnel->name ?? '-'],
                ['type' => 'String', 'value' => $payment->lead->name ?? '-'],
                ['type' => 'String', 'value' => $payment->lead->source_campaign ?? '-'],
                ['type' => 'Number', 'style' => 'currency', 'value' => (float) $payment->amount],
                ['type' => 'Number', 'style' => 'currency', 'value' => $basisAmount],
                ['type' => 'Number', 'style' => 'currency', 'value' => $salesCommission],
                ['type' => 'Number', 'style' => 'currency', 'value' => $marketingCommission],
                ['type' => 'Number', 'style' => 'currency', 'value' => max(0, $basisAmount - $salesCommission - $marketingCommission)],
                ['type' => 'String', 'value' => ucfirst($payment->status)],
            ];
        }

        $workbook = (new XlsxWorkbookBuilder(
            'Owner Reports',
            'Owner Reports Export',
            'Gross paid: PHP ' . number_format((float) data_get($summary, 'totals.gross_paid_revenue', 0), 2)
                . ' | Net eligible: PHP ' . number_format((float) data_get($summary, 'totals.net_eligible_revenue', 0), 2)
                . ' | Owner residual: PHP ' . number_format((float) data_get($summary, 'totals.owner_residual_total', 0), 2),
            ['Payment Date', 'Payment Type', 'Funnel', 'Lead', 'Campaign', 'Gross Paid', 'Net Eligible', 'Sales Commission', 'Marketing Commission', 'Owner Residual', 'Status'],
            [18, 18, 22, 22, 20, 16, 16, 18, 18, 18, 12],
            $rows
        ))->build();

        $fileName = 'owner-reports-' . now()->format('Ymd-His') . '.xlsx';

        return response()->streamDownload(function () use ($workbook) {
            echo $workbook;
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function basePaymentQuery(Tenant $tenant, array $filters): Builder
    {
        return Payment::query()
            ->where('payments.tenant_id', $tenant->id)
            ->where('payments.payment_type', Payment::TYPE_FUNNEL_CHECKOUT)
            ->when(! empty($filters['date_from']), fn (Builder $query) => $query->whereDate('payments.payment_date', '>=', $filters['date_from']))
            ->when(! empty($filters['date_to']), fn (Builder $query) => $query->whereDate('payments.payment_date', '<=', $filters['date_to']))
            ->when(! empty($filters['funnel_id']), fn (Builder $query) => $query->where('payments.funnel_id', (int) $filters['funnel_id']));
    }

    private function baseCommissionQuery(Tenant $tenant, array $filters): Builder
    {
        return CommissionEntry::query()
            ->where('commission_entries.tenant_id', $tenant->id)
            ->whereHas('payment', function (Builder $query) use ($filters) {
                $query->where('payment_type', Payment::TYPE_FUNNEL_CHECKOUT)
                    ->when(! empty($filters['date_from']), fn (Builder $subQuery) => $subQuery->whereDate('payment_date', '>=', $filters['date_from']))
                    ->when(! empty($filters['date_to']), fn (Builder $subQuery) => $subQuery->whereDate('payment_date', '<=', $filters['date_to']))
                    ->when(! empty($filters['funnel_id']), fn (Builder $subQuery) => $subQuery->where('funnel_id', (int) $filters['funnel_id']));
            });
    }

    private function baseReceiptQuery(Tenant $tenant, array $filters): Builder
    {
        return PaymentReceipt::query()
            ->where('payment_receipts.tenant_id', $tenant->id)
            ->whereHas('payment', function (Builder $query) use ($filters) {
                $query->where('payment_type', Payment::TYPE_FUNNEL_CHECKOUT)
                    ->when(! empty($filters['date_from']), fn (Builder $subQuery) => $subQuery->whereDate('payment_date', '>=', $filters['date_from']))
                    ->when(! empty($filters['date_to']), fn (Builder $subQuery) => $subQuery->whereDate('payment_date', '<=', $filters['date_to']))
                    ->when(! empty($filters['funnel_id']), fn (Builder $subQuery) => $subQuery->where('funnel_id', (int) $filters['funnel_id']));
            });
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    private function monthlyRevenueTrend(Tenant $tenant, array $filters): array
    {
        $monthKeyExpression = DB::getDriverName() === 'pgsql'
            ? "TO_CHAR(payment_date, 'YYYY-MM')"
            : "DATE_FORMAT(payment_date, '%Y-%m')";

        $raw = $this->basePaymentQuery($tenant, $filters)
            ->where('payments.status', 'paid')
            ->where('payments.payment_date', '>=', now()->copy()->subMonths(5)->startOfMonth())
            ->selectRaw($monthKeyExpression . ' as month_key, SUM(payments.amount) as total')
            ->groupBy('month_key')
            ->pluck('total', 'month_key');

        $labels = [];
        $values = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->copy()->subMonths($i);
            $key = $month->format('Y-m');
            $labels[] = $month->format('M Y');
            $values[] = round((float) ($raw[$key] ?? 0), 2);
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return \Illuminate\Support\Collection<int, object>
     */
    private function topFunnels(Tenant $tenant, array $filters)
    {
        return $this->basePaymentQuery($tenant, $filters)
            ->leftJoin('funnels', 'funnels.id', '=', 'payments.funnel_id')
            ->where('payments.status', 'paid')
            ->groupBy('payments.funnel_id', 'funnels.name')
            ->selectRaw('funnels.name as funnel_name, COUNT(payments.id) as paid_orders, SUM(payments.amount) as paid_revenue')
            ->orderByDesc('paid_revenue')
            ->limit(5)
            ->get();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return \Illuminate\Support\Collection<int, object>
     */
    private function topCampaigns(Tenant $tenant, array $filters)
    {
        return $this->basePaymentQuery($tenant, $filters)
            ->leftJoin('leads', 'leads.id', '=', 'payments.lead_id')
            ->where('payments.status', 'paid')
            ->whereNotNull('leads.source_campaign')
            ->where('leads.source_campaign', '!=', '')
            ->groupBy('leads.source_campaign')
            ->selectRaw('leads.source_campaign as source_campaign, COUNT(payments.id) as paid_orders, SUM(payments.amount) as paid_revenue')
            ->orderByDesc('paid_revenue')
            ->limit(5)
            ->get();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    private function normalizedFilters(array $filters): array
    {
        return [
            'date_from' => ! empty($filters['date_from']) ? (string) $filters['date_from'] : now()->copy()->subDays(29)->toDateString(),
            'date_to' => ! empty($filters['date_to']) ? (string) $filters['date_to'] : now()->toDateString(),
            'funnel_id' => ! empty($filters['funnel_id']) ? (int) $filters['funnel_id'] : null,
        ];
    }
}
