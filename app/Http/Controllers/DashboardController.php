<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\Funnel;
use App\Models\Payment;
use App\Models\PaymentReceipt;
use App\Models\CommissionEntry;
use App\Services\CouponService;
use App\Services\AnalyticsDashboardService;
use App\Services\CommissionService;
use App\Services\FunnelTrackingService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    protected function monthKeyExpression(string $column): string
    {
        return DB::getDriverName() === 'pgsql'
            ? "TO_CHAR({$column}, 'YYYY-MM')"
            : "DATE_FORMAT({$column}, '%Y-%m')";
    }

    public function owner(AnalyticsDashboardService $analytics, CouponService $coupons)
    {
        $tenant = auth()->user()->tenant;
        $tenant?->loadMissing('defaultPayoutAccount');
        $tenantId = auth()->user()->tenant_id;
        $payoutAccount = $tenant?->defaultPayoutAccount;
        $requiresPayoutSetup = ! ($payoutAccount && $payoutAccount->hasDestinationDetails());

        $leadsThisMonth = Lead::where('tenant_id', $tenantId)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();

        $wonStatuses = Lead::wonStatusValues();
        $lostStatuses = Lead::lostStatusValues();

        $wonCount = Lead::where('tenant_id', $tenantId)->whereIn('status', $wonStatuses)->count();
        $lostCount = Lead::where('tenant_id', $tenantId)->whereIn('status', $lostStatuses)->count();
        $closedCount = $wonCount + $lostCount;
        $conversionRate = $closedCount > 0 ? round(($wonCount / $closedCount) * 100, 1) : 0;

        $pipelineDistribution = Lead::select('status', DB::raw('COUNT(*) as total'))
            ->where('tenant_id', $tenantId)
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        $openLeadQuery = Lead::where('tenant_id', $tenantId)
            ->whereNotIn('status', array_merge($wonStatuses, $lostStatuses));

        $pipelineAging = [
            '0-7 days' => (clone $openLeadQuery)->where('created_at', '>=', now()->copy()->subDays(7))->count(),
            '8-14 days' => (clone $openLeadQuery)
                ->whereBetween('created_at', [now()->copy()->subDays(14), now()->copy()->subDays(8)])
                ->count(),
            '15+ days' => (clone $openLeadQuery)->where('created_at', '<', now()->copy()->subDays(14))->count(),
        ];

        $paymentStatusTotals = Payment::select('status', DB::raw('SUM(amount) as total'))
            ->where('tenant_id', $tenantId)
            ->where('payment_type', Payment::TYPE_FUNNEL_CHECKOUT)
            ->groupBy('status')
            ->pluck('total', 'status');

        $revenueTotal = (float) ($paymentStatusTotals['paid'] ?? 0);
        $salesByPurpose = Payment::query()
            ->select(
                DB::raw("COALESCE(funnels.purpose, 'service') as purpose"),
                DB::raw('SUM(payments.amount) as total')
            )
            ->leftJoin('funnels', 'funnels.id', '=', 'payments.funnel_id')
            ->where('payments.tenant_id', $tenantId)
            ->where('payments.payment_type', Payment::TYPE_FUNNEL_CHECKOUT)
            ->where('payments.status', 'paid')
            ->groupBy('purpose')
            ->pluck('total', 'purpose');

        $physicalProductSalesTotal = (float) (
            ($salesByPurpose['physical_product'] ?? 0)
            + ($salesByPurpose['hybrid'] ?? 0)
        );
        $serviceSalesTotal = (float) (
            ($salesByPurpose['service'] ?? 0)
            + ($salesByPurpose['digital_product'] ?? 0)
        );

        $teamActivity = LeadActivity::with(['lead:id,name'])
            ->whereHas('lead', function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId);
            })
            ->latest()
            ->paginate(10, ['*'], 'activity_page');

        $trialDaysRemaining = $tenant?->trialDaysRemaining() ?? 0;
        $trialEndsAt = $tenant?->trial_ends_at;
        $trialActive = $tenant?->isOnTrial() && ! $tenant?->isTrialExpired();
        $visibleCoupons = $coupons->visibleToTenant((int) $tenantId)
            ->get()
            ->map(fn ($coupon) => $coupons->syncCouponStatus($coupon));
        $activeCouponCount = $visibleCoupons->where('status', 'active')->count();
        $platformCouponCount = $visibleCoupons->where('scope_type', 'platform')->count();
        $analyticsSummary = $tenant ? $analytics->tenantOwnerSummary($tenant) : [
            'usage' => [],
            'revenue_trend_labels' => [],
            'revenue_trend_values' => [],
        ];

        return view('dashboard.account-owner', compact(
            'tenant',
            'payoutAccount',
            'requiresPayoutSetup',
            'leadsThisMonth',
            'wonCount',
            'lostCount',
            'conversionRate',
            'pipelineDistribution',
            'pipelineAging',
            'revenueTotal',
            'serviceSalesTotal',
            'physicalProductSalesTotal',
            'paymentStatusTotals',
            'teamActivity',
            'trialDaysRemaining',
            'trialEndsAt',
            'trialActive',
            'activeCouponCount',
            'platformCouponCount',
            'analyticsSummary'
        ));
    }

    public function marketing(CommissionService $commissions)
    {
        $user = auth()->user();
        $tenantId = $user->tenant_id;
        $commissionSummary = $commissions->summaryForUser($user);
        $attributedRevenue = (float) CommissionEntry::query()
            ->where('tenant_id', $tenantId)
            ->where('user_id', $user->id)
            ->where('commission_type', 'marketing_manager')
            ->sum('basis_amount');

        $sourceBreakdownChart = Lead::selectRaw("COALESCE(NULLIF(source_campaign, ''), 'Unspecified') as source_label, COUNT(*) as total")
            ->where('tenant_id', $tenantId)
            ->groupBy('source_label')
            ->orderByDesc('total')
            ->get();

        $sourceBreakdown = Lead::selectRaw("COALESCE(NULLIF(source_campaign, ''), 'Unspecified') as source_label, COUNT(*) as total")
            ->where('tenant_id', $tenantId)
            ->groupBy('source_label')
            ->orderByDesc('total')
            ->paginate(10, ['*'], 'source_page');

        $mqlThreshold = 20;
        $mqlCount = Lead::where('tenant_id', $tenantId)->where('score', '>=', $mqlThreshold)->count();
        $avgLeadScore = round((float) Lead::where('tenant_id', $tenantId)->avg('score'), 1);

        $mqlTrendRaw = Lead::selectRaw($this->monthKeyExpression('created_at') . " as month_key, COUNT(*) as total")
            ->where('tenant_id', $tenantId)
            ->where('score', '>=', $mqlThreshold)
            ->where('created_at', '>=', now()->copy()->subMonths(5)->startOfMonth())
            ->groupBy('month_key')
            ->pluck('total', 'month_key');

        $trendLabels = [];
        $trendValues = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->copy()->subMonths($i);
            $key = $month->format('Y-m');
            $trendLabels[] = $month->format('M Y');
            $trendValues[] = (int) ($mqlTrendRaw[$key] ?? 0);
        }

        return view('dashboard.marketing', compact(
            'sourceBreakdown',
            'sourceBreakdownChart',
            'mqlCount',
            'avgLeadScore',
            'trendLabels',
            'trendValues',
            'mqlThreshold',
            'commissionSummary',
            'attributedRevenue'
        ));
    }

    public function sales(CommissionService $commissions)
    {
        $user = auth()->user();
        $assignedLeadsQuery = Lead::where('tenant_id', $user->tenant_id)->where('assigned_to', $user->id);
        $commissionSummary = $commissions->summaryForUser($user);

        $myAssignedLeadsCount = (clone $assignedLeadsQuery)->count();
        $pipelineStageCounts = (clone $assignedLeadsQuery)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        $overdueLeads = (clone $assignedLeadsQuery)
            ->whereNotIn('status', Lead::closedStatusValues())
            ->where('updated_at', '<', now()->copy()->subDays(3))
            ->latest('updated_at')
            ->paginate(10, ['id', 'name', 'status', 'updated_at'], 'overdue_page');

        $overdueFollowUpsCount = (clone $assignedLeadsQuery)
            ->whereNotIn('status', Lead::closedStatusValues())
            ->where('updated_at', '<', now()->copy()->subDays(3))
            ->count();

        $todayTaskCount = LeadActivity::whereHas('lead', function ($query) use ($user) {
            $query->where('tenant_id', $user->tenant_id)->where('assigned_to', $user->id);
        })->whereDate('created_at', now()->toDateString())->count();

        $myRecentLeads = (clone $assignedLeadsQuery)
            ->latest()
            ->paginate(10, ['id', 'name', 'status', 'updated_at'], 'recent_page');

        return view('dashboard.sales', compact(
            'myAssignedLeadsCount',
            'pipelineStageCounts',
            'overdueFollowUpsCount',
            'todayTaskCount',
            'overdueLeads',
            'myRecentLeads',
            'commissionSummary'
        ));
    }

    public function finance()
    {
        $tenantId = auth()->user()->tenant_id;

        $statusAmounts = Payment::select('status', DB::raw('SUM(amount) as total'))
            ->where('tenant_id', $tenantId)
            ->groupBy('status')
            ->pluck('total', 'status');

        $statusCounts = Payment::select('status', DB::raw('COUNT(*) as total'))
            ->where('tenant_id', $tenantId)
            ->groupBy('status')
            ->pluck('total', 'status');

        $outstandingAmount = (float) ($statusAmounts['pending'] ?? 0);
        $outstandingCount = (int) ($statusCounts['pending'] ?? 0);

        $collectionTrendRaw = Payment::selectRaw($this->monthKeyExpression('payment_date') . " as month_key, SUM(amount) as total")
            ->where('tenant_id', $tenantId)
            ->where('status', 'paid')
            ->where('payment_date', '>=', now()->copy()->subMonths(5)->startOfMonth())
            ->groupBy('month_key')
            ->pluck('total', 'month_key');

        $trendLabels = [];
        $trendValues = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->copy()->subMonths($i);
            $key = $month->format('Y-m');
            $trendLabels[] = $month->format('M Y');
            $trendValues[] = (float) ($collectionTrendRaw[$key] ?? 0);
        }

        $pendingInvoices = Payment::with('lead:id,name')
            ->where('tenant_id', $tenantId)
            ->where('status', 'pending')
            ->orderBy('payment_date')
            ->paginate(10, ['id', 'lead_id', 'amount', 'payment_date'], 'pending_page');
        $receiptReviewCount = PaymentReceipt::query()
            ->where('tenant_id', $tenantId)
            ->where('status', PaymentReceipt::STATUS_PENDING)
            ->count();
        $payableCommissionTotal = (float) CommissionEntry::query()
            ->where('tenant_id', $tenantId)
            ->where('status', CommissionEntry::STATUS_PAYABLE)
            ->sum('commission_amount');

        return view('dashboard.finance', compact(
            'statusAmounts',
            'statusCounts',
            'outstandingAmount',
            'outstandingCount',
            'trendLabels',
            'trendValues',
            'pendingInvoices',
            'receiptReviewCount',
            'payableCommissionTotal'
        ));
    }

    protected function customerPortalData(FunnelTrackingService $tracking): array
    {
        $user = auth()->user();
        $funnelSearch = trim((string) request('funnel'));

        $normalizedEmail = mb_strtolower(trim((string) $user->email));
        $orderRows = Funnel::query()
            ->where('tenant_id', $user->tenant_id)
            ->get(['id', 'tenant_id', 'name', 'slug', 'purpose'])
            ->flatMap(function (Funnel $funnel) use ($tracking, $normalizedEmail): Collection {
                $analytics = $tracking->analyticsForFunnel($funnel);

                return collect($analytics['offer_customer_summary'] ?? [])
                    ->filter(function (array $row) use ($normalizedEmail): bool {
                        $rowEmail = mb_strtolower(trim((string) ($row['email'] ?? '')));

                        return $rowEmail !== '' && $rowEmail === $normalizedEmail && ($row['order_status'] ?? '') === 'paid';
                    })
                    ->map(function (array $row) use ($funnel): array {
                        $row['funnel_name'] = $row['funnel_name'] ?? $funnel->name;
                        $row['funnel_slug'] = $row['funnel_slug'] ?? $funnel->slug;

                        return $row;
                    });
            })
            ->sortByDesc(fn (array $row) => (string) ($row['ordered_at'] ?? $row['last_activity_at'] ?? ''))
            ->values();

        if ($funnelSearch !== '') {
            $needle = mb_strtolower($funnelSearch);
            $orderRows = $orderRows
                ->filter(function (array $row) use ($needle): bool {
                    $funnelName = mb_strtolower(trim((string) ($row['funnel_name'] ?? '')));
                    $funnelSlug = mb_strtolower(trim((string) ($row['funnel_slug'] ?? '')));

                    return str_contains($funnelName, $needle) || str_contains($funnelSlug, $needle);
                })
                ->values();
        }

        $pageName = 'orders_page';
        $page = LengthAwarePaginator::resolveCurrentPage($pageName);
        $perPage = 10;
        $orders = new LengthAwarePaginator(
            $orderRows->forPage($page, $perPage)->values(),
            $orderRows->count(),
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'pageName' => $pageName,
            ]
        );
        $orders->appends(request()->except($pageName));

        $orderSummary = [
            'total_orders' => $orderRows->count(),
            'total_spent' => round((float) $orderRows->sum(fn (array $row) => (float) ($row['checkout_amount'] ?? 0)), 2),
            'active_shipments' => $orderRows->filter(function (array $row): bool {
                return in_array((string) ($row['delivery_status'] ?? ''), ['processing', 'shipped', 'out_for_delivery'], true);
            })->count(),
            'last_ordered_at' => $orderRows->first()['ordered_at_label'] ?? null,
        ];

        return compact(
            'orders',
            'orderSummary',
            'funnelSearch'
        );
    }

    public function customer(FunnelTrackingService $tracking)
    {
        return view('dashboard.customer-overview', $this->customerPortalData($tracking));
    }

    public function customerOrders(FunnelTrackingService $tracking)
    {
        return view('dashboard.customer', $this->customerPortalData($tracking));
    }
}
