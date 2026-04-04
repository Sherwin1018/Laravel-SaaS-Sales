<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\SignupIntent;
use App\Models\User;
use App\Services\PayMongoCheckoutService;
use App\Services\SignupOnboardingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PublicOnboardingController extends Controller
{
    public function landing(SignupOnboardingService $onboarding)
    {
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user());
        }

        $landingHeroVideoPath = AppSetting::getValue('landing_hero_video_path');
        $landingHeroVideoWidth = (int) (AppSetting::getValue('landing_hero_video_width', '1280') ?? 1280);
        $landingHeroVideoHeight = (int) (AppSetting::getValue('landing_hero_video_height', '720') ?? 720);
        $landingHeroVideoUrl = null;
        if (is_string($landingHeroVideoPath) && $landingHeroVideoPath !== '' && Storage::disk('public')->exists($landingHeroVideoPath)) {
            $landingHeroVideoUrl = Storage::disk('public')->url($landingHeroVideoPath);
        }

        return view('marketing.landing', [
            'plans' => $onboarding->plans(),
            'landingHeroVideoUrl' => $landingHeroVideoUrl,
            'landingHeroVideoWidth' => max(1, $landingHeroVideoWidth),
            'landingHeroVideoHeight' => max(1, $landingHeroVideoHeight),
        ]);
    }

    public function showRegister(Request $request, SignupOnboardingService $onboarding)
    {
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user());
        }

        $trialMode = $request->boolean('trial');

        if (! $trialMode) {
            return redirect()
                ->route('landing', ['plan' => (string) $request->query('plan', 'growth')])
                ->with('open_onboarding_modal', true);
        }

        return view('auth.register', [
            'plans' => $onboarding->plans(),
            'selectedPlan' => (string) $request->query('plan', 'growth'),
            'paymentCancelled' => $request->query('payment') === 'cancelled',
            'trialMode' => $trialMode,
        ]);
    }

    public function startRegistrationCheckout(
        Request $request,
        SignupOnboardingService $onboarding,
        PayMongoCheckoutService $payMongo,
    ) {
        $trialMode = $request->boolean('trial_mode');

        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'mobile' => $trialMode ? 'nullable|string|max:32' : ['required', 'regex:/^09\d{9}$/'],
            'password' => $trialMode ? [
                'required',
                'string',
                'min:12',
                'max:64',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[^A-Za-z0-9]/',
                'confirmed',
            ] : 'nullable|string',
            'plan' => $trialMode ? 'nullable|string' : 'required|string',
            'trial_mode' => 'nullable|boolean',
        ], [
            'password.regex' => 'Password must contain uppercase, lowercase, number, and a special character.',
            'mobile.regex' => 'Mobile number must be a valid PH format (09XXXXXXXXX).',
        ]);

        try {
            if ($trialMode) {
                $user = $onboarding->createTrialAccount($validated);

                Auth::login($user, true);
                $request->session()->regenerate();

                return redirect()
                    ->route('dashboard.owner')
                    ->with('success', 'Your 7-day free trial is now active. Welcome to your Account Owner dashboard.');
            }

            $intent = $onboarding->upsertIntent($validated);

            if ($payMongo->isConfigured()) {
                $session = $onboarding->beginPayMongoCheckout($intent, $payMongo);
                if ($session === null) {
                    return back()->withInput()->with('error', 'Unable to start PayMongo checkout. Please verify your payment settings and try again.');
                }

                return redirect()->away($session['checkout_url']);
            }

            $intent = $onboarding->markPaid($intent, 'manual');
            $onboarding->finalize($intent);

            return redirect()
                ->route('login')
                ->with('success', 'Payment Successful. Please check your email to activate your account.');
        } catch (\Throwable $e) {
            report($e);

            return back()->withInput()->with('error', 'We could not start your registration checkout right now. Please try again.');
        }
    }

    public function paymongoReturn(
        Request $request,
        SignupIntent $signupIntent,
        SignupOnboardingService $onboarding,
        PayMongoCheckoutService $payMongo,
    ) {
        try {
            if ($signupIntent->status === 'completed' || $signupIntent->lifecycle_state === 'email_sent') {
                return redirect()
                    ->route('login')
                    ->with('success', 'Payment Successful. Please check your email to activate your account.');
            }

            if ($signupIntent->status !== 'paid') {
                $sessionId = (string) $signupIntent->provider_reference;
                if ($sessionId === '') {
                    return redirect()->route('register')->with('error', 'We could not verify your payment session. Please try again.');
                }

                $data = $payMongo->retrieveCheckoutSession($sessionId);
                if ($data === null) {
                    return redirect()->route('register')->with('error', 'We could not verify your payment yet. If you completed checkout, wait a moment and try again.');
                }

                $paid = false;
                $method = null;
                $payments = data_get($data, 'attributes.payments');
                if (is_array($payments)) {
                    foreach ($payments as $payment) {
                        if (! is_array($payment)) {
                            continue;
                        }

                        if (data_get($payment, 'attributes.status') === 'paid') {
                            $paid = true;
                            $sourceType = data_get($payment, 'attributes.source.type');
                            $method = is_string($sourceType) ? $sourceType : null;
                            break;
                        }
                    }
                }

                if (! $paid) {
                    return redirect()->route('register')->with('error', 'Payment was not completed. You can try again anytime.');
                }

                $signupIntent = $onboarding->markPaid($signupIntent, $method);
            }

            $onboarding->finalize($signupIntent);

            return redirect()
                ->route('login')
                ->with('success', 'Payment Successful. Please check your email to activate your account.');
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('register')->with('error', 'Your payment was received, but we could not complete the signup automatically yet. Please contact support if this continues.');
        }
    }

    private function redirectByRole(User $user)
    {
        if ($user->hasRole('super-admin')) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->hasRole('account-owner')) {
            $tenant = $user->tenant;
            if ($tenant && $tenant->isTrialExpired()) {
                return redirect()->route('trial.billing.show');
            }

            return redirect()->route('dashboard.owner');
        }

        if ($user->hasRole('marketing-manager')) {
            return redirect()->route('dashboard.marketing');
        }

        if ($user->hasRole('sales-agent')) {
            return redirect()->route('dashboard.sales');
        }

        if ($user->hasRole('finance')) {
            return redirect()->route('dashboard.finance');
        }

        if ($user->hasRole('customer')) {
            return redirect()->route('dashboard.customer');
        }

        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login')->with('error', 'Login Failed. Your role does not have access.');
    }
}
