<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\Payment;
use App\Models\Role;
use App\Models\SetupToken;
use App\Models\SignupIntent;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use RuntimeException;

class SignupOnboardingService
{
    public const TRIAL_DAYS = 7;

    /**
     * @return array<int, array{code: string, name: string, price: float, period: string, summary: string, features: array<int, string>, spotlight: string|null}>
     */
    public function plans(): array
    {
        try {
            if (! Schema::hasTable('plans')) {
                return $this->defaultPlans();
            }

            $plans = Plan::query()
                ->where('is_active', true)
                ->where('code', '!=', 'free-trial')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get()
                ->map(fn (Plan $plan) => $this->serializePlan($plan))
                ->all();

            return $plans !== [] ? $plans : $this->defaultPlans();
        } catch (\Throwable) {
            return $this->defaultPlans();
        }
    }

    /**
     * @return array{code: string, name: string, price: float, period: string, summary: string, features: array<int, string>, spotlight: string|null}
     */
    public function findPlan(string $code): array
    {
        foreach ($this->plans() as $plan) {
            if ($plan['code'] === $code) {
                return $plan;
            }
        }

        throw new RuntimeException('Selected plan is invalid.');
    }

    /**
     * @return array{code: string, name: string, price: float, period: string, summary: string, features: array<int, string>, spotlight: string|null}
     */
    private function serializePlan(Plan $plan): array
    {
        return [
            'code' => $plan->code,
            'name' => $plan->name,
            'price' => (float) $plan->price,
            'period' => $plan->period,
            'summary' => $plan->summary,
            'features' => array_values(array_filter((array) $plan->features, fn ($feature) => is_string($feature) && trim($feature) !== '')),
            'spotlight' => $plan->spotlight,
        ];
    }

    /**
     * @return array<int, array{code: string, name: string, price: float, period: string, summary: string, features: array<int, string>, spotlight: string|null}>
     */
    private function defaultPlans(): array
    {
        return [
            [
                'code' => 'starter',
                'name' => 'Starter',
                'price' => 1499.00,
                'period' => 'per month',
                'summary' => 'For teams launching their first lead capture and conversion funnels.',
                'features' => [
                    '1 workspace with Account Owner dashboard access',
                    'Lead capture funnels and conversion tracking',
                    'Basic funnel analytics and payment monitoring',
                    'Email and landing-page-ready funnel journeys',
                ],
                'spotlight' => null,
            ],
            [
                'code' => 'growth',
                'name' => 'Growth',
                'price' => 3499.00,
                'period' => 'per month',
                'summary' => 'For growing businesses managing campaigns, leads, and sales handoff in one place.',
                'features' => [
                    'Unlimited active funnels for one brand workspace',
                    'Marketing, sales, and finance collaboration tools',
                    'Role-based dashboards and pipeline visibility',
                    'PayMongo-ready checkout journeys for your offers',
                ],
                'spotlight' => 'Most Popular',
            ],
            [
                'code' => 'scale',
                'name' => 'Scale',
                'price' => 6999.00,
                'period' => 'per month',
                'summary' => 'For teams that want advanced funnel execution with higher-volume operations.',
                'features' => [
                    'Everything in Growth plus enterprise-ready onboarding',
                    'Priority support for launch and billing workflows',
                    'Multi-team operational visibility for leaders',
                    'Built for aggressive campaign and revenue targets',
                ],
                'spotlight' => 'Best For Teams',
            ],
        ];
    }

    /**
     * @param  array{full_name:string,company_name:string,email:string,mobile?:string,plan:string}  $validated
     */
    public function upsertIntent(array $validated): SignupIntent
    {
        $plan = $this->findPlan($validated['plan']);

        $intent = SignupIntent::query()->where('email', $validated['email'])->first();
        if ($intent && in_array($intent->lifecycle_state, [
            SignupIntent::STATE_PAYMENT_PAID,
            SignupIntent::STATE_ACCOUNT_CREATED_PENDING_ACTIVATION,
            SignupIntent::STATE_EMAIL_SENT,
            SignupIntent::STATE_EMAIL_VERIFIED,
            SignupIntent::STATE_PASSWORD_SET,
            SignupIntent::STATE_ACTIVE,
        ], true)) {
            throw new RuntimeException('This signup has already moved past payment pending. Please continue account activation from your email.');
        }

        if (! $intent) {
            $intent = new SignupIntent([
                'email' => $validated['email'],
                'lifecycle_state' => SignupIntent::STATE_SIGNUP_INTENT_CREATED,
                'email_delivery_attempts' => 0,
            ]);
        }

        $intent->fill([
            'full_name' => $validated['full_name'],
            'company_name' => $validated['company_name'],
            'mobile' => (string) ($validated['mobile'] ?? ''),
            // Password is created via setup link after payment.
            'password_encrypted' => Crypt::encryptString(Str::random(40)),
            'plan_code' => $plan['code'],
            'plan_name' => $plan['name'],
            'amount' => $plan['price'],
            'status' => 'pending',
            'provider' => null,
            'provider_reference' => null,
            'payment_method' => null,
            'paid_at' => null,
            'email_sent_at' => null,
            'email_delivery_status' => null,
            'email_delivery_attempts' => 0,
            'email_last_attempt_at' => null,
            'email_last_error' => null,
            'completed_at' => null,
            'activated_at' => null,
        ])->save();

        if (! in_array($intent->lifecycle_state, SignupIntent::LIFECYCLE_STATES, true)) {
            $intent->update(['lifecycle_state' => SignupIntent::STATE_SIGNUP_INTENT_CREATED]);
        }

        if ($intent->lifecycle_state === SignupIntent::STATE_SIGNUP_INTENT_CREATED) {
            $this->transitionIntentOrFail($intent, SignupIntent::STATE_PAYMENT_PENDING);
        }

        return $intent->fresh();
    }

    /**
     * @param  array{full_name:string,company_name:string,email:string,password:string}  $validated
     */
    public function createTrialAccount(array $validated): User
    {
        return DB::transaction(function () use ($validated) {
            $existingUser = User::query()->where('email', $validated['email'])->first();
            if ($existingUser) {
                throw new RuntimeException('This email address is already registered.');
            }

            $trialStartsAt = now();
            $trialEndsAt = $trialStartsAt->copy()->addDays(self::TRIAL_DAYS);

            $tenant = Tenant::create([
                'company_name' => $validated['company_name'],
                'subscription_plan' => 'Free Trial',
                'status' => 'trial',
                'billing_status' => 'trial',
                'trial_starts_at' => $trialStartsAt,
                'trial_ends_at' => $trialEndsAt,
            ]);

            $user = User::create([
                'tenant_id' => $tenant->id,
                'name' => $validated['full_name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'role' => 'account-owner',
                'status' => 'active',
            ]);

            $role = Role::query()->where('slug', 'account-owner')->first();
            if ($role) {
                $user->roles()->syncWithoutDetaching([$role->id]);
            }

            return $user;
        });
    }

    /**
     * @return array{checkout_url: string, id: string}|null
     */
    public function beginPayMongoCheckout(SignupIntent $intent, PayMongoCheckoutService $payMongo): ?array
    {
        $successUrl = URL::signedRoute('register.paymongo.return', [
            'signupIntent' => $intent->id,
        ], now()->addHours(48));

        $cancelUrl = route('register', [
            'payment' => 'cancelled',
            'plan' => $intent->plan_code,
        ]);

        $session = $payMongo->createCheckoutSession(
            (int) round(((float) $intent->amount) * 100),
            $intent->plan_name.' Plan',
            trim($intent->company_name).' subscription for the Sales & Marketing Funnel System',
            $successUrl,
            $cancelUrl,
            [
                'signup_intent_id' => (string) $intent->id,
                'flow' => 'signup',
                'plan_code' => $intent->plan_code,
            ],
            [
                'name' => $intent->full_name,
                'email' => $intent->email,
            ],
        );

        if ($session !== null) {
            $intent->update([
                'provider' => 'paymongo',
                'provider_reference' => $session['id'],
            ]);
        }

        return $session;
    }

    public function markPaid(SignupIntent $intent, ?string $paymentMethod = null): SignupIntent
    {
        if ($intent->status === 'completed') {
            return $intent;
        }

        $intent->update([
            'status' => 'paid',
            'payment_method' => $paymentMethod ?? $intent->payment_method,
            'paid_at' => $intent->paid_at ?? now(),
        ]);
        $this->transitionIntentOrFail($intent, SignupIntent::STATE_PAYMENT_PAID);

        return $intent->fresh();
    }

    public function markFailed(SignupIntent $intent): void
    {
        if ($intent->status === 'completed') {
            return;
        }

        $intent->update(['status' => 'failed']);
    }

    /**
     * @return array{checkout_url: string, id: string}|null
     */
    public function beginTrialUpgradeCheckout(Tenant $tenant, array $plan, PayMongoCheckoutService $payMongo): ?array
    {
        $payment = Payment::create([
            'tenant_id' => $tenant->id,
            'payment_type' => Payment::TYPE_PLATFORM_SUBSCRIPTION,
            'lead_id' => null,
            'amount' => $plan['price'],
            'status' => 'pending',
            'payment_date' => now()->toDateString(),
            'provider' => 'paymongo',
            'provider_reference' => null,
            'payment_method' => null,
        ]);

        $successUrl = URL::signedRoute('trial.billing.return', [
            'payment' => $payment->id,
        ], now()->addHours(48));

        $cancelUrl = route('trial.billing.show', [
            'payment' => 'cancelled',
        ]);

        $session = $payMongo->createCheckoutSession(
            (int) round(((float) $plan['price']) * 100),
            $plan['name'].' Plan',
            trim($tenant->company_name).' subscription upgrade for the Sales & Marketing Funnel System',
            $successUrl,
            $cancelUrl,
            [
                'flow' => 'trial_upgrade',
                'tenant_id' => (string) $tenant->id,
                'payment_id' => (string) $payment->id,
                'plan_code' => $plan['code'],
                'plan_name' => $plan['name'],
            ],
            null,
        );

        if ($session === null) {
            $payment->delete();

            return null;
        }

        $payment->update([
            'provider_reference' => $session['id'],
        ]);

        return $session;
    }

    public function activateTenantSubscription(Tenant $tenant, array $plan): Tenant
    {
        $tenant->update([
            'subscription_plan' => $plan['name'],
            'status' => 'active',
            'billing_status' => 'current',
            'billing_grace_ends_at' => null,
            'last_payment_failed_at' => null,
            'subscription_activated_at' => $tenant->subscription_activated_at ?? now(),
            'trial_ends_at' => null,
        ]);

        return $tenant->fresh();
    }

    public function activateTenantSubscriptionFromPayment(Payment $payment, array $plan, ?string $paymentMethod = null): Tenant
    {
        return app(SubscriptionLifecycleService::class)->activateTenantSubscriptionFromPayment($payment, $plan, $paymentMethod);
    }

    public function finalize(SignupIntent $intent): User
    {
        [$user, $setupToken] = DB::transaction(function () use ($intent) {
            $intent = SignupIntent::query()->lockForUpdate()->findOrFail($intent->id);

            $existingUser = User::query()->where('email', $intent->email)->first();
            if ($intent->status === 'completed') {
                if (! $existingUser) {
                    throw new RuntimeException('Signup was completed but the account could not be found.');
                }

                return $existingUser;
            }

            if ($intent->status !== 'paid') {
                throw new RuntimeException('Signup payment is not completed yet.');
            }

            if ($existingUser) {
                throw new RuntimeException('This email address is already registered.');
            }

            $tenant = Tenant::create([
                'company_name' => $intent->company_name,
                'subscription_plan' => $intent->plan_name,
                'status' => 'inactive',
                'billing_status' => 'current',
                'subscription_activated_at' => now(),
            ]);

            $user = User::create([
                'tenant_id' => $tenant->id,
                'name' => $intent->full_name,
                'email' => $intent->email,
                'phone' => $intent->mobile,
                'password' => Str::random(40),
                'role' => 'account-owner',
                'status' => 'inactive',
                'activation_state' => 'pending_activation',
            ]);

            $role = Role::query()->where('slug', 'account-owner')->first();
            if ($role) {
                $user->roles()->syncWithoutDetaching([$role->id]);
            }

            if ($intent->provider_reference) {
                Payment::firstOrCreate(
                    [
                        'provider' => $intent->provider,
                        'provider_reference' => $intent->provider_reference,
                    ],
                    [
                        'tenant_id' => $tenant->id,
                        'payment_type' => Payment::TYPE_PLATFORM_SUBSCRIPTION,
                        'lead_id' => null,
                        'amount' => $intent->amount,
                        'status' => 'paid',
                        'payment_date' => Carbon::parse($intent->paid_at ?? now())->toDateString(),
                        'payment_method' => $intent->payment_method,
                    ]
                );
            } else {
                Payment::create([
                    'tenant_id' => $tenant->id,
                    'payment_type' => Payment::TYPE_PLATFORM_SUBSCRIPTION,
                    'lead_id' => null,
                    'amount' => $intent->amount,
                    'status' => 'paid',
                    'payment_date' => Carbon::parse($intent->paid_at ?? now())->toDateString(),
                    'provider' => $intent->provider,
                    'provider_reference' => $intent->provider_reference,
                    'payment_method' => $intent->payment_method,
                ]);
            }

            $setupTokenData = app(SetupTokenService::class)->createForUser(
                $user,
                'account_owner_onboarding',
                ['signup_intent_id' => $intent->id]
            );

            $intent->update([
                'email_delivery_status' => 'queued',
                'email_delivery_attempts' => 0,
                'email_last_attempt_at' => null,
                'email_last_error' => null,
                'completed_at' => null,
                'activated_at' => null,
            ]);
            $this->transitionIntentOrFail($intent, SignupIntent::STATE_ACCOUNT_CREATED_PENDING_ACTIVATION);

            return [$user->fresh(['roles']), $setupTokenData['token']];
        });

        $this->queueSetupEmail($user, 'account_owner_paid_signup_created', app(SetupTokenService::class), [
            'token' => $setupToken,
        ]);

        if ((bool) config('services.n8n.send_payment_success_event', false)) {
            app(N8nEmailOrchestrator::class)->dispatch('payment_successful', [
                'signup_intent_id' => $intent->id,
                'user_id' => $user->id,
                'tenant_id' => $user->tenant_id,
                'email' => $user->email,
                'name' => $user->name,
                'plan_code' => $intent->plan_code,
                'plan_name' => $intent->plan_name,
                'amount' => (float) $intent->amount,
                'payment_method' => $intent->payment_method,
                'paid_at' => optional($intent->paid_at ?? now())->toIso8601String(),
                // Kept for optional template compatibility when this event is enabled.
                'setup_url' => route('login'),
                'expires_at' => now()->addHours(24)->toIso8601String(),
                'login_url' => route('login'),
            ]);
        }

        return $user;
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    public function queueSetupEmail(
        User $user,
        string $eventName,
        SetupTokenService $setupTokenService,
        array $meta = [],
    ): bool {
        $purpose = $this->purposeFromEvent($eventName);

        if (! isset($meta['token']) || ! is_string($meta['token']) || $meta['token'] === '') {
            SetupToken::query()
                ->where('user_id', $user->id)
                ->where('purpose', $purpose)
                ->whereNull('used_at')
                ->update(['used_at' => now()]);

            $tokenData = $setupTokenService->createForUser($user, $purpose);
            $token = $tokenData['token'];
            $expiresAt = $tokenData['setupToken']->expires_at;
        } else {
            $token = $meta['token'];
            $tokenRecord = $setupTokenService->resolveByPlainToken($token);
            $expiresAt = $tokenRecord?->expires_at ?? now()->addHours(24);
        }

        $setupUrl = route('setup.show', [
            'token' => $token,
            'email' => $user->email,
        ]);

        $sent = app(N8nEmailOrchestrator::class)->dispatch($eventName, [
            'user_id' => $user->id,
            'tenant_id' => $user->tenant_id,
            'email' => $user->email,
            'name' => $user->name,
            'role' => $user->role,
            'setup_url' => $setupUrl,
            'expires_at' => $expiresAt?->toIso8601String(),
            'login_url' => route('login'),
        ]);

        if ($sent) {
            $user->update(['activation_state' => 'email_sent']);
            $intent = SignupIntent::query()
                ->where('email', $user->email)
                ->where('status', 'paid')
                ->latest('id')
                ->first();
            if ($intent) {
                $intent->update([
                    'email_sent_at' => now(),
                    'email_delivery_status' => 'sent',
                    'email_delivery_attempts' => (int) $intent->email_delivery_attempts + 1,
                    'email_last_attempt_at' => now(),
                    'email_last_error' => null,
                ]);
                if ($intent->lifecycle_state === SignupIntent::STATE_ACCOUNT_CREATED_PENDING_ACTIVATION) {
                    $this->transitionIntentOrFail($intent, SignupIntent::STATE_EMAIL_SENT);
                }
            }
            app(OnboardingAuditService::class)->record(
                'onboarding_email_sent',
                'success',
                'Onboarding email dispatched to n8n successfully.',
                $user,
                $intent,
                ['event_name' => $eventName]
            );
        } else {
            $intent = SignupIntent::query()
                ->where('email', $user->email)
                ->whereIn('status', ['pending', 'paid'])
                ->latest('id')
                ->first();
            if ($intent) {
                $intent->update([
                    'email_delivery_status' => 'failed',
                    'email_delivery_attempts' => (int) $intent->email_delivery_attempts + 1,
                    'email_last_attempt_at' => now(),
                    'email_last_error' => 'n8n dispatch failed',
                ]);
            }
            Log::warning('Setup email dispatch skipped/failed.', [
                'user_id' => $user->id,
                'event_name' => $eventName,
            ]);
            app(OnboardingAuditService::class)->record(
                'onboarding_email_failed',
                'failed',
                'Onboarding email dispatch to n8n failed.',
                $user,
                $intent,
                ['event_name' => $eventName]
            );
        }

        return $sent;
    }

    private function transitionIntentOrFail(SignupIntent $intent, string $nextState): void
    {
        $current = (string) $intent->lifecycle_state;
        if ($current === $nextState) {
            return;
        }

        if (! $intent->transitionTo($nextState)) {
            throw new RuntimeException("Invalid signup lifecycle transition: {$current} -> {$nextState}");
        }
    }

    private function purposeFromEvent(string $eventName): string
    {
        return match ($eventName) {
            'team_member_invited' => 'team_member_invite',
            'customer_portal_invited' => 'customer_portal_invite',
            default => 'account_owner_onboarding',
        };
    }
}
