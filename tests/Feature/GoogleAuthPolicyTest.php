<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Tests\TestCase;

class GoogleAuthPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_google_callback_rejects_unknown_email(): void
    {
        $this->mockGoogleUser('unknown@example.com', 'gid-unknown');

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error', 'No existing account found for this Google email. Please use your registered account.');
    }

    public function test_google_callback_rejects_super_admin(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'status' => 'active',
            'email_verified_at' => now(),
            'activation_state' => 'active',
        ]);
        $this->attachRole($user, 'super-admin', 'Super Admin');

        $this->mockGoogleUser('admin@example.com', 'gid-admin');

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error', 'Super Admin accounts cannot use Google sign-in.');
    }

    public function test_google_callback_rejects_unverified_invited_user(): void
    {
        $tenant = Tenant::create([
            'company_name' => 'Policy Tenant',
            'subscription_plan' => 'Growth',
            'status' => 'active',
            'billing_status' => 'current',
        ]);

        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'email' => 'invitee@example.com',
            'status' => 'active',
            'email_verified_at' => null,
            'activation_state' => 'email_sent',
        ]);
        $this->attachRole($user, 'sales-agent', 'Sales Agent');

        $this->mockGoogleUser('invitee@example.com', 'gid-invitee');

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error', 'Please complete email verification and setup-password first.');
    }

    private function mockGoogleUser(string $email, string $id): void
    {
        config()->set('services.google.client_id', 'test-client');
        config()->set('services.google.client_secret', 'test-secret');
        config()->set('services.google.redirect', 'http://localhost/auth/google/callback');

        $googleUser = new class($email, $id) {
            public function __construct(private string $email, private string $id)
            {
            }

            public function getEmail(): string
            {
                return $this->email;
            }

            public function getId(): string
            {
                return $this->id;
            }
        };

        Socialite::shouldReceive('driver')
            ->with('google')
            ->andReturnSelf();
        Socialite::shouldReceive('user')
            ->andReturn($googleUser);
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

