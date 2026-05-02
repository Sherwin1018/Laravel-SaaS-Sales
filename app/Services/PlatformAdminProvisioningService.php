<?php

namespace App\Services;

use App\Models\Role;
use App\Models\SetupToken;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class PlatformAdminProvisioningService
{
    /**
     * @return array<int, User>
     */
    public function provisionConfiguredAccounts(): array
    {
        $users = [];

        foreach ($this->configuredAccounts() as $account) {
            $users[] = $this->provisionAccount($account);
        }

        return $users;
    }

    /**
     * @return array<int, array{role_slug:string, role_key:string, name:string, email:string}>
     */
    public function configuredAccounts(): array
    {
        $accounts = [];

        foreach ((array) config('platform_admins.accounts', []) as $account) {
            $email = mb_strtolower(trim((string) Arr::get($account, 'email', '')));
            $roleSlug = trim((string) Arr::get($account, 'role_slug', ''));
            $roleKey = trim((string) Arr::get($account, 'role_key', ''));
            $name = trim((string) Arr::get($account, 'name', ''));

            if ($email === '' || $roleSlug === '' || $roleKey === '' || $name === '') {
                continue;
            }

            $accounts[] = [
                'email' => $email,
                'name' => $name,
                'role_key' => $roleKey,
                'role_slug' => $roleSlug,
            ];
        }

        return $accounts;
    }

    /**
     * @param  array{role_slug:string, role_key:string, name:string, email:string}  $account
     */
    public function provisionAccount(array $account): User
    {
        return DB::transaction(function () use ($account) {
            $roleSlug = $account['role_slug'];
            $role = Role::query()->where('slug', $roleSlug)->first();

            if (! $role) {
                throw new RuntimeException("Role [{$roleSlug}] is not available.");
            }

            $user = $this->findUserByEmail($account['email']) ?? new User([
                'email' => $account['email'],
            ]);
            $isNew = ! $user->exists;

            if (trim((string) $user->name) === '') {
                $user->name = $account['name'];
            }

            $user->email = $account['email'];
            $user->tenant_id = null;
            $user->role = $account['role_key'];

            if ($isNew) {
                $user->status = 'active';
                $user->activation_state = 'pending_activation';
                $user->must_change_password = true;
                $user->password = Str::random(64);
            } else {
                if (! in_array((string) $user->activation_state, User::ACTIVATION_STATES, true)) {
                    $user->activation_state = 'pending_activation';
                }

                if (trim((string) $user->status) === '') {
                    $user->status = 'active';
                }

                if ($user->must_change_password === null) {
                    $user->must_change_password = $user->activation_state !== 'active';
                }
            }

            $user->save();
            $user->roles()->syncWithoutDetaching([$role->id]);

            return $user->fresh(['roles']);
        });
    }

    public function findUserByEmail(string $email): ?User
    {
        $normalizedEmail = mb_strtolower(trim($email));

        if ($normalizedEmail === '') {
            return null;
        }

        return User::query()
            ->with('roles')
            ->whereRaw('LOWER(email) = ?', [$normalizedEmail])
            ->first();
    }

    public function issueSetupLink(User $user, bool $forceReset = false): string
    {
        return DB::transaction(function () use ($user, $forceReset) {
            $user = User::query()->lockForUpdate()->findOrFail($user->id);

            if ($forceReset) {
                $user->forceFill([
                    'password' => Str::random(64),
                    'status' => 'active',
                    'activation_state' => 'pending_activation',
                    'must_change_password' => true,
                ])->save();
            }

            SetupToken::query()
                ->where('user_id', $user->id)
                ->where('purpose', 'platform_admin_setup')
                ->whereNull('used_at')
                ->update(['used_at' => now()]);

            $tokenData = app(SetupTokenService::class)->createForUser(
                $user,
                'platform_admin_setup',
                [
                    'force_reset' => $forceReset,
                    'issued_via' => 'console',
                ],
                (int) config('platform_admins.setup_link_expires_hours', 24)
            );

            return route('setup.show', [
                'token' => $tokenData['token'],
                'email' => $user->email,
            ]);
        });
    }
}
