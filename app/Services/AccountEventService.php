<?php

namespace App\Services;

use App\Models\User;
use App\Models\SignupIntent;
use Illuminate\Support\Facades\Log;

/**
 * Service for handling account-related events and notifications
 */
class AccountEventService
{
    public function __construct(
        private AutomationWebhookService $webhookService
    ) {}

    /**
     * Dispatch account owner paid signup created event
     */
    public function dispatchAccountOwnerPaidSignupCreated(
        string $email,
        string $name,
        string $setupUrl,
        string $expiresAt,
        ?int $userId = null,
        ?int $tenantId = null
    ): bool {
        $payload = $this->webhookService->buildAccountEventPayload(
            'account_owner_paid_signup_created',
            $email,
            $name,
            [
                'setup_url' => $setupUrl,
                'expires_at' => $expiresAt,
                'login_url' => config('app.url') . '/login',
                'user_id' => $userId,
                'tenant_id' => $tenantId ?? 0,
                'event_name' => 'account_owner_paid_signup_created'
            ]
        );

        return $this->webhookService->dispatchEvent('account_owner_paid_signup_created', $payload);
    }

    /**
     * Dispatch team member invited event
     */
    public function dispatchTeamMemberInvited(
        string $email,
        string $name,
        string $setupUrl,
        string $expiresAt,
        ?int $userId = null,
        ?int $tenantId = null
    ): bool {
        $payload = $this->webhookService->buildAccountEventPayload(
            'team_member_invited',
            $email,
            $name,
            [
                'setup_url' => $setupUrl,
                'expires_at' => $expiresAt,
                'login_url' => config('app.url') . '/login',
                'user_id' => $userId,
                'tenant_id' => $tenantId ?? 0,
                'event_name' => 'team_member_invited'
            ]
        );

        return $this->webhookService->dispatchEvent('team_member_invited', $payload);
    }

    /**
     * Dispatch customer portal invited event
     */
    public function dispatchCustomerPortalInvited(
        string $email,
        string $name,
        string $setupUrl,
        string $expiresAt,
        ?int $userId = null,
        ?int $tenantId = null
    ): bool {
        $payload = $this->webhookService->buildAccountEventPayload(
            'customer_portal_invited',
            $email,
            $name,
            [
                'setup_url' => $setupUrl,
                'expires_at' => $expiresAt,
                'login_url' => config('app.url') . '/login',
                'user_id' => $userId,
                'tenant_id' => $tenantId ?? 0,
                'event_name' => 'customer_portal_invited'
            ]
        );

        return $this->webhookService->dispatchEvent('customer_portal_invited', $payload);
    }

    /**
     * Dispatch setup link expiring event
     */
    public function dispatchSetupLinkExpiring(
        string $email,
        string $name,
        string $setupUrl,
        string $expiresAt,
        ?int $userId = null,
        ?int $tenantId = null
    ): bool {
        $payload = $this->webhookService->buildAccountEventPayload(
            'setup_link_expiring',
            $email,
            $name,
            [
                'setup_url' => $setupUrl,
                'expires_at' => $expiresAt,
                'login_url' => config('app.url') . '/login',
                'user_id' => $userId,
                'tenant_id' => $tenantId ?? 0,
                'event_name' => 'setup_link_expiring'
            ]
        );

        return $this->webhookService->dispatchEvent('setup_link_expiring', $payload);
    }

    /**
     * Dispatch setup link expired event
     */
    public function dispatchSetupLinkExpired(
        string $email,
        string $name,
        ?int $userId = null,
        ?int $tenantId = null
    ): bool {
        $payload = $this->webhookService->buildAccountEventPayload(
            'setup_link_expired',
            $email,
            $name,
            [
                'login_url' => config('app.url') . '/login',
                'user_id' => $userId,
                'tenant_id' => $tenantId ?? 0,
                'event_name' => 'setup_link_expired'
            ]
        );

        return $this->webhookService->dispatchEvent('setup_link_expired', $payload);
    }

    /**
     * Send account activation email for a user
     */
    public function sendAccountActivationEmail(User $user, string $setupToken, string $expiresAt): bool
    {
        $setupUrl = config('app.url') . "/setup/{$setupToken}";
        
        return $this->dispatchAccountOwnerPaidSignupCreated(
            $user->email,
            $user->name,
            $setupUrl,
            $expiresAt,
            $user->id,
            $user->tenant_id
        );
    }

    /**
     * Send team invitation email
     */
    public function sendTeamInvitationEmail(User $user, string $setupToken, string $expiresAt): bool
    {
        $setupUrl = config('app.url') . "/setup/{$setupToken}";
        
        return $this->dispatchTeamMemberInvited(
            $user->email,
            $user->name,
            $setupUrl,
            $expiresAt,
            $user->id,
            $user->tenant_id
        );
    }

    /**
     * Send customer portal invitation email
     */
    public function sendCustomerPortalInvitationEmail(SignupIntent $signupIntent, string $setupToken, string $expiresAt): bool
    {
        $setupUrl = config('app.url') . "/setup/{$setupToken}";
        
        return $this->dispatchCustomerPortalInvited(
            $signupIntent->email,
            $signupIntent->name ?? 'Customer',
            $setupUrl,
            $expiresAt,
            null,
            $signupIntent->tenant_id
        );
    }

    /**
     * Send setup link expiring reminder
     */
    public function sendSetupLinkExpiringReminder(User $user, string $setupToken, string $expiresAt): bool
    {
        $setupUrl = config('app.url') . "/setup/{$setupToken}";
        
        return $this->dispatchSetupLinkExpiring(
            $user->email,
            $user->name,
            $setupUrl,
            $expiresAt,
            $user->id,
            $user->tenant_id
        );
    }

    /**
     * Send setup link expired notification
     */
    public function sendSetupLinkExpiredNotification(User $user): bool
    {
        return $this->dispatchSetupLinkExpired(
            $user->email,
            $user->name,
            $user->id,
            $user->tenant_id
        );
    }
}
