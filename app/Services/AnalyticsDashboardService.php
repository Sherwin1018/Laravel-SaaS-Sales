<?php

namespace App\Services;

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
}
