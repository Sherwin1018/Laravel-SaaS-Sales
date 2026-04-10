<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\SetupToken;
use App\Models\SignupIntent;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OnboardingLifecycleE2ETest extends TestCase
{
    use RefreshDatabase;

    public function test_register_route_redirects_to_landing_for_non_trial_mode(): void
    {
        $response = $this->get(route('register', ['plan' => 'growth']));

        $response->assertRedirect(route('landing', ['plan' => 'growth']));
    }

    public function test_account_owner_onboarding_e2e_plan_payment_email_setup_login(): void
    {
        config()->set('services.paymongo.secret', null);
        config()->set('services.n8n.webhook_url', 'http://n8n.test/webhook/laravel-auth-events');
        Http::fake([
            'http://n8n.test/*' => Http::response(['ok' => true], 200),
        ]);

        $response = $this->post(route('register.post'), [
            'full_name' => 'Owner One',
            'company_name' => 'Owner Co',
            'email' => 'owner@example.com',
            'mobile' => '09123456789',
            'plan' => 'growth',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success', 'Payment Successful. Please check your email to activate your account.');

        $user = User::query()->where('email', 'owner@example.com')->firstOrFail();
        $this->assertSame('inactive', $user->status);

        $intent = SignupIntent::query()->where('email', 'owner@example.com')->latest('id')->firstOrFail();
        $this->assertSame(SignupIntent::STATE_EMAIL_SENT, $intent->lifecycle_state);
        $this->assertSame('sent', $intent->email_delivery_status);

        $setupToken = SetupToken::query()->where('user_id', $user->id)->latest('id')->firstOrFail();

        $show = $this->get(route('setup.show', ['token' => $this->resolvePlainToken($setupToken)]));
        $show->assertOk();

        $complete = $this->post(route('setup.complete', ['token' => $this->resolvePlainToken($setupToken)]), [
            'password' => 'StrongPass!123',
            'password_confirmation' => 'StrongPass!123',
        ]);

        $complete->assertRedirect(route('dashboard.owner'));

        $user->refresh();
        $intent->refresh();
        $this->assertSame('active', $user->status);
        $this->assertNotNull($user->email_verified_at);
        $this->assertSame(SignupIntent::STATE_ACTIVE, $intent->lifecycle_state);
    }

    public function test_team_invite_e2e_invite_email_setup_login(): void
    {
        config()->set('services.n8n.webhook_url', 'http://n8n.test/webhook/laravel-auth-events');
        Http::fake([
            'http://n8n.test/*' => Http::response(['ok' => true], 200),
        ]);

        [$owner] = $this->createAccountOwner();
        Role::query()->firstOrCreate(['slug' => 'sales-agent'], ['name' => 'Sales Agent']);

        $invite = $this->actingAs($owner)->post(route('users.store'), [
            'name' => 'Team User',
            'email' => 'team@example.com',
            'phone' => '09123456789',
            'role' => 'sales-agent',
        ]);
        $invite->assertRedirect(route('users.index'));
        $invite->assertSessionHas('success', 'Invitation sent successfully.');

        auth()->logout();

        $user = User::query()->where('email', 'team@example.com')->firstOrFail();
        $token = SetupToken::query()->where('user_id', $user->id)->latest('id')->firstOrFail();

        $complete = $this->post(route('setup.complete', ['token' => $this->resolvePlainToken($token)]), [
            'password' => 'StrongPass!123',
            'password_confirmation' => 'StrongPass!123',
        ]);

        $complete->assertRedirect(route('dashboard.sales'));
    }

    public function test_customer_optional_portal_invite_e2e(): void
    {
        config()->set('services.n8n.webhook_url', 'http://n8n.test/webhook/laravel-auth-events');
        Http::fake([
            'http://n8n.test/*' => Http::response(['ok' => true], 200),
        ]);

        [$owner] = $this->createAccountOwner();
        Role::query()->firstOrCreate(['slug' => 'customer'], ['name' => 'Customer']);

        $invite = $this->actingAs($owner)->post(route('users.store'), [
            'name' => 'Customer Portal',
            'email' => 'customer@example.com',
            'phone' => '09123456789',
            'role' => 'customer',
        ]);
        $invite->assertRedirect(route('users.index'));

        auth()->logout();

        $user = User::query()->where('email', 'customer@example.com')->firstOrFail();
        $this->assertTrue((bool) $user->is_customer_portal_user);

        $token = SetupToken::query()->where('user_id', $user->id)->latest('id')->firstOrFail();
        $complete = $this->post(route('setup.complete', ['token' => $this->resolvePlainToken($token)]), [
            'password' => 'StrongPass!123',
            'password_confirmation' => 'StrongPass!123',
        ]);

        $complete->assertRedirect(route('dashboard.customer'));
    }

    public function test_expired_token_and_resend_e2e(): void
    {
        config()->set('services.n8n.webhook_url', 'http://n8n.test/webhook/laravel-auth-events');
        Http::fake([
            'http://n8n.test/*' => Http::response(['ok' => true], 200),
        ]);

        [$owner] = $this->createAccountOwner('resend-owner@example.com');
        $owner->update([
            'status' => 'inactive',
            'activation_state' => 'email_sent',
            'email_verified_at' => null,
        ]);

        $expired = SetupToken::query()->create([
            'user_id' => $owner->id,
            'purpose' => 'account_owner_onboarding',
            'token_hash' => hash('sha256', 'expired-token'),
            'expires_at' => now()->subHour(),
        ]);

        $show = $this->get(route('setup.show', ['token' => 'expired-token', 'email' => $owner->email]));
        $show->assertOk();
        $show->assertSee('expired', false);

        $resend = $this->post(route('setup.resend'), [
            'email' => $owner->email,
        ]);
        $resend->assertSessionHas('success', 'A new activation email has been queued.');

        $this->assertGreaterThan(
            $expired->id,
            (int) SetupToken::query()->where('user_id', $owner->id)->latest('id')->value('id')
        );
    }

    public function test_lifecycle_transition_blocks_invalid_jump(): void
    {
        $intent = SignupIntent::query()->create([
            'full_name' => 'Jump User',
            'company_name' => 'Jump Co',
            'email' => 'jump@example.com',
            'password_encrypted' => 'x',
            'plan_code' => 'growth',
            'plan_name' => 'Growth',
            'amount' => 1,
            'status' => 'pending',
            'lifecycle_state' => SignupIntent::STATE_PAYMENT_PENDING,
        ]);

        $moved = $intent->transitionTo(SignupIntent::STATE_ACTIVE);
        $this->assertFalse($moved);
    }

    private function createAccountOwner(string $email = 'owner-team@example.com'): array
    {
        $tenant = Tenant::query()->create([
            'company_name' => 'Owner Team Co',
            'subscription_plan' => 'Growth',
            'status' => 'active',
            'billing_status' => 'current',
        ]);

        $owner = User::factory()->create([
            'tenant_id' => $tenant->id,
            'email' => $email,
            'status' => 'active',
            'activation_state' => 'active',
            'email_verified_at' => now(),
        ]);
        $this->attachRole($owner, 'account-owner', 'Account Owner');

        return [$owner, $tenant];
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

    private function resolvePlainToken(SetupToken $token): string
    {
        // In tests we can't recover plain token from hash; mint known token record by replacing hash for this run.
        // Use deterministic plain token per token id.
        $plain = 'plain-token-' . $token->id;
        $token->update(['token_hash' => hash('sha256', $plain)]);

        return $plain;
    }
}
