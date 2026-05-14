<?php

use App\Models\ExternalDeliveryLog;
use App\Models\Lead;
use App\Models\OnboardingAuditLog;
use App\Models\Plan;
use App\Models\Role;
use App\Models\SetupToken;
use App\Models\Tenant;
use App\Models\TenantPayoutAccount;
use App\Models\User;
use App\Services\CommissionService;
use App\Services\N8nEmailOrchestrator;
use App\Services\PlatformAdminProvisioningService;
use App\Services\SetupTokenService;
use App\Services\SignupOnboardingService;
use App\Services\TransactionalEmailService;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('platform-admin:setup-link {email? : Platform admin email. Omit to issue links for configured platform admins.} {--force-reset : Rotate the current password and require setup completion before the next login.}', function (PlatformAdminProvisioningService $provisioning) {
    $forceReset = (bool) $this->option('force-reset');
    $email = $this->argument('email');
    $provisioning->provisionConfiguredAccounts();

    if (is_string($email) && trim($email) !== '') {
        $user = $provisioning->findUserByEmail($email);

        if (! $user) {
            $this->error('No user found for that email.');

            return 1;
        }

        if (! $user->hasAnyRole(['super-admin', 'payout-admin'])) {
            $this->error('That user is not a configured platform admin.');

            return 1;
        }

        $link = $provisioning->issueSetupLink($user, $forceReset);

        $this->line($user->email . ' => ' . $link);

        return 0;
    }

    foreach ($provisioning->provisionConfiguredAccounts() as $user) {
        $link = $provisioning->issueSetupLink($user, $forceReset);
        $this->line($user->email . ' => ' . $link);
    }

    return 0;
})->purpose('Issue a one-time setup link for the platform super admin and payout admin accounts.');

Artisan::command('commissions:release-held', function (CommissionService $commissions) {
    $released = $commissions->releaseDueCommissions();
    $this->info('Released ' . $released . ' commission entr' . ($released === 1 ? 'y' : 'ies') . '.');

    return 0;
})->purpose('Release held commission entries whose hold date has passed.');

Artisan::command('email:smoke-test {recipient : Base inbox to receive the smoke-test emails.} {--keep-data : Keep the temporary database fixtures for inspection after the run.} {--single-recipient : Send every flow to the exact recipient without Gmail plus-address aliases.}', function (
    SignupOnboardingService $onboarding,
    SetupTokenService $setupTokens,
    TransactionalEmailService $emails,
    HttpKernel $kernel,
) {
    $baseRecipient = mb_strtolower(trim((string) $this->argument('recipient')));
    if (! filter_var($baseRecipient, FILTER_VALIDATE_EMAIL)) {
        $this->error('The recipient must be a valid email address.');

        return 1;
    }

    $singleRecipient = (bool) $this->option('single-recipient');
    [$baseLocal, $baseDomain] = array_pad(explode('@', $baseRecipient, 2), 2, '');
    $makeRecipient = function (string $tag) use ($singleRecipient, $baseLocal, $baseDomain, $baseRecipient): string {
        $safeTag = Str::slug($tag);

        return ! $singleRecipient && $safeTag !== '' && $baseDomain !== ''
            ? $baseLocal . '+' . $safeTag . '@' . $baseDomain
            : $baseRecipient;
    };
    $fixtureEmail = function (string $tag) use ($singleRecipient, $makeRecipient): string {
        if (! $singleRecipient) {
            return $makeRecipient($tag);
        }

        $safeTag = Str::slug($tag) ?: 'fixture';

        return 'email-smoke-' . $safeTag . '-' . Str::lower(Str::random(8)) . '@example.test';
    };
    $routeModelEmailToInbox = function ($model, callable $callback) use ($singleRecipient, $baseRecipient) {
        if (! $singleRecipient) {
            return $callback();
        }

        $originalEmail = (string) $model->email;
        if (strcasecmp($originalEmail, $baseRecipient) === 0) {
            return $callback();
        }

        $model->forceFill(['email' => $baseRecipient])->save();

        try {
            return $callback();
        } finally {
            $model->forceFill(['email' => $originalEmail])->save();
        }
    };

    $fetchLog = function (string $recipient, string $eventName): ?ExternalDeliveryLog {
        return ExternalDeliveryLog::query()
            ->where('recipient', $recipient)
            ->where('event_name', $eventName)
            ->latest('id')
            ->first();
    };

    $toRow = function (string $label, string $recipient, string $eventName, bool $ok, ?string $provider = null, ?string $error = null) use ($fetchLog): array {
        $log = $fetchLog($recipient, $eventName);

        return [
            'label' => $label,
            'event' => $eventName,
            'recipient' => $recipient,
            'ok' => $ok ? 'yes' : 'no',
            'provider' => $log?->provider ?? $provider ?? '-',
            'status' => $log?->status ?? ($ok ? 'sent' : 'failed'),
            'error' => $log?->error_message ?? $error ?? '-',
        ];
    };

    $callbackToken = trim((string) config('services.n8n.callback_bearer_token'));
    if ($callbackToken === '') {
        $callbackToken = trim((string) config('services.n8n.webhook_token'));
    }

    $postInternalJson = function (string $uri, array $payload) use ($kernel, $callbackToken): array {
        $request = Request::create(
            $uri,
            'POST',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
            ],
            json_encode($payload, JSON_UNESCAPED_SLASHES)
        );

        if ($callbackToken !== '') {
            $request->headers->set('Authorization', 'Bearer ' . $callbackToken);
        }

        $response = $kernel->handle($request);
        $decoded = json_decode((string) $response->getContent(), true);
        $kernel->terminate($request, $response);

        return [
            'status_code' => $response->getStatusCode(),
            'body' => is_array($decoded) ? $decoded : ['raw' => (string) $response->getContent()],
        ];
    };

    $results = [];
    $keepData = (bool) $this->option('keep-data');

    DB::beginTransaction();

    try {
        $timestamp = now()->format('YmdHis');
        $planCode = 'email-smoke-' . $timestamp;
        $plan = Plan::query()->create([
            'code' => $planCode,
            'name' => 'Email Smoke ' . $timestamp,
            'price' => 0,
            'period' => 'per month',
            'summary' => 'Temporary plan for email smoke testing.',
            'features' => ['Email smoke testing'],
            'spotlight' => null,
            'is_active' => true,
            'sort_order' => 999,
            'max_users' => null,
            'max_leads' => null,
            'max_funnels' => null,
            'max_templates' => null,
            'max_workflows' => null,
            'max_monthly_messages' => null,
            'automation_enabled' => true,
        ]);

        $roles = collect([
            'account-owner' => 'Account Owner',
            'marketing-manager' => 'Marketing Manager',
            'customer' => 'Customer',
        ])->mapWithKeys(fn (string $name, string $slug) => [
            $slug => Role::query()->firstOrCreate(['slug' => $slug], ['name' => $name]),
        ]);

        $tenant = Tenant::query()->create([
            'company_name' => 'Email Smoke Test Workspace',
            'subscription_plan' => $plan->code,
            'status' => 'active',
            'billing_status' => 'current',
            'subscription_activated_at' => now(),
            'subscription_renews_at' => now()->addDays(7),
            'trial_starts_at' => now()->subDays(1),
            'trial_ends_at' => now()->addDays(3),
        ]);

        $owner = User::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Email Smoke Owner',
            'email' => $fixtureEmail('owner'),
            'role' => 'account-owner',
            'status' => 'active',
            'activation_state' => 'active',
        ]);
        $owner->roles()->syncWithoutDetaching([$roles['account-owner']->id]);
        $owner->load('roles');

        $teamMember = User::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Email Smoke Team Member',
            'email' => $fixtureEmail('team-member'),
            'role' => 'marketing-manager',
            'status' => 'inactive',
            'activation_state' => 'invited',
        ]);
        $teamMember->roles()->syncWithoutDetaching([$roles['marketing-manager']->id]);

        $customer = User::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Email Smoke Customer',
            'email' => $fixtureEmail('customer-portal'),
            'role' => 'customer',
            'status' => 'inactive',
            'activation_state' => 'invited',
            'is_customer_portal_user' => true,
        ]);
        $customer->roles()->syncWithoutDetaching([$roles['customer']->id]);

        $lead = Lead::query()->create([
            'tenant_id' => $tenant->id,
            'assigned_to' => $owner->id,
            'name' => 'Email Smoke Lead',
            'email' => $fixtureEmail('lead'),
            'phone' => '09171234567',
            'source_campaign' => 'Email Smoke Test',
            'tags' => ['email-smoke'],
            'status' => 'new',
            'score' => 42,
        ]);

        $payoutAccount = TenantPayoutAccount::query()->create([
            'tenant_id' => $tenant->id,
            'destination_type' => 'gcash',
            'account_name' => 'Email Smoke Owner',
            'destination_value' => '09171234567',
            'masked_destination' => '0917****567',
            'provider_destination_reference' => 'smoke-ref-' . $timestamp,
            'is_verified' => true,
            'verified_at' => now(),
            'verification_status' => TenantPayoutAccount::STATUS_APPROVED,
            'reviewed_at' => now(),
            'reviewed_by' => $owner->id,
            'review_notes' => 'Smoke-test review note.',
            'is_default' => true,
        ]);

        $results[] = $toRow(
            'Account Owner Setup',
            $makeRecipient('owner'),
            'account_owner_paid_signup_created',
            $onboarding->queueSetupEmail($owner, 'account_owner_paid_signup_created', $setupTokens, ['recipient_email' => $makeRecipient('owner')])
        );
        $results[] = $toRow(
            'Setup Link Resend',
            $makeRecipient('owner'),
            'setup_link_expiring',
            $onboarding->queueSetupEmail($owner, 'setup_link_expiring', $setupTokens, ['recipient_email' => $makeRecipient('owner')])
        );
        $results[] = $toRow(
            'Team Member Invite',
            $makeRecipient('team-member'),
            'team_member_invited',
            $onboarding->queueSetupEmail($teamMember, 'team_member_invited', $setupTokens, ['recipient_email' => $makeRecipient('team-member')])
        );
        $results[] = $toRow(
            'Customer Portal Invite',
            $makeRecipient('customer-portal'),
            'customer_portal_invited',
            $onboarding->queueSetupEmail($customer, 'customer_portal_invited', $setupTokens, ['recipient_email' => $makeRecipient('customer-portal')])
        );

        $automationCases = [
            ['label' => 'Lead Captured', 'event' => 'lead_captured', 'payload' => ['tenant_id' => $tenant->id, 'lead_id' => $lead->id]],
            ['label' => 'Lead Stage Changed', 'event' => 'lead_stage_changed', 'payload' => ['tenant_id' => $tenant->id, 'lead_id' => $lead->id]],
            ['label' => 'Payment Failed', 'event' => 'payment_failed', 'payload' => ['tenant_id' => $tenant->id, 'recipient_email' => $makeRecipient('payment-failed'), 'invoice_id' => 'INV-' . $timestamp]],
            ['label' => 'Payment Recovered', 'event' => 'payment_recovered', 'payload' => ['tenant_id' => $tenant->id, 'recipient_email' => $makeRecipient('payment-recovered'), 'invoice_id' => 'INV-' . $timestamp]],
            ['label' => 'Funnel Opt-In Customer', 'event' => 'funnel_opt_in_submitted_customer', 'payload' => ['tenant_id' => $tenant->id, 'lead_id' => $lead->id]],
            ['label' => 'Funnel Checkout Started', 'event' => 'funnel_checkout_started_customer', 'payload' => ['tenant_id' => $tenant->id, 'recipient_email' => $makeRecipient('checkout-started')]],
            ['label' => 'Funnel Payment Paid', 'event' => 'funnel_payment_paid_customer', 'payload' => ['tenant_id' => $tenant->id, 'recipient_email' => $makeRecipient('checkout-paid')]],
            ['label' => 'Funnel Checkout Abandoned', 'event' => 'funnel_checkout_abandoned_customer', 'payload' => ['tenant_id' => $tenant->id, 'recipient_email' => $makeRecipient('checkout-abandoned')]],
            ['label' => 'Funnel Order Activity', 'event' => 'funnel_order_delivery_updated_customer', 'payload' => ['tenant_id' => $tenant->id, 'recipient_email' => $makeRecipient('order-activity')]],
            ['label' => 'Upsell Accepted', 'event' => 'funnel_upsell_accepted_customer', 'payload' => ['tenant_id' => $tenant->id, 'recipient_email' => $makeRecipient('upsell-accepted')]],
            ['label' => 'Upsell Declined', 'event' => 'funnel_upsell_declined_customer', 'payload' => ['tenant_id' => $tenant->id, 'recipient_email' => $makeRecipient('upsell-declined')]],
            ['label' => 'Downsell Accepted', 'event' => 'funnel_downsell_accepted_customer', 'payload' => ['tenant_id' => $tenant->id, 'recipient_email' => $makeRecipient('downsell-accepted')]],
            ['label' => 'Downsell Declined', 'event' => 'funnel_downsell_declined_customer', 'payload' => ['tenant_id' => $tenant->id, 'recipient_email' => $makeRecipient('downsell-declined')]],
            ['label' => 'Payout Pending Review', 'event' => 'payout_account_pending_review', 'payload' => ['tenant_id' => $tenant->id, 'recipient_email' => $makeRecipient('payout-pending'), 'invoice_id' => (string) $payoutAccount->id]],
            ['label' => 'Payout Approved', 'event' => 'payout_account_approved', 'payload' => ['tenant_id' => $tenant->id, 'recipient_email' => $makeRecipient('payout-approved'), 'invoice_id' => (string) $payoutAccount->id]],
            ['label' => 'Payout Rejected', 'event' => 'payout_account_rejected', 'payload' => ['tenant_id' => $tenant->id, 'recipient_email' => $makeRecipient('payout-rejected'), 'invoice_id' => (string) $payoutAccount->id]],
            ['label' => 'Settlement Recorded', 'event' => 'settlement_payout_recorded', 'payload' => ['tenant_id' => $tenant->id, 'recipient_email' => $makeRecipient('settlement'), 'amount' => 1499, 'payment_reference' => 'SETTLE-' . $timestamp, 'masked_destination' => $payoutAccount->masked_destination, 'payments_count' => 2, 'paid_at' => now()->toDateTimeString()]],
            ['label' => 'Renewal Reminder 7 Days', 'event' => 'subscription_deadline_reminder_7_days_owner', 'payload' => ['tenant_id' => $tenant->id, 'recipient_email' => $makeRecipient('renewal-7')]],
            ['label' => 'Renewal Reminder 3 Days', 'event' => 'subscription_deadline_reminder_3_days_owner', 'payload' => ['tenant_id' => $tenant->id, 'recipient_email' => $makeRecipient('renewal-3')]],
        ];

        foreach ($automationCases as $case) {
            $payload = array_merge($case['payload'], [
                'template' => $case['event'],
            ]);
            $response = isset($case['payload']['lead_id'])
                ? $routeModelEmailToInbox($lead, fn () => $postInternalJson('/api/n8n/send-email', $payload))
                : $postInternalJson('/api/n8n/send-email', $payload);
            $recipient = (string) ($response['body']['recipient'] ?? ($case['payload']['recipient_email'] ?? $lead->email));
            $ok = ($response['status_code'] ?? 500) < 400 && (bool) ($response['body']['ok'] ?? false);
            $results[] = $toRow($case['label'], $recipient, $case['event'], $ok, (string) ($response['body']['provider'] ?? ''), null);
        }

        $ownerDigestResponse = $postInternalJson('/api/n8n/send-owner-digest', [
            'date' => now()->toDateString(),
            'audience' => 'all_owners',
            'recipient_email' => $makeRecipient('owner'),
        ]);
        $results[] = $toRow(
            'Owner Digest',
            $makeRecipient('owner'),
            'owner_digest',
            ($ownerDigestResponse['status_code'] ?? 500) < 400 && ((int) ($ownerDigestResponse['body']['sent'] ?? 0) >= 1),
            null,
            null
        );

        $deliveryUpdateRecipient = $makeRecipient('delivery-update');
        $deliveryUpdate = $emails->sendDeliveryUpdateEmail($deliveryUpdateRecipient, [
            'funnel_name' => 'Email Smoke Delivery Funnel',
            'customer_name' => 'Email Smoke Customer',
            'delivery_status' => 'shipped',
            'tracking_value' => 'TRACK-' . $timestamp,
            'courier_name' => 'LBC',
            'order_items' => [
                ['name' => 'Starter Box', 'quantity' => 1, 'badge' => 'Bundle', 'price' => 'PHP 999.00'],
                ['name' => 'Upsell Add-On', 'quantity' => 2, 'badge' => 'Bonus', 'price' => 'PHP 250.00'],
            ],
            'order_quantity' => 3,
            'custom_message' => 'This is a live smoke test for the delivery update email path.',
        ], [
            'tenant_id' => $tenant->id,
            'lead_id' => $lead->id,
            'event_name' => 'funnel_order_delivery_updated_customer',
            'is_billable' => true,
            'idempotency_key' => 'email-smoke-delivery:' . $timestamp,
        ]);
        $results[] = $toRow(
            'Delivery Update Direct Path',
            $deliveryUpdateRecipient,
            'funnel_order_delivery_updated_customer',
            (bool) ($deliveryUpdate['sent'] ?? false),
            $deliveryUpdate['provider'] ?? null,
            $deliveryUpdate['error_message'] ?? null
        );

        $sentCount = collect($results)->where('ok', 'yes')->count();
        $this->table(
            ['Label', 'Event', 'Recipient', 'OK', 'Provider', 'Status', 'Error'],
            collect($results)->map(fn (array $row) => [
                $row['label'],
                $row['event'],
                $row['recipient'],
                $row['ok'],
                $row['provider'],
                $row['status'],
                $row['error'],
            ])->all()
        );

        $this->info('Completed ' . count($results) . ' email flow checks. Successful sends: ' . $sentCount . '.');
        $this->line('Base inbox: ' . $baseRecipient);
        $this->line($singleRecipient
            ? 'Single-recipient mode was used, so every flow was sent to the exact same inbox address.'
            : 'Gmail plus-addressing was used so each flow lands in the same inbox while staying unique per fixture.');

        if (! $keepData) {
            DB::rollBack();
            $this->comment('Temporary smoke-test records were rolled back after sending.');

            return 0;
        }

        DB::commit();
        $this->comment('Temporary smoke-test records were kept in the database because --keep-data was used.');

        return 0;
    } catch (\Throwable $e) {
        DB::rollBack();
        $this->error('Email smoke test failed: ' . $e->getMessage());

        return 1;
    }
})->purpose('Send all major email flows to one inbox using Gmail plus-addressing aliases for end-to-end smoke testing.');

Artisan::command('email:n8n-smoke-test {recipient : Base inbox to receive the n8n smoke-test emails.} {--keep-data : Keep the temporary database fixtures for inspection after the run.} {--single-recipient : Send every flow to the exact recipient without Gmail plus-address aliases.} {--wait-seconds=15 : Seconds to wait for each n8n callback or delivery log.}', function (
    SetupTokenService $setupTokens,
    N8nEmailOrchestrator $orchestrator,
) {
    $baseRecipient = mb_strtolower(trim((string) $this->argument('recipient')));
    if (! filter_var($baseRecipient, FILTER_VALIDATE_EMAIL)) {
        $this->error('The recipient must be a valid email address.');

        return 1;
    }

    $waitSeconds = max(5, (int) $this->option('wait-seconds'));
    $singleRecipient = (bool) $this->option('single-recipient');
    [$baseLocal, $baseDomain] = array_pad(explode('@', $baseRecipient, 2), 2, '');
    $makeRecipient = function (string $tag) use ($singleRecipient, $baseLocal, $baseDomain, $baseRecipient): string {
        $safeTag = Str::slug($tag);

        return ! $singleRecipient && $safeTag !== '' && $baseDomain !== ''
            ? $baseLocal . '+' . $safeTag . '@' . $baseDomain
            : $baseRecipient;
    };
    $fixtureEmail = function (string $tag) use ($singleRecipient, $makeRecipient): string {
        if (! $singleRecipient) {
            return $makeRecipient($tag);
        }

        $safeTag = Str::slug($tag) ?: 'fixture';

        return 'n8n-smoke-' . $safeTag . '-' . Str::lower(Str::random(8)) . '@example.test';
    };
    $routeModelEmailToInbox = function ($model, string $recipient, callable $callback) {
        $originalEmail = (string) $model->email;
        if (strcasecmp($originalEmail, $recipient) === 0) {
            return $callback();
        }

        $model->forceFill(['email' => $recipient])->save();

        try {
            return $callback();
        } finally {
            $model->forceFill(['email' => $originalEmail])->save();
        }
    };
    $waitForDeliveryLog = function (int $afterId, string $recipient, string $eventName, int $timeoutSeconds): ?ExternalDeliveryLog {
        $deadline = microtime(true) + $timeoutSeconds;

        do {
            $log = ExternalDeliveryLog::query()
                ->where('id', '>', $afterId)
                ->where('recipient', $recipient)
                ->where('event_name', $eventName)
                ->latest('id')
                ->first();

            if ($log) {
                return $log;
            }

            usleep(250000);
        } while (microtime(true) < $deadline);

        return null;
    };
    $waitForAudit = function (int $afterId, int $userId, string $eventName, int $timeoutSeconds): ?OnboardingAuditLog {
        $deadline = microtime(true) + $timeoutSeconds;

        do {
            $audit = OnboardingAuditLog::query()
                ->where('id', '>', $afterId)
                ->where('user_id', $userId)
                ->where('event_type', 'onboarding_email_callback')
                ->where('status', 'success')
                ->where('context->event_name', $eventName)
                ->latest('id')
                ->first();

            if ($audit) {
                return $audit;
            }

            usleep(250000);
        } while (microtime(true) < $deadline);

        return null;
    };

    $results = [];
    $keepData = (bool) $this->option('keep-data');
    $cleanup = function (?Tenant $tenant, ?Plan $plan, array $users, ?Lead $lead, ?TenantPayoutAccount $payoutAccount): void {
        $userIds = collect($users)->filter()->map(fn (User $user) => $user->id)->all();

        if ($tenant) {
            ExternalDeliveryLog::query()->where('tenant_id', $tenant->id)->delete();
        }
        if ($userIds !== []) {
            OnboardingAuditLog::query()->whereIn('user_id', $userIds)->delete();
            SetupToken::query()->whereIn('user_id', $userIds)->delete();
        }
        if ($payoutAccount) {
            TenantPayoutAccount::query()->whereKey($payoutAccount->id)->delete();
        }
        if ($lead) {
            Lead::query()->whereKey($lead->id)->delete();
        }
        foreach ($users as $user) {
            if (! $user) {
                continue;
            }

            $user->roles()->detach();
            User::query()->whereKey($user->id)->delete();
        }
        if ($tenant) {
            Tenant::query()->whereKey($tenant->id)->delete();
        }
        if ($plan) {
            Plan::query()->whereKey($plan->id)->delete();
        }
    };
    $plan = null;
    $tenant = null;
    $owner = null;
    $teamMember = null;
    $customer = null;
    $lead = null;
    $payoutAccount = null;

    try {
        $timestamp = now()->format('YmdHis');
        $plan = Plan::query()->create([
            'code' => 'n8n-email-smoke-' . $timestamp,
            'name' => 'n8n Email Smoke ' . $timestamp,
            'price' => 0,
            'period' => 'per month',
            'summary' => 'Temporary plan for n8n email smoke testing.',
            'features' => ['n8n Email smoke testing'],
            'spotlight' => null,
            'is_active' => true,
            'sort_order' => 998,
            'max_users' => null,
            'max_leads' => null,
            'max_funnels' => null,
            'max_templates' => null,
            'max_workflows' => null,
            'max_monthly_messages' => null,
            'automation_enabled' => true,
        ]);

        $roles = collect([
            'account-owner' => 'Account Owner',
            'marketing-manager' => 'Marketing Manager',
            'customer' => 'Customer',
        ])->mapWithKeys(fn (string $name, string $slug) => [
            $slug => Role::query()->firstOrCreate(['slug' => $slug], ['name' => $name]),
        ]);

        $tenant = Tenant::query()->create([
            'company_name' => 'n8n Email Smoke Test Workspace',
            'subscription_plan' => $plan->code,
            'status' => 'active',
            'billing_status' => 'current',
            'subscription_activated_at' => now(),
            'subscription_renews_at' => now()->addDays(7),
            'trial_starts_at' => now()->subDays(1),
            'trial_ends_at' => now()->addDays(3),
        ]);

        $owner = User::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => 'n8n Smoke Owner',
            'email' => $fixtureEmail('owner'),
            'role' => 'account-owner',
            'status' => 'active',
            'activation_state' => 'active',
        ]);
        $owner->roles()->syncWithoutDetaching([$roles['account-owner']->id]);

        $teamMember = User::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => 'n8n Smoke Team Member',
            'email' => $fixtureEmail('team-member'),
            'role' => 'marketing-manager',
            'status' => 'inactive',
            'activation_state' => 'invited',
        ]);
        $teamMember->roles()->syncWithoutDetaching([$roles['marketing-manager']->id]);

        $customer = User::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => 'n8n Smoke Customer',
            'email' => $fixtureEmail('customer-portal'),
            'role' => 'customer',
            'status' => 'inactive',
            'activation_state' => 'invited',
            'is_customer_portal_user' => true,
        ]);
        $customer->roles()->syncWithoutDetaching([$roles['customer']->id]);

        $lead = Lead::query()->create([
            'tenant_id' => $tenant->id,
            'assigned_to' => $owner->id,
            'name' => 'n8n Smoke Lead',
            'email' => $fixtureEmail('lead'),
            'phone' => '09171234567',
            'source_campaign' => 'n8n Smoke Test',
            'tags' => ['n8n-email-smoke'],
            'status' => 'proposal',
            'score' => 42,
        ]);

        $payoutAccount = TenantPayoutAccount::query()->create([
            'tenant_id' => $tenant->id,
            'destination_type' => 'gcash',
            'account_name' => 'n8n Smoke Owner',
            'destination_value' => '09171234567',
            'masked_destination' => '0917****567',
            'provider_destination_reference' => 'n8n-smoke-ref-' . $timestamp,
            'is_verified' => true,
            'verified_at' => now(),
            'verification_status' => TenantPayoutAccount::STATUS_APPROVED,
            'reviewed_at' => now(),
            'reviewed_by' => $owner->id,
            'review_notes' => 'n8n smoke-test review note.',
            'is_default' => true,
        ]);

        $purposeFromEvent = function (string $eventName): string {
            return match ($eventName) {
                'team_member_invited' => 'team_member_invite',
                'customer_portal_invited' => 'customer_portal_invite',
                default => 'account_owner_onboarding',
            };
        };
        $dispatchAuth = function (string $label, string $eventName, User $user, string $recipient) use ($tenant, $setupTokens, $orchestrator, $purposeFromEvent, $waitForAudit, $waitSeconds, &$results) {
            $tokenData = $setupTokens->createForUser($user, $purposeFromEvent($eventName));
            $beforeAuditId = (int) (OnboardingAuditLog::query()->max('id') ?? 0);
            $dispatched = $orchestrator->dispatch($eventName, [
                'tenant_id' => $tenant->id,
                'user_id' => $user->id,
                'event_name' => $eventName,
                'email' => $recipient,
                'name' => $user->name,
                'setup_url' => route('setup.show', [
                    'token' => $tokenData['token'],
                    'email' => $user->email,
                ]),
                'expires_at' => optional($tokenData['setupToken']->expires_at)->toIso8601String(),
                'login_url' => route('login'),
            ]);
            $audit = $dispatched ? $waitForAudit($beforeAuditId, $user->id, $eventName, $waitSeconds) : null;

            $results[] = [
                'label' => $label,
                'event' => $eventName,
                'recipient' => $recipient,
                'ok' => $audit ? 'yes' : 'no',
                'provider' => $audit ? 'n8n-auth' : '-',
                'status' => $audit ? 'sent' : ($dispatched ? 'timeout' : 'dispatch_failed'),
                'error' => $audit ? '-' : ($dispatched ? 'No onboarding callback received within timeout.' : 'n8n dispatch failed'),
            ];
        };
        $dispatchAutomation = function (string $label, string $dispatchEvent, string $expectedEventName, string $recipient, array $payload, ?Lead $leadModel = null) use ($orchestrator, $routeModelEmailToInbox, $waitForDeliveryLog, $waitSeconds, &$results) {
            $beforeLogId = (int) (ExternalDeliveryLog::query()->max('id') ?? 0);
            $runner = function () use ($orchestrator, $dispatchEvent, $payload) {
                return $orchestrator->dispatch($dispatchEvent, $payload);
            };
            $dispatched = $leadModel ? $routeModelEmailToInbox($leadModel, $recipient, $runner) : $runner();
            $log = $dispatched ? $waitForDeliveryLog($beforeLogId, $recipient, $expectedEventName, $waitSeconds) : null;

            $results[] = [
                'label' => $label,
                'event' => $expectedEventName,
                'recipient' => $recipient,
                'ok' => $log ? 'yes' : 'no',
                'provider' => $log?->provider ?? '-',
                'status' => $log?->status ?? ($dispatched ? 'timeout' : 'dispatch_failed'),
                'error' => $log?->error_message ?? ($dispatched ? 'No delivery log received within timeout.' : 'n8n dispatch failed'),
            ];
        };

        $dispatchAuth('Account Owner Setup via n8n', 'account_owner_paid_signup_created', $owner, $makeRecipient('owner'));
        $dispatchAuth('Setup Link Resend via n8n', 'setup_link_expiring', $owner, $makeRecipient('owner'));
        $dispatchAuth('Team Member Invite via n8n', 'team_member_invited', $teamMember, $makeRecipient('team-member'));
        $dispatchAuth('Customer Portal Invite via n8n', 'customer_portal_invited', $customer, $makeRecipient('customer-portal'));

        $automationCases = [
            ['label' => 'Lead Stage Changed via n8n', 'dispatch_event' => 'lead_stage_changed', 'expected_event' => 'lead_stage_changed', 'recipient' => $makeRecipient('lead'), 'payload' => ['tenant_id' => $tenant->id, 'lead_id' => $lead->id, 'to_stage' => 'qualified'], 'lead' => $lead],
            ['label' => 'Payment Recovered via n8n', 'dispatch_event' => 'payment_recovered', 'expected_event' => 'payment_recovered', 'recipient' => $makeRecipient('payment-recovered'), 'payload' => ['tenant_id' => $tenant->id, 'recipient_email' => $makeRecipient('payment-recovered'), 'invoice_id' => 'INV-RECOVER-' . $timestamp]],
            ['label' => 'Funnel Opt-In via n8n', 'dispatch_event' => 'funnel_opt_in_submitted', 'expected_event' => 'funnel_opt_in_submitted_customer', 'recipient' => $makeRecipient('lead'), 'payload' => ['tenant_id' => $tenant->id, 'lead_id' => $lead->id, 'recipient_email' => $makeRecipient('lead')], 'lead' => $lead],
            ['label' => 'Funnel Payment Paid via n8n', 'dispatch_event' => 'funnel_payment_paid', 'expected_event' => 'funnel_payment_paid_customer', 'recipient' => $makeRecipient('checkout-paid'), 'payload' => ['tenant_id' => $tenant->id, 'recipient_email' => $makeRecipient('checkout-paid'), 'payment_id' => 'FNL-PAID-' . $timestamp]],
            ['label' => 'Funnel Checkout Started via n8n', 'dispatch_event' => 'funnel_checkout_started', 'expected_event' => 'funnel_checkout_started_customer', 'recipient' => $makeRecipient('checkout-started'), 'payload' => ['tenant_id' => $tenant->id, 'recipient_email' => $makeRecipient('checkout-started')]],
            ['label' => 'Funnel Checkout Abandoned via n8n', 'dispatch_event' => 'funnel_checkout_abandoned', 'expected_event' => 'funnel_checkout_abandoned_customer', 'recipient' => $makeRecipient('checkout-abandoned'), 'payload' => ['tenant_id' => $tenant->id, 'recipient_email' => $makeRecipient('checkout-abandoned')]],
            ['label' => 'Funnel Order Delivery Updated via n8n', 'dispatch_event' => 'funnel_order_delivery_updated', 'expected_event' => 'funnel_order_delivery_updated_customer', 'recipient' => $makeRecipient('order-activity'), 'payload' => ['tenant_id' => $tenant->id, 'recipient_email' => $makeRecipient('order-activity'), 'delivery_status' => 'shipped', 'tracking_number' => 'N8N-TRACK-' . $timestamp]],
            ['label' => 'Upsell Accepted via n8n', 'dispatch_event' => 'funnel_upsell_accepted', 'expected_event' => 'funnel_upsell_accepted_customer', 'recipient' => $makeRecipient('upsell-accepted'), 'payload' => ['tenant_id' => $tenant->id, 'recipient_email' => $makeRecipient('upsell-accepted')]],
            ['label' => 'Upsell Declined via n8n', 'dispatch_event' => 'funnel_upsell_declined', 'expected_event' => 'funnel_upsell_declined_customer', 'recipient' => $makeRecipient('upsell-declined'), 'payload' => ['tenant_id' => $tenant->id, 'recipient_email' => $makeRecipient('upsell-declined')]],
            ['label' => 'Downsell Accepted via n8n', 'dispatch_event' => 'funnel_downsell_accepted', 'expected_event' => 'funnel_downsell_accepted_customer', 'recipient' => $makeRecipient('downsell-accepted'), 'payload' => ['tenant_id' => $tenant->id, 'recipient_email' => $makeRecipient('downsell-accepted')]],
            ['label' => 'Downsell Declined via n8n', 'dispatch_event' => 'funnel_downsell_declined', 'expected_event' => 'funnel_downsell_declined_customer', 'recipient' => $makeRecipient('downsell-declined'), 'payload' => ['tenant_id' => $tenant->id, 'recipient_email' => $makeRecipient('downsell-declined')]],
            ['label' => 'Payout Pending Review via n8n', 'dispatch_event' => 'payout_account_pending_review', 'expected_event' => 'payout_account_pending_review', 'recipient' => $makeRecipient('payout-pending'), 'payload' => ['tenant_id' => $tenant->id, 'recipient_email' => $makeRecipient('payout-pending'), 'invoice_id' => (string) $payoutAccount->id]],
            ['label' => 'Payout Approved via n8n', 'dispatch_event' => 'payout_account_approved', 'expected_event' => 'payout_account_approved', 'recipient' => $makeRecipient('payout-approved'), 'payload' => ['tenant_id' => $tenant->id, 'recipient_email' => $makeRecipient('payout-approved'), 'invoice_id' => (string) $payoutAccount->id]],
            ['label' => 'Payout Rejected via n8n', 'dispatch_event' => 'payout_account_rejected', 'expected_event' => 'payout_account_rejected', 'recipient' => $makeRecipient('payout-rejected'), 'payload' => ['tenant_id' => $tenant->id, 'recipient_email' => $makeRecipient('payout-rejected'), 'invoice_id' => (string) $payoutAccount->id]],
            ['label' => 'Settlement Recorded via n8n', 'dispatch_event' => 'settlement_payout_recorded', 'expected_event' => 'settlement_payout_recorded', 'recipient' => $makeRecipient('settlement'), 'payload' => ['tenant_id' => $tenant->id, 'recipient_email' => $makeRecipient('settlement'), 'account_owner_email' => $makeRecipient('settlement'), 'account_owner_id' => $owner->id, 'amount' => 1499, 'payment_reference' => 'SETTLE-' . $timestamp, 'masked_destination' => $payoutAccount->masked_destination, 'payments_count' => 2, 'paid_at' => now()->toDateTimeString()]],
            ['label' => 'Renewal Reminder 7 Days via n8n', 'dispatch_event' => 'subscription_deadline_reminder_7_days_owner', 'expected_event' => 'subscription_deadline_reminder_7_days_owner', 'recipient' => $makeRecipient('renewal-7'), 'payload' => ['tenant_id' => $tenant->id, 'account_owner_id' => $owner->id, 'account_owner_email' => $makeRecipient('renewal-7'), 'trial_ends_at' => now()->addDays(7)->toDateString()]],
            ['label' => 'Renewal Reminder 3 Days via n8n', 'dispatch_event' => 'subscription_deadline_reminder_3_days_owner', 'expected_event' => 'subscription_deadline_reminder_3_days_owner', 'recipient' => $makeRecipient('renewal-3'), 'payload' => ['tenant_id' => $tenant->id, 'account_owner_id' => $owner->id, 'account_owner_email' => $makeRecipient('renewal-3'), 'trial_ends_at' => now()->addDays(3)->toDateString()]],
        ];

        foreach ($automationCases as $case) {
            $dispatchAutomation(
                $case['label'],
                $case['dispatch_event'],
                $case['expected_event'],
                $case['recipient'],
                $case['payload'],
                $case['lead'] ?? null
            );
        }

        $sentCount = collect($results)->where('ok', 'yes')->count();
        $this->table(
            ['Label', 'Event', 'Recipient', 'OK', 'Provider', 'Status', 'Error'],
            collect($results)->map(fn (array $row) => [
                $row['label'],
                $row['event'],
                $row['recipient'],
                $row['ok'],
                $row['provider'],
                $row['status'],
                $row['error'],
            ])->all()
        );

        $this->info('Completed ' . count($results) . ' n8n email flow checks. Successful sends: ' . $sentCount . '.');
        $this->line('Base inbox: ' . $baseRecipient);
        $this->line('Wait timeout per flow: ' . $waitSeconds . ' seconds.');

        if (! $keepData) {
            $cleanup($tenant, $plan, [$owner, $teamMember, $customer], $lead, $payoutAccount);
            $this->comment('Temporary n8n smoke-test records were deleted after sending.');

            return 0;
        }

        $this->comment('Temporary n8n smoke-test records were kept in the database because --keep-data was used.');

        return 0;
    } catch (\Throwable $e) {
        if (! $keepData) {
            $cleanup($tenant, $plan, [$owner, $teamMember, $customer], $lead, $payoutAccount);
        }
        $this->error('n8n email smoke test failed: ' . $e->getMessage());

        return 1;
    }
})->purpose('Send the n8n-routed auth, automation, and finance email flows to one inbox using the live direct webhooks.');

Artisan::command('automation:run-subscription-deadline-reminders {--date= : Override the run date (YYYY-MM-DD).}', function (
    \App\Services\SubscriptionDeadlineReminderService $reminders,
) {
    $dateOption = trim((string) $this->option('date'));
    $runDate = $dateOption !== '' ? \Illuminate\Support\Carbon::parse($dateOption)->startOfDay() : now()->startOfDay();

    $result = $reminders->dispatch($runDate);

    $this->table(
        ['Run Date', 'Processed', 'Dispatched'],
        [[
            $result['run_date'],
            (string) $result['processed'],
            (string) $result['dispatched'],
        ]]
    );

    $this->info('Subscription reminder sweep completed through the live n8n workflow.');

    return 0;
})->purpose('Dispatch real subscription deadline reminders for active tenant subscriptions.');

Schedule::command('commissions:release-held')->hourly();
