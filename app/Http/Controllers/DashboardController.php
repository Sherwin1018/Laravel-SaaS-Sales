<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\Payment;
use App\Services\AnalyticsDashboardService;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    protected function monthKeyExpression(string $column): string
    {
        return DB::getDriverName() === 'pgsql'
            ? "TO_CHAR({$column}, 'YYYY-MM')"
            : "DATE_FORMAT({$column}, '%Y-%m')";
    }

    public function owner(AnalyticsDashboardService $analytics)
    {
        $tenant = auth()->user()->tenant;
        $tenantId = auth()->user()->tenant_id;

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
            ->groupBy('status')
            ->pluck('total', 'status');

        $revenueTotal = (float) ($paymentStatusTotals['paid'] ?? 0);

        $teamActivity = LeadActivity::with(['lead:id,name'])
            ->whereHas('lead', function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId);
            })
            ->latest()
            ->paginate(10, ['*'], 'activity_page');

        $trialDaysRemaining = $tenant?->trialDaysRemaining() ?? 0;
        $trialEndsAt = $tenant?->trial_ends_at;
        $trialActive = $tenant?->isOnTrial() && ! $tenant?->isTrialExpired();
        $analyticsSummary = $tenant ? $analytics->tenantOwnerSummary($tenant) : [
            'usage' => [],
            'revenue_trend_labels' => [],
            'revenue_trend_values' => [],
        ];

        return view('dashboard.account-owner', compact(
            'tenant',
            'leadsThisMonth',
            'wonCount',
            'lostCount',
            'conversionRate',
            'pipelineDistribution',
            'pipelineAging',
            'revenueTotal',
            'paymentStatusTotals',
            'teamActivity',
            'trialDaysRemaining',
            'trialEndsAt',
            'trialActive',
            'analyticsSummary'
        ));
    }

    public function marketing()
    {
        $tenantId = auth()->user()->tenant_id;

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
            'mqlThreshold'
        ));
    }

    public function sales()
    {
        $user = auth()->user();
        $assignedLeadsQuery = Lead::where('tenant_id', $user->tenant_id)->where('assigned_to', $user->id);

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
            'myRecentLeads'
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

        return view('dashboard.finance', compact(
            'statusAmounts',
            'statusCounts',
            'outstandingAmount',
            'outstandingCount',
            'trendLabels',
            'trendValues',
            'pendingInvoices'
        ));
    }

    public function customer()
    {
        $user = auth()->user();
        $tenant = $user->tenant;

        $subscriptionStatus = $tenant ? ucfirst($tenant->status) : 'N/A';
        $subscriptionPlan = $tenant->subscription_plan ?? 'N/A';

        $recentPayments = Payment::where('tenant_id', $user->tenant_id)
            ->latest('payment_date')
            ->paginate(10, ['id', 'amount', 'status', 'payment_date'], 'payments_page');

        return view('dashboard.customer', compact(
            'subscriptionStatus',
            'subscriptionPlan',
            'recentPayments'
        ));
    }
}
