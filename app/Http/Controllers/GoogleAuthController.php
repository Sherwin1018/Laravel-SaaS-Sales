<?php

namespace App\Http\Controllers;

use App\Services\SubscriptionLifecycleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        if ($this->isGoogleConfigMissing()) {
            return redirect()->route('login')->with('error', 'Google sign-in is not configured yet.');
        }

        return Socialite::driver('google')
            ->scopes(['openid', 'profile', 'email'])
            ->with(['prompt' => 'select_account'])
            ->redirect();
    }

    public function callback(Request $request)
    {
        if ($this->isGoogleConfigMissing()) {
            return redirect()->route('login')->with('error', 'Google sign-in is not configured yet.');
        }

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable) {
            return redirect()->route('login')->with('error', 'Google sign-in failed. Please try again.');
        }

        $email = mb_strtolower(trim((string) $googleUser->getEmail()));
        if ($email === '') {
            return redirect()->route('login')->with('error', 'Google account did not provide a valid email.');
        }

        $user = \App\Models\User::query()->whereRaw('LOWER(email) = ?', [$email])->first();
        if (! $user) {
            return redirect()->route('login')->with('error', 'No existing account found for this Google email. Please use your registered account.');
        }

        if ($user->hasRole('super-admin')) {
            return redirect()->route('login')->with('error', 'Super Admin accounts cannot use Google sign-in.');
        }

        if ($user->status !== 'active') {
            $reason = $user->suspension_reason ?: 'No reason provided.';

            return redirect()->route('login')->with('error', "Login Failed. Your account is inactive. Reason: {$reason}");
        }

        if ($this->requiresActivationSetup($user)) {
            return redirect()->route('login')->with('error', 'Please complete email verification and setup-password first.');
        }

        $googleId = (string) $googleUser->getId();
        if ($googleId === '') {
            return redirect()->route('login')->with('error', 'Google sign-in failed. Missing Google account ID.');
        }

        if ($user->google_id && $user->google_id !== $googleId) {
            return redirect()->route('login')->with('error', 'This account is already linked to another Google identity.');
        }

        if (! $user->google_id) {
            $user->google_id = $googleId;
        }
        $user->last_login_at = now();
        $user->save();

        Auth::login($user, true);
        $request->session()->regenerate();

        return $this->redirectByRole($user, true);
    }

    private function isGoogleConfigMissing(): bool
    {
        return ! config('services.google.client_id')
            || ! config('services.google.client_secret')
            || ! config('services.google.redirect');
    }

    public function processing(Request $request)
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $redirectTo = $request->session()->pull('google_login_redirect_to');
        if (! is_string($redirectTo) || $redirectTo === '') {
            $redirectTo = $this->dashboardRouteForUser(Auth::user()) ?? route('landing');
        }

        return view('auth.google-processing', [
            'redirectTo' => $redirectTo,
        ]);
    }

    private function redirectByRole($user, bool $useGoogleSplash = false)
    {
        if ($user->hasRole('super-admin')) {
            return redirect()->intended('/admin/dashboard')->with('success', 'Login Successfully');
        }

        if ($response = $this->tenantAccessRedirect($user)) {
            return $response;
        }

        $destination = $this->dashboardRouteForUser($user);
        if ($destination) {
            if ($useGoogleSplash) {
                session(['google_login_redirect_to' => $destination]);

                return redirect()->route('auth.google.processing');
            }

            return redirect()->intended($destination)->with('success', 'Login Successfully');
        }

        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login')->with('error', 'Login Failed. Your role does not have access.');
    }

    private function dashboardRouteForUser($user): ?string
    {
        if ($user->hasRole('account-owner')) {
            return route('dashboard.owner');
        }

        if ($user->hasRole('marketing-manager')) {
            return route('dashboard.marketing');
        }

        if ($user->hasRole('sales-agent')) {
            return route('dashboard.sales');
        }

        if ($user->hasRole('finance')) {
            return route('dashboard.finance');
        }

        if ($user->hasRole('customer')) {
            return route('dashboard.customer');
        }

        return null;
    }

    private function tenantAccessRedirect($user)
    {
        $tenant = $user->tenant;
        if (! $tenant) {
            return null;
        }

        $tenant = app(SubscriptionLifecycleService::class)->expireGracePeriodIfNeeded($tenant);

        if ($tenant->isTrialExpired()) {
            if ($user->hasRole('account-owner')) {
                return redirect()->intended(route('trial.billing.show'))->with('error', 'Your 7-day free trial has ended. Complete payment to continue.');
            }

            Auth::logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();

            return redirect()->route('login')->with('error', 'Your workspace trial has ended. Please contact your Account Owner to reactivate access.');
        }

        if ($tenant->isInactive()) {
            if ($user->hasRole('account-owner')) {
                return redirect()->intended(route('trial.billing.show'))->with('error', 'Your workspace is inactive. Complete payment to restore access.');
            }

            Auth::logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();

            return redirect()->route('login')->with('error', 'Your workspace is inactive. Please contact your Account Owner to restore access.');
        }

        if ($tenant->isOverdue() && $user->hasRole('account-owner')) {
            return redirect()->intended(route('payments.index'))->with('error', 'A recent payment failed. Your workspace is in a grace period until ' . optional($tenant->billing_grace_ends_at)->format('F j, Y g:i A') . '. Complete payment to avoid deactivation.');
        }

        return null;
    }

    private function requiresActivationSetup($user): bool
    {
        return in_array((string) $user->activation_state, [
            'invited',
            'pending_activation',
            'email_sent',
            'email_verified',
            'password_set',
        ], true);
    }
}
