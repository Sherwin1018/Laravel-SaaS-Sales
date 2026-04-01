<?php

namespace App\Services;

use App\Models\FunnelVisit;
use App\Models\Lead;
use App\Models\LeadLinkClick;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class UTMAnalyticsService
{
    /**
     * Get source performance data for the UTM Pipeline Analytics dashboard
     * Returns the core table: Visits → Leads → Pipeline Stages → Won
     */
    public function getSourcePerformance(int $tenantId, array $filters = []): Collection
    {
        $query = "
            SELECT 
                COALESCE(fv.utm_source, l.source_campaign, 'Unspecified') as source,
                COUNT(DISTINCT fv.id) as visits,
                COUNT(DISTINCT l.id) as leads,
                SUM(CASE WHEN l.status = 'contacted' THEN 1 ELSE 0 END) as contacted,
                SUM(CASE WHEN l.status = 'proposal_sent' THEN 1 ELSE 0 END) as proposal,
                SUM(CASE WHEN l.status = 'closed_won' THEN 1 ELSE 0 END) as won,
                ROUND(
                    SUM(CASE WHEN l.status = 'closed_won' THEN 1 ELSE 0 END) * 100.0 / 
                    NULLIF(COUNT(DISTINCT l.id), 0), 2
                ) as conversion_rate
            FROM funnel_visits fv
            LEFT JOIN leads l ON fv.tenant_id = l.tenant_id 
                AND (fv.utm_source = l.source_campaign OR l.source_campaign IS NULL)
            WHERE fv.tenant_id = ?
            GROUP BY COALESCE(fv.utm_source, l.source_campaign, 'Unspecified')
            ORDER BY leads DESC
        ";

        $results = DB::select($query, [$tenantId]);
        
        return collect($results)->map(function($item) {
            return [
                'source' => $item->source,
                'visits' => (int) $item->visits,
                'leads' => (int) $item->leads,
                'contacted' => (int) $item->contacted,
                'proposal' => (int) $item->proposal,
                'won' => (int) $item->won,
                'conversion_rate' => (float) $item->conversion_rate,
                'performance_indicator' => $this->getPerformanceIndicator((float) $item->conversion_rate),
            ];
        });
    }

    /**
     * Get pipeline flow data broken down by source
     * Used for visual funnel representation
     */
    public function getPipelineFlowBySource(int $tenantId): array
    {
        $sources = $this->getSourcePerformance($tenantId);
        
        return $sources->mapWithKeys(function($sourceData) use ($tenantId) {
            $flow = [
                'visits' => $sourceData['visits'],
                'leads' => $sourceData['leads'],
                'contacted' => $sourceData['contacted'],
                'proposal' => $sourceData['proposal'],
                'won' => $sourceData['won'],
            ];
            
            // Calculate conversion rates between stages
            $rates = [
                'visit_to_lead' => $sourceData['visits'] > 0 ? 
                    round(($sourceData['leads'] / $sourceData['visits']) * 100, 1) : 0,
                'lead_to_contacted' => $sourceData['leads'] > 0 ? 
                    round(($sourceData['contacted'] / $sourceData['leads']) * 100, 1) : 0,
                'contacted_to_proposal' => $sourceData['contacted'] > 0 ? 
                    round(($sourceData['proposal'] / $sourceData['contacted']) * 100, 1) : 0,
                'proposal_to_won' => $sourceData['proposal'] > 0 ? 
                    round(($sourceData['won'] / $sourceData['proposal']) * 100, 1) : 0,
            ];
            
            return [$sourceData['source'] => [
                'flow' => $flow,
                'rates' => $rates,
                'performance' => $sourceData['performance_indicator'],
            ]];
        })->toArray();
    }

    /**
     * Get hot leads with priority scoring based on engagement and pipeline stage
     * Priority = Lead Score + (Link Clicks × 5) + Stage Weight
     */
    public function getHotLeads(int $tenantId, int $limit = 5): Collection
    {
        $stageWeights = [
            'new' => 10,
            'contacted' => 25,
            'proposal_sent' => 50,
        ];

        return Lead::select('name', 'source_campaign as source', 'status', 'score')
            ->withCount(['linkClicks' => function($query) {
                $query->where('clicked_at', '>=', now()->subDays(7));
            }])
            ->where('tenant_id', $tenantId)
            ->whereNotIn('status', ['closed_won', 'closed_lost'])
            ->get()
            ->map(function($lead) use ($stageWeights) {
                $clicksCount = $lead->link_clicks_count ?? 0;
                $stageWeight = $stageWeights[$lead->status] ?? 0;
                
                // Calculate priority score
                $priorityScore = $lead->score + ($clicksCount * 5) + $stageWeight;
                
                return [
                    'name' => $lead->name,
                    'source' => $lead->source ?: 'Unspecified',
                    'status' => $lead->status,
                    'score' => $lead->score,
                    'clicks' => $clicksCount,
                    'priority_score' => $priorityScore,
                    'priority_indicator' => $this->getPriorityIndicator($priorityScore),
                ];
            })
            ->sortByDesc('priority_score')
            ->take($limit)
            ->values();
    }

    /**
     * Get overall KPI metrics for the dashboard header
     */
    public function getKpiMetrics(int $tenantId): array
    {
        $totalLeads = Lead::where('tenant_id', $tenantId)->count();
        $closedWon = Lead::where('tenant_id', $tenantId)->where('status', 'closed_won')->count();
        $totalVisits = FunnelVisit::where('tenant_id', $tenantId)->count();
        
        // Calculate pipeline value (estimated based on leads in pipeline)
        $pipelineLeads = Lead::where('tenant_id', $tenantId)
            ->whereNotIn('status', ['closed_won', 'closed_lost'])
            ->count();
        
        // Estimate average deal size (this could be enhanced with actual payment data)
        $avgDealSize = 5000; // Default estimate - could be calculated from actual payments
        $pipelineValue = $pipelineLeads * $avgDealSize;
        
        $conversionRate = $totalLeads > 0 ? 
            round(($closedWon / $totalLeads) * 100, 1) : 0;

        return [
            'pipeline_value' => $pipelineValue,
            'total_deals' => $totalLeads,
            'closed_won' => $closedWon,
            'conversion_rate' => $conversionRate,
            'total_visits' => $totalVisits,
        ];
    }

    /**
     * Get pipeline flow distribution (for the middle left panel)
     */
    public function getPipelineFlow(int $tenantId): array
    {
        $pipelineData = Lead::select('status', DB::raw('COUNT(*) as count'))
            ->where('tenant_id', $tenantId)
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Ensure all pipeline stages are present
        $stages = ['new', 'contacted', 'proposal_sent', 'closed_won', 'closed_lost'];
        $flow = [];
        
        foreach ($stages as $stage) {
            $flow[$stage] = $pipelineData[$stage] ?? 0;
        }

        // Calculate conversion rates between stages
        $totalLeads = array_sum($flow);
        $conversionRates = [];
        
        if ($flow['new'] > 0) {
            $conversionRates['new_to_contacted'] = round(($flow['contacted'] / $flow['new']) * 100, 1);
        }
        
        if ($flow['contacted'] > 0) {
            $conversionRates['contacted_to_proposal'] = round(($flow['proposal_sent'] / $flow['contacted']) * 100, 1);
        }
        
        if ($flow['proposal_sent'] > 0) {
            $conversionRates['proposal_to_won'] = round(($flow['closed_won'] / $flow['proposal_sent']) * 100, 1);
        }

        return [
            'flow' => $flow,
            'conversion_rates' => $conversionRates,
            'total_leads' => $totalLeads,
        ];
    }

    /**
     * Determine performance indicator based on conversion rate
     */
    private function getPerformanceIndicator(float $conversionRate): string
    {
        if ($conversionRate >= 15) {
            return '🔥'; // Excellent
        } elseif ($conversionRate >= 8) {
            return '📈'; // Good
        } elseif ($conversionRate >= 3) {
            return '➡️'; // Average
        } else {
            return '❌'; // Poor
        }
    }

    /**
     * Get enhanced link performance with conversion and revenue data
     * Shows which CTAs actually convert and generate revenue
     */
    public function getLinkPerformance(int $tenantId): Collection
    {
        return LeadLinkClick::select('link_name', 
            DB::raw('COUNT(*) as clicks'),
            DB::raw('COUNT(DISTINCT lead_id) as leads'),
            DB::raw('SUM(CASE WHEN l.status = "closed_won" THEN 1 ELSE 0 END) as won'),
            DB::raw('AVG(l.score) as avg_score'))
        ->join('leads as l', 'lead_link_clicks.lead_id', '=', 'l.id')
        ->where('lead_link_clicks.tenant_id', $tenantId)
        ->whereNotNull('link_name')
        ->groupBy('link_name')
        ->orderByDesc('clicks')
        ->get()
        ->map(function($item) {
            $clicks = (int) $item->clicks;
            $leads = (int) $item->leads;
            $won = (int) $item->won;
            
            // Calculate conversion rates
            $clickToLeadRate = $clicks > 0 ? round(($leads / $clicks) * 100, 1) : 0;
            $leadToWinRate = $leads > 0 ? round(($won / $leads) * 100, 1) : 0;
            
            // Estimate revenue (₱5,000 average deal size - could be calculated from actual payments)
            $estimatedRevenue = $won * 5000;
            
            return [
                'link_name' => $item->link_name,
                'clicks' => $clicks,
                'leads' => $leads,
                'won' => $won,
                'avg_score' => round((float) $item->avg_score, 1),
                'click_to_lead_rate' => $clickToLeadRate,
                'lead_to_win_rate' => $leadToWinRate,
                'estimated_revenue' => $estimatedRevenue,
                'performance_indicator' => $this->getLinkPerformanceIndicator($clickToLeadRate, $leadToWinRate),
                'action_recommendation' => $this->getLinkActionRecommendation($clickToLeadRate, $leadToWinRate, $clicks),
            ];
        });
    }

    /**
     * Determine performance indicator for links
     */
    private function getLinkPerformanceIndicator(float $clickToLeadRate, float $leadToWinRate): string
    {
        if ($clickToLeadRate >= 30 && $leadToWinRate >= 25) {
            return '�'; // Excellent
        } elseif ($clickToLeadRate >= 15 && $leadToWinRate >= 10) {
            return '📈'; // Good
        } elseif ($clickToLeadRate >= 5 || $leadToWinRate >= 5) {
            return '➡️'; // Average
        } else {
            return '❌'; // Poor
        }
    }

    /**
     * Get action recommendation for link optimization
     */
    private function getLinkActionRecommendation(float $clickToLeadRate, float $leadToWinRate, int $clicks): string
    {
        if ($clickToLeadRate >= 30 && $leadToWinRate >= 25) {
            return 'Scale up - high performer!';
        } elseif ($clickToLeadRate >= 15 && $leadToWinRate >= 10) {
            return 'Good potential - optimize placement';
        } elseif ($clicks >= 5 && $clickToLeadRate < 5) {
            return 'Fix landing page - clicks but no leads';
        } elseif ($clicks < 3 && $leadToWinRate > 0) {
            return 'Increase visibility - converts well';
        } else {
            return 'Test new approach';
        }
    }

    /**
     * Determine priority indicator based on priority score
     */
    private function getPriorityIndicator(int $priorityScore): string
    {
        if ($priorityScore >= 80) {
            return '🔥'; // High priority
        } elseif ($priorityScore >= 50) {
            return '📈'; // Medium priority
        } elseif ($priorityScore >= 20) {
            return '➡️'; // Low priority
        } else {
            return '❄️'; // Cold
        }
    }
}
