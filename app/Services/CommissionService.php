<?php

namespace App\Services;

use App\Models\CommissionEntry;
use App\Models\CommissionPlan;
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
            ->with(['lead.assignedAgent.roles', 'lead', 'tenant'])
            ->where('tenant_id', $tenant->id)
            ->where('payment_type', Payment::TYPE_FUNNEL_CHECKOUT)
            ->where('status', 'paid')
            ->chunkById(100, function (Collection $payments) use ($plan) {
                foreach ($payments as $payment) {
                    $this->syncPayment($payment, $plan);
                }
            });

        Payment::query()
            ->where('tenant_id', $tenant->id)
            ->where('payment_type', Payment::TYPE_FUNNEL_CHECKOUT)
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
        if (! $payment->isFunnelSale()) {
            return;
        }

        if ($payment->status !== 'paid') {
            $this->reverseForPayment($payment, 'payment_not_paid');

            return;
        }

        $payment->loadMissing(['tenant', 'lead.assignedAgent.roles', 'commissionEntries']);
        $tenant = $payment->tenant;
        if (! $tenant) {
            return;
        }

        $plan ??= $this->resolvePlanForTenant($tenant);
        $grossAmount = round((float) $payment->amount, 2);
        $basisAmount = $this->calculateNetEligibleAmount($payment, $plan);
        if ($basisAmount <= 0) {
            $this->reverseForPayment($payment, 'basis_amount_below_zero');

            return;
        }

        $entriesToKeep = [];
        foreach ($this->commissionRecipients($payment, $plan, $basisAmount, $grossAmount) as $definition) {
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
            ->where('tenant_id', $user->tenant_id)
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
        $gatewayFee = $this->estimateGatewayFee($grossAmount, $plan);
        $platformFee = $this->estimatePlatformFee($grossAmount, $plan);

        return round(max(0, $grossAmount - $gatewayFee - $platformFee), 2);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function commissionRecipients(Payment $payment, CommissionPlan $plan, float $basisAmount, float $grossAmount): array
    {
        $definitions = [];
        $lead = $payment->lead;

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
