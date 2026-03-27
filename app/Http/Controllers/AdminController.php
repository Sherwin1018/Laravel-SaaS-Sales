<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Payment;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function index()
    {
        $monthKeyExpression = DB::getDriverName() === 'pgsql'
            ? "TO_CHAR(created_at, 'YYYY-MM')"
            : "DATE_FORMAT(created_at, '%Y-%m')";

        $tenantCount = Tenant::count();
        $activeTenantCount = Tenant::where('status', 'active')->count();
        $trialTenantCount = Tenant::where('status', 'trial')->count();
        $userCount = User::count();
        $leadCount = Lead::withoutGlobalScope('tenant')->count();

        $mrr = Payment::where('status', 'paid')
            ->whereYear('payment_date', now()->year)
            ->whereMonth('payment_date', now()->month)
            ->sum('amount');

        $paymentStatusTotals = Payment::select('status', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total'))
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        $usersByRole = Role::withCount('users')
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        $leadTrendRaw = Lead::withoutGlobalScope('tenant')
            ->selectRaw("{$monthKeyExpression} as month_key, COUNT(*) as total")
            ->where('created_at', '>=', now()->copy()->subMonths(5)->startOfMonth())
            ->groupBy('month_key')
            ->pluck('total', 'month_key');

        $leadTrendLabels = [];
        $leadTrendValues = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->copy()->subMonths($i);
            $key = $month->format('Y-m');
            $leadTrendLabels[] = $month->format('M Y');
            $leadTrendValues[] = (int) ($leadTrendRaw[$key] ?? 0);
        }

        $actionableTenants = Tenant::whereIn('status', ['trial', 'inactive'])
            ->latest()
            ->paginate(10, ['id', 'company_name', 'status', 'subscription_plan', 'created_at'], 'tenants_page');

        return view('admin.dashboard', compact(
            'tenantCount',
            'activeTenantCount',
            'trialTenantCount',
            'userCount',
            'leadCount',
            'mrr',
            'paymentStatusTotals',
            'usersByRole',
            'leadTrendLabels',
            'leadTrendValues',
            'actionableTenants'
        ));
    }
}
