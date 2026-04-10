<?php

namespace App\Services;

use App\Models\Funnel;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\Tenant;
use App\Models\User;
use App\Support\TenantPlanEnforcer;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AnalyticsDashboardService
{
    public function platformSummary(): array
    {
        $tenantCount = Tenant::count();
        $activeTenantCount = Tenant::where('status', 'active')->count();
        $trialTenantCount = Tenant::where('status', 'trial')->count();
        $inactiveTenantCount = Tenant::where('status', 'inactive')->count();

        $currentMonthPaid = Payment::query()
            ->where('status', 'paid')
            ->whereYear('payment_date', now()->year)
            ->whereMonth('payment_date', now()->month);

        $previousMonthPaid = Payment::query()
            ->where('status', 'paid')
            ->whereYear('payment_date', now()->copy()->subMonth()->year)
            ->whereMonth('payment_date', now()->copy()->subMonth()->month);

        $mrr = (float) $currentMonthPaid->sum('amount');
        $previousMonthMrr = (float) $previousMonthPaid->sum('amount');

        $previousActiveTenants = Tenant::query()
            ->whereDate('created_at', '<=', now()->copy()->subMonth()->endOfMonth())
            ->count();

        $cancelledThisMonth = Tenant::query()
            ->where('status', 'inactive')
            ->whereYear('updated_at', now()->year)
            ->whereMonth('updated_at', now()->month)
            ->count();

        $churnRate = $previousActiveTenants > 0
            ? round(($cancelledThisMonth / $previousActiveTenants) * 100, 2)
            : 0.0;

        $payingTenants = Payment::query()
            ->where('status', 'paid')
            ->whereYear('payment_date', now()->year)
            ->whereMonth('payment_date', now()->month)
            ->distinct('tenant_id')
            ->count('tenant_id');

        $arpu = $payingTenants > 0 ? round($mrr / $payingTenants, 2) : 0.0;

        return [
            'tenant_count' => $tenantCount,
            'active_tenants' => $activeTenantCount,
            'trial_tenants' => $trialTenantCount,
            'inactive_tenants' => $inactiveTenantCount,
            'mrr' => $mrr,
            'previous_month_mrr' => $previousMonthMrr,
            'mrr_growth_rate' => $previousMonthMrr > 0 ? round((($mrr - $previousMonthMrr) / $previousMonthMrr) * 100, 2) : ($mrr > 0 ? 100.0 : 0.0),
            'churn_rate' => $churnRate,
            'paying_tenants' => $payingTenants,
            'arpu' => $arpu,
            'usage_metrics' => $this->platformUsageMetrics(),
            'tenant_growth' => $this->tenantGrowthSeries(),
        ];
    }

    public function tenantOwnerSummary(Tenant $tenant): array
    {
        $usage = app(TenantPlanEnforcer::class)->usageSummary($tenant);
        $monthKeyExpression = DB::getDriverName() === 'pgsql'
            ? "TO_CHAR(payment_date, 'YYYY-MM')"
            : "DATE_FORMAT(payment_date, '%Y-%m')";

        $revenueTrendRaw = Payment::query()
            ->where('tenant_id', $tenant->id)
            ->where('status', 'paid')
            ->where('payment_date', '>=', now()->copy()->subMonths(5)->startOfMonth())
            ->selectRaw($monthKeyExpression . " as month_key, SUM(amount) as total")
            ->groupBy('month_key')
            ->pluck('total', 'month_key');

        $labels = [];
        $values = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->copy()->subMonths($i);
            $key = $month->format('Y-m');
            $labels[] = $month->format('M Y');
            $values[] = round((float) ($revenueTrendRaw[$key] ?? 0), 2);
        }

        return [
            'usage' => $usage,
            'revenue_trend_labels' => $labels,
            'revenue_trend_values' => $values,
            'physical_sales' => $this->tenantPhysicalSalesSummary($tenant),
        ];
    }

    private function platformUsageMetrics(): array
    {
        return [
            'users' => User::count(),
            'leads' => Lead::withoutGlobalScope('tenant')->count(),
            'funnels' => DB::table('funnels')->count(),
            'payments' => Payment::count(),
        ];
    }

    private function tenantGrowthSeries(): array
    {
        $monthKeyExpression = DB::getDriverName() === 'pgsql'
            ? "TO_CHAR(created_at, 'YYYY-MM')"
            : "DATE_FORMAT(created_at, '%Y-%m')";

        $raw = Tenant::query()
            ->selectRaw($monthKeyExpression . " as month_key, COUNT(*) as total")
            ->where('created_at', '>=', now()->copy()->subMonths(5)->startOfMonth())
            ->groupBy('month_key')
            ->pluck('total', 'month_key');

        $labels = [];
        $values = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->copy()->subMonths($i);
            $key = $month->format('Y-m');
            $labels[] = $month->format('M Y');
            $values[] = (int) ($raw[$key] ?? 0);
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }

    private function tenantPhysicalSalesSummary(Tenant $tenant): array
    {
        $funnels = Funnel::query()
            ->where('tenant_id', $tenant->id)
            ->whereIn('purpose', ['physical_product', 'hybrid'])
            ->get(['id', 'name', 'slug', 'purpose']);

        if ($funnels->isEmpty()) {
            return [
                'funnel_count' => 0,
                'total_orders' => 0,
                'paid_orders' => 0,
                'pending_orders' => 0,
                'abandoned_orders' => 0,
                'units_ordered' => 0,
                'paid_revenue' => 0.0,
                'pending_revenue' => 0.0,
                'average_paid_order_value' => 0.0,
                'top_products' => [],
                'top_funnels' => [],
                'delivery_statuses' => [],
            ];
        }

        /** @var FunnelTrackingService $tracking */
        $tracking = app(FunnelTrackingService::class);

        $summary = [
            'funnel_count' => $funnels->count(),
            'total_orders' => 0,
            'paid_orders' => 0,
            'pending_orders' => 0,
            'abandoned_orders' => 0,
            'units_ordered' => 0,
            'paid_revenue' => 0.0,
            'pending_revenue' => 0.0,
            'average_paid_order_value' => 0.0,
            'top_products' => [],
            'top_funnels' => [],
            'delivery_statuses' => [],
        ];

        $products = [];
        $deliveryStatuses = [];
        $funnelRows = [];

        foreach ($funnels as $funnel) {
            $analytics = $tracking->analyticsForFunnel($funnel);
            $totals = $analytics['physical_order_totals'] ?? [];
            $orders = collect($analytics['physical_orders'] ?? []);

            $summary['total_orders'] += (int) ($totals['total_orders'] ?? 0);
            $summary['paid_orders'] += (int) ($totals['paid_orders'] ?? 0);
            $summary['pending_orders'] += (int) ($totals['pending_orders'] ?? 0);
            $summary['abandoned_orders'] += (int) ($totals['abandoned_orders'] ?? 0);
            $summary['units_ordered'] += (int) ($totals['units_ordered'] ?? 0);
            $summary['paid_revenue'] += (float) ($totals['paid_revenue'] ?? 0);

            $pendingRevenue = (float) $orders
                ->where('order_status', 'pending')
                ->sum(fn (array $row) => (float) ($row['checkout_amount'] ?? 0));
            $summary['pending_revenue'] += $pendingRevenue;

            $funnelRows[] = [
                'id' => $funnel->id,
                'name' => $funnel->name,
                'slug' => $funnel->slug,
                'purpose' => $funnel->purposeLabel(),
                'total_orders' => (int) ($totals['total_orders'] ?? 0),
                'paid_orders' => (int) ($totals['paid_orders'] ?? 0),
                'pending_orders' => (int) ($totals['pending_orders'] ?? 0),
                'units_ordered' => (int) ($totals['units_ordered'] ?? 0),
                'paid_revenue' => round((float) ($totals['paid_revenue'] ?? 0), 2),
            ];

            foreach ($orders as $row) {
                $deliveryStatus = trim((string) ($row['delivery_status'] ?? ''));
                if ($deliveryStatus !== '') {
                    $deliveryStatuses[$deliveryStatus] = ($deliveryStatuses[$deliveryStatus] ?? 0) + 1;
                }

                foreach ((is_array($row['order_items'] ?? null) ? $row['order_items'] : []) as $item) {
                    if (! is_array($item)) {
                        continue;
                    }

                    $name = trim((string) ($item['name'] ?? ''));
                    if ($name === '') {
                        continue;
                    }

                    $key = mb_strtolower($name);
                    $quantity = max(1, (int) ($item['quantity'] ?? 1));

                    if (! isset($products[$key])) {
                        $products[$key] = [
                            'name' => $name,
                            'units' => 0,
                            'paid_units' => 0,
                            'orders' => 0,
                        ];
                    }

                    $products[$key]['units'] += $quantity;
                    $products[$key]['orders'] += 1;

                    if (($row['order_status'] ?? '') === 'paid') {
                        $products[$key]['paid_units'] += $quantity;
                    }
                }
            }
        }

        $summary['paid_revenue'] = round($summary['paid_revenue'], 2);
        $summary['pending_revenue'] = round($summary['pending_revenue'], 2);
        $summary['average_paid_order_value'] = $summary['paid_orders'] > 0
            ? round($summary['paid_revenue'] / $summary['paid_orders'], 2)
            : 0.0;
        $summary['top_products'] = collect($products)
            ->sortByDesc(fn (array $row) => [$row['paid_units'], $row['units'], $row['orders']])
            ->take(5)
            ->values()
            ->all();
        $summary['top_funnels'] = collect($funnelRows)
            ->sortByDesc(fn (array $row) => [$row['paid_revenue'], $row['paid_orders'], $row['total_orders']])
            ->take(5)
            ->values()
            ->all();
        $summary['delivery_statuses'] = collect($deliveryStatuses)
            ->sortByDesc(fn (int $count) => $count)
            ->mapWithKeys(fn (int $count, string $status) => [str_replace('_', ' ', $status) => $count])
            ->all();

        return $summary;
    }
}
