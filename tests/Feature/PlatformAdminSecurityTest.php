<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PlatformAdminSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_seeder_creates_platform_admins_without_known_repo_passwords(): void
    {
        $this->seed(RoleSeeder::class);
        $this->seed(UserSeeder::class);

        $superAdmin = User::query()->where('email', 'superadmin@gmail.com')->firstOrFail();
        $payoutAdmin = User::query()->where('email', 'platform.finance.admin@gmail.com')->firstOrFail();

        $this->assertSame('super_admin', $superAdmin->role);
        $this->assertSame('payout_admin', $payoutAdmin->role);
        $this->assertSame('pending_activation', $superAdmin->activation_state);
        $this->assertSame('pending_activation', $payoutAdmin->activation_state);
        $this->assertTrue($superAdmin->must_change_password);
        $this->assertTrue($payoutAdmin->must_change_password);
        $this->assertFalse(Hash::check('password123', $superAdmin->password));
        $this->assertFalse(Hash::check('PayoutAdmin#2026', $payoutAdmin->password));
        $this->assertTrue($superAdmin->hasRole('super-admin'));
        $this->assertTrue($payoutAdmin->hasRole('payout-admin'));
    }

    public function test_user_seeder_preserves_existing_platform_admin_credentials_and_state(): void
    {
        $this->seed(RoleSeeder::class);

        $superAdmin = User::factory()->create([
            'email' => 'superadmin@gmail.com',
            'name' => 'Existing Super Admin',
            'password' => 'KeepThisPassword!123',
            'tenant_id' => null,
            'role' => 'super_admin',
            'status' => 'active',
            'activation_state' => 'active',
            'must_change_password' => false,
        ]);
        $payoutAdmin = User::factory()->create([
            'email' => 'platform.finance.admin@gmail.com',
            'name' => 'Existing Payout Admin',
            'password' => 'AnotherSafePass!123',
            'tenant_id' => null,
            'role' => 'payout_admin',
            'status' => 'active',
            'activation_state' => 'active',
            'must_change_password' => false,
        ]);

        $this->attachRole($superAdmin, 'super-admin', 'Super Admin');
        $this->attachRole($payoutAdmin, 'payout-admin', 'Platform Finance Admin');

        $this->seed(UserSeeder::class);

        $superAdmin->refresh();
        $payoutAdmin->refresh();

        $this->assertSame('Existing Super Admin', $superAdmin->name);
        $this->assertSame('Existing Payout Admin', $payoutAdmin->name);
        $this->assertSame('active', $superAdmin->activation_state);
        $this->assertSame('active', $payoutAdmin->activation_state);
        $this->assertFalse($superAdmin->must_change_password);
        $this->assertFalse($payoutAdmin->must_change_password);
        $this->assertTrue(Hash::check('KeepThisPassword!123', $superAdmin->password));
        $this->assertTrue(Hash::check('AnotherSafePass!123', $payoutAdmin->password));
    }

    public function test_super_admin_with_pending_activation_cannot_log_in(): void
    {
        $this->seed(RoleSeeder::class);

        $user = User::factory()->create([
            'email' => 'pending-super@example.com',
            'password' => 'SetupRequired!123',
            'tenant_id' => null,
            'role' => 'super_admin',
            'status' => 'active',
            'activation_state' => 'pending_activation',
            'must_change_password' => true,
        ]);

        $this->attachRole($user, 'super-admin', 'Super Admin');

        $response = $this->post(route('login.post'), [
            'email' => 'pending-super@example.com',
            'password' => 'SetupRequired!123',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error', 'Please verify your email and complete password setup before continuing.');
        $this->assertGuest();
    }

    public function test_platform_admin_setup_link_command_issues_link_without_rotating_active_account(): void
    {
        $this->seed(RoleSeeder::class);

        $user = User::factory()->create([
            'email' => 'platform.finance.admin@gmail.com',
            'password' => 'KeepCurrentPassword!123',
            'tenant_id' => null,
            'role' => 'payout_admin',
            'status' => 'active',
            'activation_state' => 'active',
            'must_change_password' => false,
        ]);

        $this->attachRole($user, 'payout-admin', 'Platform Finance Admin');

        $this->artisan('platform-admin:setup-link platform.finance.admin@gmail.com')
            ->expectsOutputToContain('platform.finance.admin@gmail.com => ')
            ->expectsOutputToContain('/setup/')
            ->assertExitCode(0);

        $user->refresh();

        $this->assertTrue(Hash::check('KeepCurrentPassword!123', $user->password));
        $this->assertSame('active', $user->activation_state);
        $this->assertFalse($user->must_change_password);
        $this->assertDatabaseHas('setup_tokens', [
            'user_id' => $user->id,
            'purpose' => 'platform_admin_setup',
        ]);
    }

    private function attachRole(User $user, string $slug, string $name): void
    {
        $role = Role::query()->firstOrCreate(
            ['slug' => $slug],
            ['name' => $name]
        );

        $user->roles()->syncWithoutDetaching([$role->id]);
        $user->load('roles');
    }
}
