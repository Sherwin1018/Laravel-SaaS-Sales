<?php

namespace App\Support;

use App\Models\Lead;
use App\Models\Funnel;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Arr;

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
            'workflows' => $this->limitEntry($plan, self::LIMIT_WORKFLOWS, 0, 'automation workflows'),
            'messages' => $this->limitEntry($plan, self::LIMIT_MESSAGES, 0, 'outbound messages'),
            'automation_enabled' => $plan?->automation_enabled ?? true,
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
                'You have reached your %s limit for the %s plan. Upgrade your subscription to add another %s.',
                str_replace('max_', '', $attribute),
                $plan->name,
                $resourceLabel
            ));
        }
    }

    private function limitEntry(?Plan $plan, string $attribute, int $used, string $label): array
    {
        $limit = $plan?->{$attribute};

        return [
            'label' => $label,
            'used' => $used,
            'limit' => $limit,
            'is_unlimited' => $limit === null,
            'remaining' => $limit === null ? null : max(0, (int) $limit - $used),
        ];
    }
}
