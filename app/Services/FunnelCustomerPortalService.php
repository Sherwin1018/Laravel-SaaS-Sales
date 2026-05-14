<?php

namespace App\Services;

use App\Models\Role;
use App\Models\SetupToken;
use App\Models\Tenant;
use App\Models\User;
use App\Support\TenantPlanEnforcer;
use Illuminate\Support\Str;

class FunnelCustomerPortalService
{
    /**
     * @return array{user: User|null, setup_url: string|null, login_url: string, setup_required: bool, setup_expires_at: string|null}
     */
    public function provisionForPaidCustomer(
        Tenant $tenant,
        string $email,
        ?string $name = null,
        ?string $phone = null,
    ): array {
        $normalizedEmail = mb_strtolower(trim($email));
        if (! filter_var($normalizedEmail, FILTER_VALIDATE_EMAIL)) {
            return [
                'user' => null,
                'setup_url' => null,
                'login_url' => route('login'),
                'setup_required' => false,
                'setup_expires_at' => null,
            ];
        }

        $displayName = trim((string) $name) !== '' ? trim((string) $name) : 'Customer';
        $phone = trim((string) $phone) !== '' ? trim((string) $phone) : null;

        $user = User::query()
            ->where('tenant_id', $tenant->id)
            ->whereRaw('LOWER(email) = ?', [$normalizedEmail])
            ->first();

        if (! $user) {
            app(TenantPlanEnforcer::class)->ensureCanCreateCustomerPortalUser($tenant);

            $user = User::create([
                'tenant_id' => $tenant->id,
                'name' => $displayName,
                'email' => $normalizedEmail,
                'phone' => $phone,
                'password' => Str::random(40),
                'role' => 'customer',
                'status' => 'inactive',
                'activation_state' => 'invited',
                'invited_at' => now(),
                'is_customer_portal_user' => true,
            ]);
        } else {
            $updates = [];

            if (trim((string) $user->name) === '' && $displayName !== '') {
                $updates['name'] = $displayName;
            }
            if (trim((string) $user->phone) === '' && $phone !== null) {
                $updates['phone'] = $phone;
            }
            if (! $user->is_customer_portal_user) {
                $updates['is_customer_portal_user'] = true;
            }
            if (trim((string) $user->role) === '') {
                $updates['role'] = 'customer';
            }
            if ($updates !== []) {
                $user->forceFill($updates)->save();
            }
        }

        $customerRole = Role::query()->where('slug', 'customer')->first();
        if ($customerRole) {
            $user->roles()->syncWithoutDetaching([$customerRole->id]);
        }

        $loginUrl = route('login');
        $needsSetup = $user->status !== 'active' || ! $user->email_verified_at || $user->must_change_password;
        if (! $needsSetup) {
            return [
                'user' => $user->fresh(['roles']),
                'setup_url' => null,
                'login_url' => $loginUrl,
                'setup_required' => false,
                'setup_expires_at' => null,
            ];
        }

        SetupToken::query()
            ->where('user_id', $user->id)
            ->where('purpose', 'customer_portal_invite')
            ->whereNull('used_at')
            ->update(['used_at' => now()]);

        $tokenData = app(SetupTokenService::class)->createForUser($user, 'customer_portal_invite');
        $token = (string) ($tokenData['token'] ?? '');
        $expiresAt = $tokenData['setupToken']->expires_at ?? null;

        return [
            'user' => $user->fresh(['roles']),
            'setup_url' => $token !== '' ? route('setup.show', ['token' => $token, 'email' => $user->email]) : null,
            'login_url' => $loginUrl,
            'setup_required' => true,
            'setup_expires_at' => optional($expiresAt)->toIso8601String(),
        ];
    }
}
