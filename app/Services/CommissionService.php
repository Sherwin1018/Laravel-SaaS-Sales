<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\CommissionEntry;
use App\Models\CommissionPlan;
use App\Models\FunnelTemplate;
use App\Models\Payment;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Collection;

class CommissionService
{
    public function resolvePlanForTenant(Tenant $tenant): CommissionPlan
    {
        $plan = CommissionPlan::query()
            ->where('tenant_id', $tenant->id)
            ->orderByDesc('is_default')
            ->orderByDesc('is_active')
            ->latest('id')
            ->first();

        if ($plan) {
            return $plan;
        }

        $defaultMarketingManager = User::query()
            ->where('tenant_id', $tenant->id)
            ->whereHas('roles', fn ($query) => $query->where('slug', 'marketing-manager'))
            ->orderBy('id')
            ->first();

        return CommissionPlan::create([
            'tenant_id' => $tenant->id,
            'name' => 'Default Commission Plan',
            'is_active' => true,
            'is_default' => true,
            'gateway_fee_rate' => 3.00,
            'platform_fee_rate' => 2.00,
            'sales_agent_rate' => 7.00,
            'marketing_manager_rate' => 3.00,
            'affiliate_sale_rate' => 5.00,
            'platform_referral_rate' => 10.00,
            'hold_days' => 7,
            'sales_attribution_model' => 'assigned_lead',
            'marketing_attribution_model' => 'last_touch_campaign',
            'default_marketing_manager_user_id' => $defaultMarketingManager?->id,
        ]);
    }

    public function syncTenant(Tenant $tenant): void
    {
        $plan = $this->resolvePlanForTenant($tenant);

        Payment::query()
            ->with(['lead.assignedAgent.roles', 'lead', 'tenant', 'funnel.sourceTemplate.creator.roles', 'sourceTemplate.creator.roles', 'referrer.roles'])
            ->where('tenant_id', $tenant->id)
            ->where('status', 'paid')
            ->chunkById(100, function (Collection $payments) use ($plan) {
                foreach ($payments as $payment) {
                    $this->syncPayment($payment, $plan);
                }
            });

        Payment::query()
            ->where('tenant_id', $tenant->id)
            ->where('status', 'failed')
            ->chunkById(100, function (Collection $payments) {
                foreach ($payments as $payment) {
                    $this->reverseForPayment($payment, 'payment_failed');
                }
            });

        $this->releasePayableForTenant($tenant);
    }

    public function syncPayment(Payment $payment, ?CommissionPlan $plan = null): void
    {
        if (! $payment->isFunnelSale() && ! $payment->isPlatformSubscription()) {
            return;
        }

        if ($payment->status !== 'paid') {
            $this->reverseForPayment($payment, 'payment_not_paid');

            return;
        }

        $payment->loadMissing([
            'tenant',
            'lead.assignedAgent.roles',
            'commissionEntries',
            'funnel.sourceTemplate.creator.roles',
            'sourceTemplate.creator.roles',
            'referrer.roles',
        ]);
        $tenant = $payment->tenant;
        if (! $tenant) {
            return;
        }

        $plan ??= $this->resolvePlanForTenant($tenant);

        if ($payment->isPlatformSubscription()) {
            $this->syncPlatformReferralPayment($payment, $plan);

            return;
        }

        $grossAmount = round((float) $payment->amount, 2);
        $gatewayFee = $this->estimateGatewayFee($grossAmount, $plan);
        $platformFee = $this->estimatePlatformFee($grossAmount, $plan);
        $basisAmount = $this->calculateNetEligibleAmount($payment, $plan);
        if ($basisAmount <= 0) {
            $this->reverseForPayment($payment, 'basis_amount_below_zero');

            $payment->update([
                'gateway_fee_amount' => $gatewayFee,
                'platform_share_amount' => $platformFee,
                'commissionable_amount' => 0,
                'template_royalty_amount' => 0,
                'affiliate_commission_amount' => 0,
                'sales_commission_amount' => 0,
                'marketing_commission_amount' => 0,
                'tenant_net_income_amount' => 0,
            ]);

            return;
        }

        $definitions = $this->commissionRecipients($payment, $plan, $basisAmount, $grossAmount);
        $entriesToKeep = [];
        foreach ($definitions as $definition) {
            /** @var User $user */
            $user = $definition['user'];
            $type = (string) $definition['commission_type'];
            $entriesToKeep[] = $user->id . ':' . $type;

            $holdUntil = $plan->hold_days > 0 ? now()->addDays($plan->hold_days) : null;
            $attributes = [
                'tenant_id' => $tenant->id,
                'commission_plan_id' => $plan->id,
                'lead_id' => $payment->lead_id,
                'commission_role' => (string) $definition['commission_role'],
                'gross_amount' => $grossAmount,
                'basis_amount' => $basisAmount,
                'rate_percentage' => round((float) $definition['rate_percentage'], 2),
                'commission_amount' => round((float) $definition['commission_amount'], 2),
                'status' => $holdUntil ? CommissionEntry::STATUS_HELD : CommissionEntry::STATUS_PAYABLE,
                'hold_until' => $holdUntil,
                'notes' => 'Generated automatically from funnel payment #' . $payment->id . '.',
                'meta' => $definition['meta'],
            ];

            $entry = CommissionEntry::query()->where([
                'payment_id' => $payment->id,
                'user_id' => $user->id,
                'commission_type' => $type,
            ])->first();

            if ($entry) {
                if (in_array($entry->status, [CommissionEntry::STATUS_PAID, CommissionEntry::STATUS_APPROVED], true)) {
                    unset($attributes['status'], $attributes['hold_until']);
                }

                $entry->update($attributes);

                continue;
            }

            $entry = CommissionEntry::create(array_merge($attributes, [
                'payment_id' => $payment->id,
                'user_id' => $user->id,
                'commission_type' => $type,
            ]));

            $this->dispatchAutomationEvent('commission_created', [
                'tenant_id' => $tenant->id,
                'payment_id' => $payment->id,
                'commission_entry_id' => $entry->id,
                'commission_type' => $entry->commission_type,
                'user_id' => $user->id,
            ]);
        }

        CommissionEntry::query()
            ->where('payment_id', $payment->id)
            ->whereNotIn('status', [CommissionEntry::STATUS_REVERSED, CommissionEntry::STATUS_CANCELLED])
            ->get()
            ->reject(fn (CommissionEntry $entry) => in_array($entry->user_id . ':' . $entry->commission_type, $entriesToKeep, true))
            ->each(function (CommissionEntry $entry) {
                $entry->update([
                    'status' => CommissionEntry::STATUS_CANCELLED,
                    'reversed_at' => now(),
                    'notes' => 'Cancelled because the current attribution rules no longer assign this commission.',
                ]);
            });

        $templateRoyaltyAmount = $this->sumDefinitionAmount($definitions, 'template_royalty');
        $affiliateAmount = $this->sumDefinitionAmount($definitions, 'affiliate_sale');
        $salesAmount = $this->sumDefinitionAmount($definitions, 'sales_agent');
        $marketingAmount = $this->sumDefinitionAmount($definitions, 'marketing_manager');

        $payment->update([
            'gateway_fee_amount' => $gatewayFee,
            'platform_share_amount' => $platformFee,
            'commissionable_amount' => $basisAmount,
            'template_royalty_amount' => $templateRoyaltyAmount,
            'affiliate_commission_amount' => $affiliateAmount,
            'sales_commission_amount' => $salesAmount,
            'marketing_commission_amount' => $marketingAmount,
            'tenant_net_income_amount' => round(max(0, $basisAmount - $templateRoyaltyAmount - $affiliateAmount - $salesAmount - $marketingAmount), 2),
        ]);
    }

    public function reverseForPayment(Payment $payment, string $reason = 'payment_reversed'): int
    {
        $entries = CommissionEntry::query()
            ->where('payment_id', $payment->id)
            ->whereNotIn('status', [CommissionEntry::STATUS_REVERSED, CommissionEntry::STATUS_CANCELLED])
            ->get();

        foreach ($entries as $entry) {
            $entry->update([
                'status' => CommissionEntry::STATUS_REVERSED,
                'reversed_at' => now(),
                'notes' => 'Reversed automatically: ' . str_replace('_', ' ', $reason) . '.',
            ]);
        }

        return $entries->count();
    }

    public function releasePayableForTenant(Tenant $tenant): int
    {
        $entries = CommissionEntry::query()
            ->where('tenant_id', $tenant->id)
            ->where('status', CommissionEntry::STATUS_HELD)
            ->whereNotNull('hold_until')
            ->where('hold_until', '<=', now())
            ->get();

        return $this->markEntriesPayable($entries);
    }

    public function releaseDueCommissions(): int
    {
        $entries = CommissionEntry::query()
            ->where('status', CommissionEntry::STATUS_HELD)
            ->whereNotNull('hold_until')
            ->where('hold_until', '<=', now())
            ->get();

        return $this->markEntriesPayable($entries);
    }

    private function markEntriesPayable(Collection $entries): int
    {
        foreach ($entries as $entry) {
            $entry->update([
                'status' => CommissionEntry::STATUS_PAYABLE,
            ]);

            $this->dispatchAutomationEvent('commission_payable', [
                'tenant_id' => $entry->tenant_id,
                'commission_entry_id' => $entry->id,
                'payment_id' => $entry->payment_id,
                'user_id' => $entry->user_id,
            ]);
        }

        return $entries->count();
    }

    public function summaryForUser(User $user): array
    {
        $entries = CommissionEntry::query()
            ->where('user_id', $user->id)
            ->get(['commission_amount', 'status']);

        return [
            'held_total' => round((float) $entries->where('status', CommissionEntry::STATUS_HELD)->sum('commission_amount'), 2),
            'payable_total' => round((float) $entries->where('status', CommissionEntry::STATUS_PAYABLE)->sum('commission_amount'), 2),
            'paid_total' => round((float) $entries->where('status', CommissionEntry::STATUS_PAID)->sum('commission_amount'), 2),
            'active_count' => $entries->whereIn('status', [
                CommissionEntry::STATUS_HELD,
                CommissionEntry::STATUS_PAYABLE,
                CommissionEntry::STATUS_APPROVED,
            ])->count(),
        ];
    }

    public function estimatePlatformFee(float $grossAmount, CommissionPlan $plan): float
    {
        return round($grossAmount * ((float) $plan->platform_fee_rate / 100), 2);
    }

    public function estimateGatewayFee(float $grossAmount, CommissionPlan $plan): float
    {
        return round($grossAmount * ((float) $plan->gateway_fee_rate / 100), 2);
    }

    public function calculateNetEligibleAmount(Payment $payment, CommissionPlan $plan): float
    {
        $grossAmount = round((float) $payment->amount, 2);
        $refundAmount = round((float) ($payment->refund_amount ?? 0), 2);
        $nonCommissionableAmount = round((float) ($payment->non_commissionable_amount ?? 0), 2);
        $gatewayFee = $this->estimateGatewayFee($grossAmount, $plan);
        $platformFee = $this->estimatePlatformFee($grossAmount, $plan);

        return round(max(0, $grossAmount - $refundAmount - $nonCommissionableAmount - $gatewayFee - $platformFee), 2);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function commissionRecipients(Payment $payment, CommissionPlan $plan, float $basisAmount, float $grossAmount): array
    {
        $definitions = [];
        $lead = $payment->lead;
        $sourceTemplate = $payment->sourceTemplate ?: $payment->funnel?->sourceTemplate;
        $referrer = $payment->referrer;

        if ($sourceTemplate && $sourceTemplate->creator) {
            $royaltyRate = $this->resolveTemplateRoyaltyRate($sourceTemplate);
            if ($royaltyRate > 0) {
                $definitions[] = [
                    'user' => $sourceTemplate->creator,
                    'commission_role' => 'super-admin',
                    'commission_type' => 'template_royalty',
                    'rate_percentage' => $royaltyRate,
                    'commission_amount' => round($basisAmount * ($royaltyRate / 100), 2),
                    'meta' => [
                        'source_template_id' => $sourceTemplate->id,
                        'source_template_name' => $sourceTemplate->name,
                        'gross_amount' => $grossAmount,
                    ],
                ];
            }
        }

        if ($referrer && $payment->referrer_user_id !== (int) ($lead?->assigned_to ?? 0)) {
            $affiliateRate = round((float) $plan->affiliate_sale_rate, 2);
            if ($affiliateRate > 0) {
                $definitions[] = [
                    'user' => $referrer,
                    'commission_role' => 'affiliate',
                    'commission_type' => 'affiliate_sale',
                    'rate_percentage' => $affiliateRate,
                    'commission_amount' => round($basisAmount * ($affiliateRate / 100), 2),
                    'meta' => [
                        'referral_code' => $payment->referral_code_snapshot,
                        'source_platform' => $payment->source_platform,
                        'source_campaign' => $payment->source_campaign,
                        'gross_amount' => $grossAmount,
                    ],
                ];
            }
        }

        if ($lead?->assignedAgent && $lead->assignedAgent->hasRole('sales-agent')) {
            $salesRate = round((float) $plan->sales_agent_rate, 2);
            $definitions[] = [
                'user' => $lead->assignedAgent,
                'commission_role' => 'sales-agent',
                'commission_type' => 'sales_agent',
                'rate_percentage' => $salesRate,
                'commission_amount' => round($basisAmount * ($salesRate / 100), 2),
                'meta' => [
                    'attribution_model' => $plan->sales_attribution_model,
                    'basis' => 'assigned_lead',
                    'gross_amount' => $grossAmount,
                ],
            ];
        }

        $marketingManager = $plan->defaultMarketingManager;
        if (
            $marketingManager
            && $marketingManager->hasRole('marketing-manager')
            && trim((string) ($lead?->source_campaign ?? '')) !== ''
        ) {
            $marketingRate = round((float) $plan->marketing_manager_rate, 2);
            $definitions[] = [
                'user' => $marketingManager,
                'commission_role' => 'marketing-manager',
                'commission_type' => 'marketing_manager',
                'rate_percentage' => $marketingRate,
                'commission_amount' => round($basisAmount * ($marketingRate / 100), 2),
                'meta' => [
                    'attribution_model' => $plan->marketing_attribution_model,
                    'source_campaign' => $lead?->source_campaign,
                    'gross_amount' => $grossAmount,
                ],
            ];
        }

        return array_values(array_filter($definitions, fn (array $definition) => ($definition['commission_amount'] ?? 0) > 0));
    }

    private function syncPlatformReferralPayment(Payment $payment, CommissionPlan $plan): void
    {
        $grossAmount = round((float) $payment->amount, 2);
        $basisAmount = round(max(0, $grossAmount - (float) ($payment->refund_amount ?? 0) - (float) ($payment->non_commissionable_amount ?? 0)), 2);

        if ($basisAmount <= 0 || ! $payment->referrer) {
            $this->reverseForPayment($payment, 'platform_referral_not_applicable');
            $payment->update([
                'commissionable_amount' => $basisAmount,
                'platform_share_amount' => $basisAmount,
                'affiliate_commission_amount' => 0,
                'tenant_net_income_amount' => 0,
            ]);

            return;
        }

        $rate = round((float) $plan->platform_referral_rate, 2);
        $commissionAmount = round($basisAmount * ($rate / 100), 2);
        $holdUntil = $plan->hold_days > 0 ? now()->addDays($plan->hold_days) : null;

        $entry = CommissionEntry::query()->firstOrNew([
            'payment_id' => $payment->id,
            'user_id' => $payment->referrer_user_id,
            'commission_type' => 'platform_referral',
        ]);

        $attributes = [
            'tenant_id' => $payment->tenant_id,
            'commission_plan_id' => $plan->id,
            'lead_id' => null,
            'commission_role' => 'affiliate',
            'gross_amount' => $grossAmount,
            'basis_amount' => $basisAmount,
            'rate_percentage' => $rate,
            'commission_amount' => $commissionAmount,
            'status' => $holdUntil ? CommissionEntry::STATUS_HELD : CommissionEntry::STATUS_PAYABLE,
            'hold_until' => $holdUntil,
            'notes' => 'Generated automatically from platform subscription payment #' . $payment->id . '.',
            'meta' => [
                'program' => 'platform_referral',
                'referral_code' => $payment->referral_code_snapshot,
                'source_platform' => $payment->source_platform,
                'source_campaign' => $payment->source_campaign,
            ],
        ];

        if ($entry->exists && in_array($entry->status, [CommissionEntry::STATUS_PAID, CommissionEntry::STATUS_APPROVED], true)) {
            unset($attributes['status'], $attributes['hold_until']);
        }

        $entry->fill($attributes);
        $entry->save();

        $payment->update([
            'commissionable_amount' => $basisAmount,
            'platform_share_amount' => $basisAmount,
            'affiliate_commission_amount' => $commissionAmount,
            'tenant_net_income_amount' => 0,
        ]);
    }

    private function resolveTemplateRoyaltyRate(FunnelTemplate $template): float
    {
        if ($template->royalty_rate !== null) {
            return round((float) $template->royalty_rate, 2);
        }

        return round((float) AppSetting::getValue('template_default_royalty_rate', '5'), 2);
    }

    /**
     * @param  array<int, array<string, mixed>>  $definitions
     */
    private function sumDefinitionAmount(array $definitions, string $commissionType): float
    {
        return round(collect($definitions)
            ->where('commission_type', $commissionType)
            ->sum(fn (array $definition) => (float) ($definition['commission_amount'] ?? 0)), 2);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function dispatchAutomationEvent(string $eventName, array $payload): void
    {
        try {
            app(N8nEmailOrchestrator::class)->dispatch($eventName, $payload);
        } catch (\Throwable) {
            // Best-effort dispatch only.
        }
    }
}
