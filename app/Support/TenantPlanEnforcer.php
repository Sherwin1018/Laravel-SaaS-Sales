<?php

namespace App\Support;

use App\Models\Lead;
use App\Models\Funnel;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use App\Services\DeliveryLogService;
use App\Services\PlanAutomationService;

class TenantPlanEnforcer
{
    public const LIMIT_USERS = 'max_users';
    public const LIMIT_LEADS = 'max_leads';
    public const LIMIT_FUNNELS = 'max_funnels';
    public const LIMIT_WORKFLOWS = 'max_workflows';
    public const LIMIT_MESSAGES = 'max_monthly_messages';

    public function resolvePlan(Tenant $tenant): ?Plan
    {
        return Plan::resolveForSubscription($tenant->subscription_plan);
    }

    public function usageSummary(Tenant $tenant): array
    {
        $plan = $this->resolvePlan($tenant);

        return [
            'plan' => $plan,
            'users' => $this->limitEntry($plan, self::LIMIT_USERS, User::where('tenant_id', $tenant->id)->count(), 'users'),
            'leads' => $this->limitEntry($plan, self::LIMIT_LEADS, Lead::where('tenant_id', $tenant->id)->count(), 'leads'),
            'funnels' => $this->limitEntry($plan, self::LIMIT_FUNNELS, Funnel::where('tenant_id', $tenant->id)->count(), 'funnels'),
            'workflows' => $this->limitEntry($plan, self::LIMIT_WORKFLOWS, $this->currentWorkflowUsage($tenant), 'automation workflows'),
            'messages' => $this->limitEntry($plan, self::LIMIT_MESSAGES, $this->currentMessageUsage($tenant), 'outbound messages'),
            'automation_enabled' => $plan?->automation_enabled ?? true,
            'automation_mode' => app(PlanAutomationService::class)->modeForPlan($plan),
            'has_overages' => collect([
                $this->limitEntry($plan, self::LIMIT_USERS, User::where('tenant_id', $tenant->id)->count(), 'users'),
                $this->limitEntry($plan, self::LIMIT_LEADS, Lead::where('tenant_id', $tenant->id)->count(), 'leads'),
                $this->limitEntry($plan, self::LIMIT_FUNNELS, Funnel::where('tenant_id', $tenant->id)->count(), 'funnels'),
                $this->limitEntry($plan, self::LIMIT_WORKFLOWS, $this->currentWorkflowUsage($tenant), 'automation workflows'),
                $this->limitEntry($plan, self::LIMIT_MESSAGES, $this->currentMessageUsage($tenant), 'outbound messages'),
            ])->contains(fn (array $entry) => (bool) ($entry['is_over_limit'] ?? false)),
        ];
    }

    public function ensureCanCreateUser(Tenant $tenant): void
    {
        $this->ensureWithinLimit($tenant, self::LIMIT_USERS, User::where('tenant_id', $tenant->id)->count(), 'team member');
    }

    public function ensureCanCreateLead(Tenant $tenant): void
    {
        $this->ensureWithinLimit($tenant, self::LIMIT_LEADS, Lead::where('tenant_id', $tenant->id)->count(), 'lead');
    }

    public function ensureCanCreateFunnel(Tenant $tenant): void
    {
        $this->ensureWithinLimit($tenant, self::LIMIT_FUNNELS, Funnel::where('tenant_id', $tenant->id)->count(), 'funnel');
    }

    public function ensureAutomationEnabled(Tenant $tenant): void
    {
        $plan = $this->resolvePlan($tenant);
        if ($plan && ! $plan->automation_enabled) {
            abort(403, 'Automation is not available on your current plan.');
        }
    }

    public function ensureCanUseWorkflow(Tenant $tenant): void
    {
        $plan = $this->resolvePlan($tenant);
        if (! $plan || ! $plan->automation_enabled) {
            return;
        }

        $this->ensureWithinLimit($tenant, self::LIMIT_WORKFLOWS, $this->currentWorkflowUsage($tenant), 'automation workflow');
    }

    public function ensureCanSendOutboundMessages(Tenant $tenant, int $messageCount = 1): void
    {
        $plan = $this->resolvePlan($tenant);
        if (! $plan) {
            return;
        }

        $limit = $plan->{self::LIMIT_MESSAGES};
        if ($limit === null) {
            return;
        }

        $currentUsage = $this->currentMessageUsage($tenant);
        if (($currentUsage + max(0, $messageCount)) > (int) $limit) {
            abort(422, sprintf(
                'You have reached your monthly outbound messages limit (%d/%d) for the %s plan. Upgrade your subscription to continue sending messages.',
                $currentUsage,
                (int) $limit,
                $plan->name
            ));
        }
    }

    private function ensureWithinLimit(Tenant $tenant, string $attribute, int $currentUsage, string $resourceLabel): void
    {
        $plan = $this->resolvePlan($tenant);
        if (! $plan) {
            return;
        }

        $limit = $plan->{$attribute};
        if ($limit === null) {
            return;
        }

        if ($currentUsage >= (int) $limit) {
            abort(422, sprintf(
                'You have reached your %s limit (%d/%d) for the %s plan. Upgrade your subscription to add another %s.',
                str_replace('max_', '', $attribute),
                $currentUsage,
                (int) $limit,
                $plan->name,
                $resourceLabel
            ));
        }
    }

    private function limitEntry(?Plan $plan, string $attribute, int $used, string $label): array
    {
        $limit = $plan?->{$attribute};
        $percentUsed = null;
        $status = 'unlimited';

        if ($limit !== null) {
            $limitValue = max(0, (int) $limit);
            $percentUsed = $limitValue > 0
                ? round(min(100, (($used / $limitValue) * 100)), 2)
                : null;

            if ($used > $limitValue) {
                $status = 'over_limit';
            } elseif ($used === $limitValue) {
                $status = 'at_limit';
            } else {
                $status = 'available';
            }
        }

        return [
            'label' => $label,
            'used' => $used,
            'limit' => $limit,
            'is_unlimited' => $limit === null,
            'remaining' => $limit === null ? null : max(0, (int) $limit - $used),
            'percent_used' => $percentUsed,
            'is_at_limit' => $limit !== null && $used === (int) $limit,
            'is_over_limit' => $limit !== null && $used > (int) $limit,
            'upgrade_required' => $limit !== null && $used >= (int) $limit,
            'status' => $status,
        ];
    }

    private function currentWorkflowUsage(Tenant $tenant): int
    {
        $plan = $this->resolvePlan($tenant);
        if (! $plan?->automation_enabled) {
            return 0;
        }

        return Funnel::query()
            ->where('tenant_id', $tenant->id)
            ->where('status', 'published')
            ->count();
    }

    private function currentMessageUsage(Tenant $tenant): int
    {
        return app(DeliveryLogService::class)->currentMonthBillableUsage($tenant);
    }
}
