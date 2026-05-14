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
     * @return array<int, array{
     *   code: string,
     *   name: string,
     *   price: float,
     *   period: string,
     *   summary: string,
     *   features: array<int, string>,
     *   spotlight: string|null,
     *   max_users: int|null,
     *   max_leads: int|null,
     *   max_funnels: int|null,
     *   max_templates: int|null,
     *   max_workflows: int|null,
     *   max_monthly_messages: int|null,
     *   automation_enabled: bool
     * }>
     */
    public function plans(bool $includeFreeTrial = false): array
    {
        try {
            if (! Schema::hasTable('plans')) {
                return $this->defaultPlans($includeFreeTrial);
            }

            $query = Plan::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('id');

            if (! $includeFreeTrial) {
                $query->where('code', '!=', 'free-trial');
            }

            $plans = $query->get()
                ->map(fn (Plan $plan) => $this->serializePlan($plan))
                ->all();

            return $plans !== [] ? $plans : $this->defaultPlans($includeFreeTrial);
        } catch (\Throwable) {
            return $this->defaultPlans($includeFreeTrial);
        }
    }

    /**
     * @return array{
     *   code: string,
     *   name: string,
     *   price: float,
     *   period: string,
     *   summary: string,
     *   features: array<int, string>,
     *   spotlight: string|null,
     *   max_users: int|null,
     *   max_leads: int|null,
     *   max_funnels: int|null,
     *   max_templates: int|null,
     *   max_workflows: int|null,
     *   max_monthly_messages: int|null,
     *   automation_enabled: bool
     * }
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
     * @return array{
     *   code: string,
     *   name: string,
     *   price: float,
     *   period: string,
     *   summary: string,
     *   features: array<int, string>,
     *   spotlight: string|null,
     *   max_users: int|null,
     *   max_leads: int|null,
     *   max_funnels: int|null,
     *   max_templates: int|null,
     *   max_workflows: int|null,
     *   max_monthly_messages: int|null,
     *   automation_enabled: bool
     * }
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
            'max_users' => $plan->max_users,
            'max_leads' => $plan->max_leads,
            'max_funnels' => $plan->max_funnels,
            'max_templates' => $plan->max_templates,
            'max_workflows' => $plan->max_workflows,
            'max_monthly_messages' => $plan->max_monthly_messages,
            'automation_enabled' => (bool) $plan->automation_enabled,
        ];
    }

    /**
     * @return array<int, array{
     *   code: string,
     *   name: string,
     *   price: float,
     *   period: string,
     *   summary: string,
     *   features: array<int, string>,
     *   spotlight: string|null,
     *   max_users: int|null,
     *   max_leads: int|null,
     *   max_funnels: int|null,
     *   max_templates: int|null,
     *   max_workflows: int|null,
     *   max_monthly_messages: int|null,
     *   automation_enabled: bool
     * }>
     */
    private function defaultPlans(bool $includeFreeTrial = false): array
    {
        $plans = [
            [
                'code' => 'starter',
                'name' => 'Starter',
                'price' => 1499.00,
                'period' => 'per month',
                'summary' => 'For teams launching their first lead capture and conversion funnels with essential built-in automations.',
                'features' => [
                    '1 workspace with Account Owner dashboard access',
                    'Welcome/setup email and payment confirmation included',
                    'One lead capture autoresponse and one abandoned checkout reminder',
                    'Basic funnel analytics, payment monitoring, and status notifications',
                    'Shared n8n email automations with limited workflow automation coverage',
                ],
                'spotlight' => 'Best for New Teams',
                'max_users' => 5,
                'max_leads' => 1000,
                'max_funnels' => 3,
                'max_templates' => 2,
                'max_workflows' => null,
                'max_monthly_messages' => 2000,
                'automation_enabled' => false,
            ],
            [
                'code' => 'growth',
                'name' => 'Growth',
                'price' => 3499.00,
                'period' => 'per month',
                'summary' => 'For growing businesses ready to unlock shared automation across lead, funnel, and billing workflows.',
                'features' => [
                    'Unlimited active funnels for one brand workspace',
                    'Shared n8n automation included for lead, funnel, billing, and reminder flows',
                    'Role-based dashboards and pipeline visibility',
                    'PayMongo-ready checkout journeys for your offers',
                ],
                'spotlight' => 'Recommended',
                'max_users' => 20,
                'max_leads' => 10000,
                'max_funnels' => null,
                'max_templates' => null,
                'max_workflows' => 10,
                'max_monthly_messages' => 30000,
                'automation_enabled' => true,
            ],
            [
                'code' => 'scale',
                'name' => 'Scale',
                'price' => 6999.00,
                'period' => 'per month',
                'summary' => 'For teams that want advanced automation coverage and higher-volume operations on the shared engine.',
                'features' => [
                    'Everything in Growth plus advanced shared automation coverage',
                    'Priority support for launch, billing, and operational workflows',
                    'Multi-team operational visibility for leaders',
                    'Built for aggressive campaign and revenue targets',
                ],
                'spotlight' => 'Best For Teams',
                'max_users' => null,
                'max_leads' => null,
                'max_funnels' => null,
                'max_templates' => null,
                'max_workflows' => null,
                'max_monthly_messages' => null,
                'automation_enabled' => true,
            ],
        ];

        if ($includeFreeTrial) {
            array_unshift($plans, [
                'code' => 'free-trial',
                'name' => 'Free Trial',
                'price' => 0.00,
                'period' => '7 days',
                'summary' => 'Account Owner dashboard access during trial period with limited team, leads, and funnel usage.',
                'features' => [
                    'Account Owner dashboard access during trial period',
                    'Limited team, leads, and funnel usage',
                    'Upgrade to Starter, Growth, or Scale anytime',
                    'No advanced shared automation during the trial period',
                ],
                'spotlight' => 'Trial',
                'max_users' => 3,
                'max_leads' => 300,
                'max_funnels' => 1,
                'max_templates' => 1,
                'max_workflows' => null,
                'max_monthly_messages' => 500,
                'automation_enabled' => false,
            ]);
        }

        return $plans;
    }

    /**
     * @param  array{full_name:string,company_name:string,email:string,mobile?:string,plan:string}  $validated
     */
    public function upsertIntent(array $validated): SignupIntent
    {
        $plan = $this->findPlan($validated['plan']);
        $attribution = is_array($validated['_attribution'] ?? null) ? $validated['_attribution'] : [];

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
            'source_platform' => $attribution['source_platform'] ?? null,
            'source_medium' => $attribution['source_medium'] ?? null,
            'source_campaign' => $attribution['source_campaign'] ?? null,
            'source_content' => $attribution['source_content'] ?? null,
            'referrer_user_id' => $attribution['referrer_user_id'] ?? null,
            'referral_code_snapshot' => $attribution['referral_code_snapshot'] ?? null,
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
    public function beginPayMongoCheckout(SignupIntent $intent, PayMongoCheckoutService $payMongo, array $options = []): ?array
    {
        $successParams = ['signupIntent' => $intent->id];
        if (($options['google_signup'] ?? false) === true) {
            $successParams['google_signup'] = 1;
        }

        $successUrl = URL::signedRoute('register.paymongo.return', [
            ...$successParams,
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
            array_filter([
                'signup_intent_id' => (string) $intent->id,
                'flow' => (string) ($options['flow'] ?? 'signup'),
                'plan_code' => $intent->plan_code,
                'google_id' => isset($options['google_id']) ? (string) $options['google_id'] : null,
            ], fn ($value) => $value !== null && $value !== ''),
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
            'subscription_activated_at' => now(),
            'subscription_renews_at' => $this->initialSubscriptionRenewalAt(),
            'trial_ends_at' => null,
        ]);

        return $tenant->fresh();
    }

    public function initialSubscriptionRenewalAt(?Carbon $anchor = null): Carbon
    {
        $base = ($anchor instanceof Carbon ? $anchor->copy() : now()->copy())->startOfSecond();
        $days = max(1, (int) config('services.billing.initial_signup_renewal_days', 30));

        return $base->addDays($days);
    }

    public function activateTenantSubscriptionFromPayment(Payment $payment, array $plan, ?string $paymentMethod = null): Tenant
    {
        return app(SubscriptionLifecycleService::class)->activateTenantSubscriptionFromPayment($payment, $plan, $paymentMethod);
    }

    public function finalize(SignupIntent $intent, array $options = []): User
    {
        $autoActivate = (bool) ($options['auto_activate'] ?? false);
        $googleId = isset($options['google_id']) ? trim((string) $options['google_id']) : '';

        [$user, $setupToken] = DB::transaction(function () use ($intent, $autoActivate, $googleId) {
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
                'subscription_renews_at' => $this->initialSubscriptionRenewalAt(),
            ]);

            $userAttributes = [
                'tenant_id' => $tenant->id,
                'name' => $intent->full_name,
                'email' => $intent->email,
                'phone' => $intent->mobile,
                'password' => Str::random(40),
                'role' => 'account-owner',
                'status' => 'inactive',
                'activation_state' => 'pending_activation',
            ];
            if ($autoActivate) {
                $userAttributes['status'] = 'active';
                $userAttributes['activation_state'] = 'active';
                $userAttributes['email_verified_at'] = now();
                $userAttributes['activation_completed_at'] = now();
                $userAttributes['must_change_password'] = false;
            }
            if ($googleId !== '') {
                $userAttributes['google_id'] = $googleId;
            }

            $user = User::create($userAttributes);

            $role = Role::query()->where('slug', 'account-owner')->first();
            if ($role) {
                $user->roles()->syncWithoutDetaching([$role->id]);
            }

            if ($intent->provider_reference) {
                $payment = Payment::firstOrCreate(
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
                        'source_platform' => $intent->source_platform,
                        'source_medium' => $intent->source_medium,
                        'source_campaign' => $intent->source_campaign,
                        'source_content' => $intent->source_content,
                        'referrer_user_id' => $intent->referrer_user_id,
                        'referral_code_snapshot' => $intent->referral_code_snapshot,
                    ]
                );
                app(CommissionService::class)->syncPayment($payment->fresh());
            } else {
                $payment = Payment::create([
                    'tenant_id' => $tenant->id,
                    'payment_type' => Payment::TYPE_PLATFORM_SUBSCRIPTION,
                    'lead_id' => null,
                    'amount' => $intent->amount,
                    'status' => 'paid',
                    'payment_date' => Carbon::parse($intent->paid_at ?? now())->toDateString(),
                    'provider' => $intent->provider,
                    'provider_reference' => $intent->provider_reference,
                    'payment_method' => $intent->payment_method,
                    'source_platform' => $intent->source_platform,
                    'source_medium' => $intent->source_medium,
                    'source_campaign' => $intent->source_campaign,
                    'source_content' => $intent->source_content,
                    'referrer_user_id' => $intent->referrer_user_id,
                    'referral_code_snapshot' => $intent->referral_code_snapshot,
                ]);
                app(CommissionService::class)->syncPayment($payment->fresh());
            }

            $intent->update([
                'email_delivery_status' => 'queued',
                'email_delivery_attempts' => 0,
                'email_last_attempt_at' => null,
                'email_last_error' => null,
                'completed_at' => null,
                'activated_at' => null,
            ]);
            $this->transitionIntentOrFail($intent, SignupIntent::STATE_ACCOUNT_CREATED_PENDING_ACTIVATION);

            if ($autoActivate) {
                if ($tenant->status === 'inactive') {
                    $tenant->update([
                        'status' => 'active',
                        'subscription_activated_at' => $tenant->subscription_activated_at ?? now(),
                        'subscription_renews_at' => $tenant->subscription_renews_at ?? $this->initialSubscriptionRenewalAt(),
                    ]);
                }
                $this->transitionIntentOrFail($intent, SignupIntent::STATE_EMAIL_SENT);
                $this->transitionIntentOrFail($intent, SignupIntent::STATE_EMAIL_VERIFIED);
                $this->transitionIntentOrFail($intent, SignupIntent::STATE_PASSWORD_SET);
                $this->transitionIntentOrFail($intent, SignupIntent::STATE_ACTIVE);
                $intent->update([
                    'status' => 'completed',
                    'activated_at' => now(),
                    'completed_at' => now(),
                ]);

                return [$user->fresh(['roles']), null];
            }

            $setupTokenData = app(SetupTokenService::class)->createForUser(
                $user,
                'account_owner_onboarding',
                ['signup_intent_id' => $intent->id]
            );

            return [$user->fresh(['roles']), $setupTokenData['token']];
        });

        if ($autoActivate) {
            app(N8nEmailOrchestrator::class)->dispatch('account_owner_google_paid_signup_created', [
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
                'setup_url' => route('login'),
                'expires_at' => now()->addHours(24)->toIso8601String(),
                'login_url' => route('login'),
                'google_signup' => true,
            ]);
        } elseif (is_string($setupToken) && $setupToken !== '') {
            $this->queueSetupEmail($user, 'account_owner_paid_signup_created', app(SetupTokenService::class), [
                'token' => $setupToken,
            ]);
        }

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

        $targetRecipient = trim((string) ($meta['recipient_email'] ?? $user->email));
        if ($targetRecipient === '') {
            $targetRecipient = $user->email;
        }

        if ($this->shouldDispatchSetupEmailViaN8n($eventName)) {
            $dispatched = app(N8nEmailOrchestrator::class)->dispatch($eventName, [
                'tenant_id' => $user->tenant_id,
                'user_id' => $user->id,
                'event_name' => $eventName,
                'email' => $targetRecipient,
                'recipient_email' => $targetRecipient,
                'name' => $user->name,
                'setup_url' => $setupUrl,
                'expires_at' => optional($expiresAt)->toIso8601String(),
                'login_url' => route('login'),
            ]);

            if ($dispatched) {
                return true;
            }
        }

        $subject = $this->setupEmailSubject($eventName);
        $body = $this->setupEmailBody($user, $setupUrl, $expiresAt, $eventName);
        $delivery = app(TransactionalEmailService::class)->sendPlainText($targetRecipient, $subject, $body, [
            'event_name' => $eventName,
            'tenant_id' => $user->tenant_id,
            'user_id' => $user->id,
            'requested_recipient_email' => $targetRecipient,
        ]);
        $sent = (bool) $delivery['sent'];

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
                'Onboarding email delivered through the transactional email service.',
                $user,
                $intent,
                [
                    'event_name' => $eventName,
                    'provider' => $delivery['provider'],
                ]
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
                    'email_last_error' => 'transactional email send failed',
                ]);
            }
            Log::warning('Setup email dispatch skipped/failed.', [
                'user_id' => $user->id,
                'event_name' => $eventName,
            ]);
            app(OnboardingAuditService::class)->record(
                'onboarding_email_failed',
                'failed',
                'Onboarding email delivery failed.',
                $user,
                $intent,
                ['event_name' => $eventName]
            );
        }

        return $sent;
    }

    private function shouldDispatchSetupEmailViaN8n(string $eventName): bool
    {
        return in_array($eventName, [
            'account_owner_paid_signup_created',
            'account_owner_google_paid_signup_created',
            'team_member_invited',
            'customer_portal_invited',
            'setup_link_expiring',
            'setup_link_expired',
        ], true);
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

    private function setupEmailSubject(string $eventName): string
    {
        return match ($eventName) {
            'team_member_invited' => 'You were invited to join Sales & Marketing Funnel System',
            'customer_portal_invited' => 'Your customer portal account is ready',
            default => 'Complete your Sales & Marketing Funnel System setup',
        };
    }

    private function setupEmailBody(User $user, string $setupUrl, mixed $expiresAt, string $eventName): string
    {
        $intro = match ($eventName) {
            'team_member_invited' => 'You were invited to join your team workspace.',
            'customer_portal_invited' => 'Your customer portal account is ready.',
            default => 'Your account has been created successfully.',
        };

        $expiresText = method_exists($expiresAt, 'format')
            ? $expiresAt->format('M j, Y g:i A')
            : 'soon';

        return implode("\n", [
            'Hi ' . $user->name . ',',
            '',
            $intro,
            'Role: ' . $user->role,
            'Complete your setup here: ' . $setupUrl,
            'Login: ' . route('login'),
            'This link expires on: ' . $expiresText,
            '',
            'If you did not expect this message, you can ignore it.',
        ]);
    }
}
