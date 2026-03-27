<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\Payment;
use App\Models\Role;
use App\Models\SignupIntent;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use RuntimeException;

class SignupOnboardingService
{
    public const TRIAL_DAYS = 7;

    /**
     * @return array<int, array{code: string, name: string, price: float, period: string, summary: string, features: array<int, string>, spotlight: string|null}>
     */
    public function plans(): array
    {
        if (! Schema::hasTable('plans')) {
            return $this->defaultPlans();
        }

        $plans = Plan::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (Plan $plan) => $this->serializePlan($plan))
            ->all();

        return $plans !== [] ? $plans : $this->defaultPlans();
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
     * @param  array{full_name:string,company_name:string,email:string,password:string,plan:string}  $validated
     */
    public function upsertIntent(array $validated): SignupIntent
    {
        $plan = $this->findPlan($validated['plan']);

        return SignupIntent::updateOrCreate(
            ['email' => $validated['email']],
            [
                'full_name' => $validated['full_name'],
                'company_name' => $validated['company_name'],
                'password_encrypted' => Crypt::encryptString($validated['password']),
                'plan_code' => $plan['code'],
                'plan_name' => $plan['name'],
                'amount' => $plan['price'],
                'status' => 'pending',
                'provider' => null,
                'provider_reference' => null,
                'payment_method' => null,
                'paid_at' => null,
                'completed_at' => null,
            ]
        );
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
        ]);

        return $tenant->fresh();
    }

    public function activateTenantSubscriptionFromPayment(Payment $payment, array $plan, ?string $paymentMethod = null): Tenant
    {
        return DB::transaction(function () use ($payment, $plan, $paymentMethod) {
            $payment = Payment::query()->lockForUpdate()->findOrFail($payment->id);
            $tenant = Tenant::query()->lockForUpdate()->findOrFail($payment->tenant_id);

            if ($payment->status !== 'paid') {
                $payment->update([
                    'status' => 'paid',
                    'payment_method' => $paymentMethod ?? $payment->payment_method,
                    'payment_date' => now()->toDateString(),
                ]);
            }

            if ($tenant->status !== 'active' || $tenant->subscription_plan !== $plan['name']) {
                $tenant->update([
                    'subscription_plan' => $plan['name'],
                    'status' => 'active',
                ]);
            }

            return $tenant->fresh();
        });
    }

    public function finalize(SignupIntent $intent): User
    {
        return DB::transaction(function () use ($intent) {
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
                'status' => 'active',
            ]);

            $user = User::create([
                'tenant_id' => $tenant->id,
                'name' => $intent->full_name,
                'email' => $intent->email,
                'password' => Crypt::decryptString($intent->password_encrypted),
                'role' => 'account-owner',
                'status' => 'active',
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
                    'lead_id' => null,
                    'amount' => $intent->amount,
                    'status' => 'paid',
                    'payment_date' => Carbon::parse($intent->paid_at ?? now())->toDateString(),
                    'provider' => $intent->provider,
                    'provider_reference' => $intent->provider_reference,
                    'payment_method' => $intent->payment_method,
                ]);
            }

            $intent->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            return $user;
        });
    }
}
