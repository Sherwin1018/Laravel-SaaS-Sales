<?php

namespace App\Services;

use App\Models\CommissionEntry;
use App\Models\FunnelTemplate;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class TemplateMarketplaceAnalyticsService
{
    /**
     * @return array<string, mixed>
     */
    public function overview(?int $templateId = null): array
    {
        $selectedTemplate = $templateId
            ? FunnelTemplate::query()
                ->whereKey($templateId)
                ->where('template_type', FunnelTemplate::TEMPLATE_TYPE_STEP_BY_STEP)
                ->first()
            : null;

        $templates = FunnelTemplate::query()
            ->where('template_type', FunnelTemplate::TEMPLATE_TYPE_STEP_BY_STEP)
            ->when(
                $selectedTemplate,
                fn ($query) => $query->whereKey($selectedTemplate->id),
                fn ($query) => $query->where('status', 'published')
            );

        $templateIds = (clone $templates)->pluck('id');
        $templateIdsList = $templateIds->all();

        $templatePayments = Payment::query()
            ->whereIn('source_funnel_template_id', $templateIdsList)
            ->where('payment_type', Payment::TYPE_FUNNEL_CHECKOUT)
            ->where('status', 'paid');

        $payoutLiabilities = CommissionEntry::query()
            ->whereIn('commission_entries.commission_type', ['template_royalty', 'affiliate_sale', 'platform_referral'])
            ->whereIn('commission_entries.status', [CommissionEntry::STATUS_HELD, CommissionEntry::STATUS_PAYABLE, CommissionEntry::STATUS_APPROVED]);

        if ($selectedTemplate) {
            $payoutLiabilities
                ->join('payments', 'payments.id', '=', 'commission_entries.payment_id')
                ->where('payments.source_funnel_template_id', $selectedTemplate->id);
        }

        return [
            'totals' => [
                'published_templates' => (clone $templates)->where('status', 'published')->count(),
                'cloned_funnels' => DB::table('funnels')
                    ->whereIn('source_template_id', $templateIdsList)
                    ->count(),
                'gross_revenue' => round((float) (clone $templatePayments)->sum('amount'), 2),
                'net_revenue' => round((float) (clone $templatePayments)
                    ->selectRaw('COALESCE(SUM(amount - refund_amount), 0) as total')
                    ->value('total'), 2),
                'template_royalty_total' => round((float) (clone $templatePayments)->sum('template_royalty_amount'), 2),
                'affiliate_commission_total' => round((float) (clone $templatePayments)->sum('affiliate_commission_amount'), 2),
                'payout_liabilities' => round((float) $payoutLiabilities->sum('commission_entries.commission_amount'), 2),
            ],
            'template_rows' => $this->templateRows($selectedTemplate?->id),
            'top_tenants' => $this->topTenants($templateIdsList),
            'top_sources' => $this->topSources($templateIdsList),
        ];
    }

    private function templateRows(?int $templateId = null)
    {
        return FunnelTemplate::query()
            ->from('funnel_templates as templates')
            ->leftJoin('funnels', 'funnels.source_template_id', '=', 'templates.id')
            ->leftJoin('payments', function ($join) {
                $join->on('payments.source_funnel_template_id', '=', 'templates.id')
                    ->where('payments.payment_type', '=', Payment::TYPE_FUNNEL_CHECKOUT)
                    ->where('payments.status', '=', 'paid');
            })
            ->where('templates.template_type', FunnelTemplate::TEMPLATE_TYPE_STEP_BY_STEP)
            ->when(
                $templateId,
                fn ($query) => $query->where('templates.id', $templateId),
                fn ($query) => $query->where('templates.status', 'published')
            )
            ->groupBy('templates.id', 'templates.name', 'templates.slug', 'templates.royalty_rate')
            ->selectRaw('
                templates.id,
                templates.name,
                templates.slug,
                templates.royalty_rate,
                COUNT(DISTINCT funnels.id) as cloned_funnels_count,
                COUNT(DISTINCT funnels.tenant_id) as tenant_count,
                COUNT(payments.id) as paid_orders_count,
                COALESCE(SUM(payments.amount), 0) as gross_revenue,
                COALESCE(SUM(payments.amount - payments.refund_amount), 0) as net_revenue,
                COALESCE(SUM(payments.template_royalty_amount), 0) as template_royalty_total,
                COALESCE(SUM(payments.affiliate_commission_amount), 0) as affiliate_commission_total,
                AVG(NULLIF(payments.amount, 0)) as average_order_value
            ')
            ->orderByDesc('gross_revenue')
            ->get();
    }

    private function topTenants(array $templateIds)
    {
        if ($templateIds === []) {
            return collect();
        }

        return DB::table('payments')
            ->join('tenants', 'tenants.id', '=', 'payments.tenant_id')
            ->where('payments.payment_type', Payment::TYPE_FUNNEL_CHECKOUT)
            ->where('payments.status', 'paid')
            ->whereIn('payments.source_funnel_template_id', $templateIds)
            ->groupBy('payments.tenant_id', 'tenants.company_name')
            ->selectRaw('tenants.company_name, COUNT(payments.id) as paid_orders, COALESCE(SUM(payments.amount), 0) as gross_revenue')
            ->orderByDesc('gross_revenue')
            ->limit(8)
            ->get();
    }

    private function topSources(array $templateIds)
    {
        if ($templateIds === []) {
            return collect();
        }

        return Payment::query()
            ->where('payment_type', Payment::TYPE_FUNNEL_CHECKOUT)
            ->where('status', 'paid')
            ->whereIn('source_funnel_template_id', $templateIds)
            ->selectRaw("COALESCE(NULLIF(source_platform, ''), 'Unspecified') as source_label, COUNT(*) as paid_orders, COALESCE(SUM(amount), 0) as gross_revenue")
            ->groupBy('source_label')
            ->orderByDesc('gross_revenue')
            ->limit(8)
            ->get();
    }
}
