<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\PayMongoCheckoutService;
use App\Services\SignupOnboardingService;
use Illuminate\Http\Request;

class TrialSubscriptionController extends Controller
{
    public function show(Request $request, SignupOnboardingService $onboarding)
    {
        $tenant = $request->user()->tenant;

        return view('billing.trial-upgrade', [
            'plans' => $onboarding->plans(),
            'tenant' => $tenant,
            'paymentCancelled' => $request->query('payment') === 'cancelled',
        ]);
    }

    public function startCheckout(
        Request $request,
        SignupOnboardingService $onboarding,
        PayMongoCheckoutService $payMongo,
    ) {
        $tenant = $request->user()->tenant;
        if (! $tenant) {
            return redirect()->route('dashboard.owner')->with('error', 'Your account is not linked to a workspace.');
        }

        if ($tenant->status === 'active') {
            return redirect()->route('dashboard.owner')->with('success', 'Your workspace is already active.');
        }

        $validated = $request->validate([
            'plan' => 'required|string',
        ]);

        try {
            $plan = $onboarding->findPlan($validated['plan']);
            if (! $payMongo->isConfigured()) {
                return back()->with('error', 'PayMongo is not configured yet. Please set your payment credentials first.');
            }

            $session = $onboarding->beginTrialUpgradeCheckout($tenant, $plan, $payMongo);
            if ($session === null) {
                return back()->with('error', 'We could not start your checkout right now. Please try again.');
            }

            return redirect()->away($session['checkout_url']);
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'We could not start your trial upgrade checkout right now. Please try again.');
        }
    }

    public function paymongoReturn(
        Request $request,
        Payment $payment,
        SignupOnboardingService $onboarding,
        PayMongoCheckoutService $payMongo,
    ) {
        $tenant = $request->user()->tenant;
        if (! $tenant || $payment->tenant_id !== $tenant->id) {
            abort(403);
        }

        try {
            if ($payment->status !== 'paid') {
                $sessionId = (string) $payment->provider_reference;
                if ($sessionId === '') {
                    return redirect()->route('trial.billing.show')->with('error', 'We could not verify your payment session yet.');
                }

                $data = $payMongo->retrieveCheckoutSession($sessionId);
                if ($data === null) {
                    return redirect()->route('trial.billing.show')->with('error', 'We could not verify your payment yet. Please try again in a moment.');
                }

                $paid = false;
                $method = null;
                $payments = data_get($data, 'attributes.payments');
                if (is_array($payments)) {
                    foreach ($payments as $item) {
                        if (! is_array($item)) {
                            continue;
                        }

                        if (data_get($item, 'attributes.status') === 'paid') {
                            $paid = true;
                            $sourceType = data_get($item, 'attributes.source.type');
                            $method = is_string($sourceType) ? $sourceType : null;
                            break;
                        }
                    }
                }

                if (! $paid) {
                    return redirect()->route('trial.billing.show')->with('error', 'Payment was not completed. You can try again anytime.');
                }

                $planCode = (string) data_get($data, 'attributes.metadata.plan_code', '');
                $plan = $onboarding->findPlan($planCode);
                $onboarding->activateTenantSubscriptionFromPayment($payment, $plan, $method);
            }

            return redirect()
                ->route('dashboard.owner')
                ->with('success', 'Payment confirmed successfully. Your workspace is now active.');
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('trial.billing.show')->with('error', 'Your payment was received, but we could not complete the upgrade automatically yet.');
        }
    }
}
