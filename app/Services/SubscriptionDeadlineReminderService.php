<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class SubscriptionDeadlineReminderService
{
    /**
     * @return array{ok: bool, processed: int, dispatched: int, run_date: string}
     */
    public function dispatch(?Carbon $runDate = null): array
    {
        $runDate = ($runDate?->copy() ?? now()->copy())->startOfDay();

        $definitions = [
            [
                'days' => 7,
                'event_name' => 'subscription_deadline_reminder_7_days_owner',
            ],
            [
                'days' => 3,
                'event_name' => 'subscription_deadline_reminder_3_days_owner',
            ],
        ];

        $processed = 0;
        $dispatched = 0;

        foreach ($definitions as $definition) {
            $targetDate = $runDate->copy()->addDays((int) $definition['days'])->toDateString();

            $tenants = Tenant::query()
                ->where('status', 'active')
                ->where('billing_status', SubscriptionLifecycleService::BILLING_CURRENT)
                ->whereNotNull('subscription_renews_at')
                ->whereDate('subscription_renews_at', '=', $targetDate)
                ->get([
                    'id',
                    'company_name',
                    'subscription_plan',
                    'status',
                    'billing_status',
                    'subscription_renews_at',
                    'trial_ends_at',
                    'billing_grace_ends_at',
                    'subscription_activated_at',
                ]);

            foreach ($tenants as $tenant) {
                $processed++;
                $owner = $this->resolveAccountOwner((int) $tenant->id);
                if (! $owner?->email) {
                    continue;
                }

                $cacheKey = implode(':', [
                    'subscription_deadline_reminder',
                    $definition['event_name'],
                    'tenant',
                    $tenant->id,
                    $targetDate,
                ]);

                if (! Cache::add($cacheKey, true, now()->addHours(36))) {
                    continue;
                }

                $this->dispatchAutomationEvent((string) $definition['event_name'], [
                    'tenant_id' => $tenant->id,
                    'company_name' => $tenant->company_name,
                    'account_owner_id' => $owner->id,
                    'account_owner_name' => $owner->name,
                    'account_owner_email' => $owner->email,
                    'subscription_plan' => $tenant->subscription_plan,
                    'status' => $tenant->status,
                    'billing_status' => $tenant->billing_status,
                    'subscription_renews_at' => optional($tenant->subscription_renews_at)->toIso8601String(),
                    'trial_ends_at' => optional($tenant->trial_ends_at)->toIso8601String(),
                    'billing_grace_ends_at' => optional($tenant->billing_grace_ends_at)->toIso8601String(),
                    'subscription_activated_at' => optional($tenant->subscription_activated_at)->toIso8601String(),
                    'reminder_days' => (int) $definition['days'],
                    'deadline_kind' => 'subscription_renews_at',
                ]);

                $dispatched++;
            }
        }

        return [
            'ok' => true,
            'processed' => $processed,
            'dispatched' => $dispatched,
            'run_date' => $runDate->toDateString(),
        ];
    }

    private function resolveAccountOwner(int $tenantId): ?\App\Models\User
    {
        return \App\Models\User::query()
            ->where('tenant_id', $tenantId)
            ->where(function ($query) {
                $query->where('role', 'account-owner')
                    ->orWhereHas('roles', function ($roleQuery) {
                        $roleQuery->where('slug', 'account-owner');
                    });
            })
            ->orderBy('id')
            ->first();
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function dispatchAutomationEvent(string $eventName, array $payload): void
    {
        app(N8nEmailOrchestrator::class)->dispatch($eventName, $payload);
    }
}
