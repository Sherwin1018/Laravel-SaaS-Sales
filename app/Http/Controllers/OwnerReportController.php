<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\CommissionService;
use App\Services\OwnerReportService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OwnerReportController extends Controller
{
    public function index(Request $request, OwnerReportService $reports)
    {
        $tenant = $request->user()->tenant?->loadMissing('defaultPayoutAccount');
        abort_if(! $tenant, 404);

        return view('reports.owner', [
            'report' => $reports->build($tenant, $request->only(['date_from', 'date_to', 'funnel_id'])),
        ]);
    }

    public function export(Request $request, OwnerReportService $reports)
    {
        $tenant = $request->user()->tenant;
        abort_if(! $tenant, 404);

        return $reports->export($tenant, $request->only(['date_from', 'date_to', 'funnel_id']));
    }

    public function updateCommissionPlan(Request $request, CommissionService $commissions)
    {
        $tenant = $request->user()->tenant;
        abort_if(! $tenant, 404);

        $marketingManagerIds = User::query()
            ->where('tenant_id', $tenant->id)
            ->whereHas('roles', fn ($query) => $query->where('slug', 'marketing-manager'))
            ->pluck('id')
            ->all();

        $validated = $request->validate([
            'gateway_fee_rate' => 'required|numeric|min:0|max:100',
            'platform_fee_rate' => 'required|numeric|min:0|max:100',
            'affiliate_sale_rate' => 'required|numeric|min:0|max:100',
            'sales_agent_rate' => 'required|numeric|min:0|max:100',
            'marketing_manager_rate' => 'required|numeric|min:0|max:100',
            'hold_days' => 'required|integer|min:0|max:365',
            'default_marketing_manager_user_id' => [
                'nullable',
                'integer',
                Rule::in($marketingManagerIds),
            ],
        ]);

        $gatewayPlusPlatform = (float) $validated['gateway_fee_rate'] + (float) $validated['platform_fee_rate'];
        $commissionPool = (float) $validated['affiliate_sale_rate']
            + (float) $validated['sales_agent_rate']
            + (float) $validated['marketing_manager_rate'];

        if ($gatewayPlusPlatform >= 100) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gateway and platform fees combined must stay below 100%.');
        }

        if ($commissionPool > 100) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Affiliate, sales, and marketing commissions combined cannot exceed 100% of the eligible basis.');
        }

        $plan = $commissions->resolvePlanForTenant($tenant);
        $plan->update([
            'gateway_fee_rate' => round((float) $validated['gateway_fee_rate'], 2),
            'platform_fee_rate' => round((float) $validated['platform_fee_rate'], 2),
            'affiliate_sale_rate' => round((float) $validated['affiliate_sale_rate'], 2),
            'sales_agent_rate' => round((float) $validated['sales_agent_rate'], 2),
            'marketing_manager_rate' => round((float) $validated['marketing_manager_rate'], 2),
            'hold_days' => (int) $validated['hold_days'],
            'default_marketing_manager_user_id' => $validated['default_marketing_manager_user_id'] ?? null,
        ]);

        return redirect()
            ->route('reports.owner')
            ->with('success', 'Commission settings updated successfully.');
    }
}
