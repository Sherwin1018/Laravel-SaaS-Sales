<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function owner()
    {
        $tenantId = auth()->user()->tenant_id;

        $totalLeads = Lead::where('tenant_id', $tenantId)->count();
        $leadsThisMonth = Lead::where('tenant_id', $tenantId)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();

        $wonCount = Lead::where('tenant_id', $tenantId)->where('status', 'closed_won')->count();
        $closedCount = Lead::where('tenant_id', $tenantId)
            ->whereIn('status', ['closed_won', 'closed_lost'])
            ->count();
        $conversionRate = $closedCount > 0 ? round(($wonCount / $closedCount) * 100, 1) : 0;

        $leadsByStatus = Lead::select('status', DB::raw('COUNT(*) as total'))
            ->where('tenant_id', $tenantId)
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        $revenueTotal = Payment::where('tenant_id', $tenantId)
            ->where('status', 'paid')
            ->sum('amount');

        return view('dashboard.account-owner', compact(
            'totalLeads',
            'leadsThisMonth',
            'conversionRate',
            'leadsByStatus',
            'revenueTotal'
        ));
    }
}
