<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantSubscriptionAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_expired_trial_logs_out_non_owner_staff_on_login(): void
    {
        $tenant = Tenant::create([
            'company_name' => 'Trial Workspace',
            'status' => 'trial',
            'trial_starts_at' => now()->subDays(7),
            'trial_ends_at' => now()->subSecond(),
        ]);

        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'password' => 'password',
            'status' => 'active',
        ]);

        $this->attachRole($user, 'marketing-manager', 'Marketing Manager');

        $response = $this->post(route('login.post'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error', 'Your workspace trial has ended. Please contact your Account Owner to reactivate access.');
        $this->assertGuest();
    }

    public function test_expired_trial_redirects_non_owner_staff_from_protected_routes(): void
    {
        $tenant = Tenant::create([
            'company_name' => 'Trial Workspace',
            'status' => 'trial',
            'trial_starts_at' => now()->subDays(7),
            'trial_ends_at' => now()->subSecond(),
        ]);

        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'status' => 'active',
        ]);

        $this->attachRole($user, 'marketing-manager', 'Marketing Manager');

        $response = $this->actingAs($user)->get(route('dashboard.marketing'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error', 'Your workspace trial has ended. Please contact your Account Owner to reactivate access.');
        $this->assertGuest();
    }

    public function test_super_admin_is_not_blocked_by_tenant_subscription_middleware(): void
    {
        $user = User::factory()->create([
            'tenant_id' => null,
            'status' => 'active',
        ]);

        $this->attachRole($user, 'super-admin', 'Super Admin');

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertOk();
    }

    private function attachRole(User $user, string $slug, string $name): void
    {
        $role = Role::query()->firstOrCreate(
            ['slug' => $slug],
            ['name' => $name]
        );

        $user->roles()->attach($role);
        $user->load('roles');
    }
}
