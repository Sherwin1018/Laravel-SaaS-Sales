<?php

namespace App\Http\Controllers;

use App\Services\SubscriptionLifecycleService;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Show the login form
    public function showLoginForm()
    {
        if (Auth::check()) {
            return $this->redirectByRole(auth()->user());
        }

        return view('auth.login');
    }

    // Handle login
    public function login(Request $request)
    {
        // Validate input
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, true)) {
            $request->session()->regenerate();

            $user = auth()->user();
            if ($user->status !== 'active') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                $reason = $user->suspension_reason ?: 'No reason provided.';
                $message = "Login Failed. Your account has been temporarily suspended. Please contact support or your system administrator for assistance. Reason: {$reason} Support: nehemiah.solutions.corp@gmail.com";

                if ($request->expectsJson()) {
                    return response()->json([
                        'ok' => false,
                        'message' => $message,
                    ], 403);
                }

                return redirect()->route('login')->with('error', $message);
            }

            if (
                ! $user->hasRole('super-admin')
                && $this->requiresActivationSetup($user)
            ) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                if ($request->expectsJson()) {
                    return response()->json([
                        'ok' => false,
                        'message' => 'Please verify your email and complete password setup before continuing.',
                    ], 403);
                }

                return redirect()->route('login')->with('error', 'Please verify your email and complete password setup before continuing.');
            }

            $user->last_login_at = now();
            $user->save();

            if ($request->expectsJson()) {
                return $this->jsonLoginResponse($user, $request);
            }

            return $this->redirectByRole($user);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => false,
                'message' => 'Login Failed. Invalid email or password.',
            ], 422);
        }

        return back()->with('error', 'Login Failed. Invalid email or password.');
    }

    public function splashRoleHint(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        $email = mb_strtolower(trim((string) ($validated['email'] ?? '')));
        $user = User::query()
            ->with('roles')
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first();

        return response()->json([
            'role' => $user ? $this->splashRoleKeyForUser($user) : 'customer',
        ]);
    }

    // Logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login')->with('success', 'Logout Successfully');
    }

    private function redirectByRole($user)
    {
        $destination = null;

        if ($user->hasRole('super-admin')) {
            $destination = '/admin/dashboard';
        }

        if (! $destination && ($response = $this->tenantAccessRedirect($user))) {
            return $response;
        }

        if (! $destination) {
            $destination = $this->dashboardRouteForUser($user);
        }

        if ($destination) {
            return redirect()->intended($destination)->with('success', 'Login Successfully');
        }

        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('login')->with('error', 'Login Failed. Your role does not have access.');
    }

    private function jsonLoginResponse($user, Request $request)
    {
        if ($user->hasRole('super-admin')) {
            return response()->json($this->loginSuccessPayload($user, url('/admin/dashboard')));
        }

        $tenant = $user->tenant;
        if ($tenant) {
            $tenant = app(SubscriptionLifecycleService::class)->expireGracePeriodIfNeeded($tenant);

            if ($tenant->isTrialExpired()) {
                if ($user->hasRole('account-owner')) {
                    return response()->json($this->loginSuccessPayload($user, route('trial.billing.show')));
                }

                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return response()->json([
                    'ok' => false,
                    'message' => 'Your workspace trial has ended. Please contact your Account Owner to reactivate access.',
                ], 403);
            }

            if ($tenant->isInactive()) {
                if ($user->hasRole('account-owner')) {
                    return response()->json($this->loginSuccessPayload($user, route('trial.billing.show')));
                }

                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return response()->json([
                    'ok' => false,
                    'message' => 'Your workspace is inactive. Please contact your Account Owner to restore access.',
                ], 403);
            }

            if ($tenant->isOverdue() && $user->hasRole('account-owner')) {
                return response()->json($this->loginSuccessPayload($user, route('payments.index')));
            }
        }

        $destination = $this->dashboardRouteForUser($user);
        if ($destination) {
            return response()->json($this->loginSuccessPayload($user, $destination));
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'ok' => false,
            'message' => 'Login Failed. Your role does not have access.',
        ], 403);
    }

    private function loginSuccessPayload($user, string $destination): array
    {
        return [
            'ok' => true,
            'redirect_to' => $destination,
            'splash_role' => $this->splashRoleKeyForUser($user),
            'splash_email' => mb_strtolower(trim((string) ($user->email ?? ''))),
        ];
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

    private function splashRoleKeyForUser($user): string
    {
        if ($user->hasRole('super-admin')) {
            return 'super_admin';
        }

        if ($user->hasRole('account-owner')) {
            return 'account_owner';
        }

        if ($user->hasRole('marketing-manager')) {
            return 'marketing_manager';
        }

        if ($user->hasRole('sales-agent')) {
            return 'sales_agent';
        }

        if ($user->hasRole('finance')) {
            return 'finance';
        }

        return 'customer';
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
