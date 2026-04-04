<?php

namespace App\Http\Controllers;

use App\Models\SignupIntent;
use App\Models\SetupToken;
use App\Models\User;
use App\Services\OnboardingAuditService;
use App\Services\SignupOnboardingService;
use App\Services\SetupTokenService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SetupAccessController extends Controller
{
    public function show(string $token, SetupTokenService $setupTokenService)
    {
        $setupToken = $setupTokenService->resolveByPlainToken($token);
        if (! $setupToken || ! $setupTokenService->isUsable($setupToken)) {
            app(OnboardingAuditService::class)->record(
                'setup_failed',
                'failed',
                'Setup link is invalid or expired (show).',
                $setupToken?->user,
                $setupToken?->user ? SignupIntent::query()->where('email', $setupToken->user->email)->latest('id')->first() : null,
                ['reason' => 'invalid_or_expired_show']
            );
            return view('auth.setup-expired', [
                'email' => $setupToken?->user?->email ?? request()->query('email'),
            ]);
        }

        return view('auth.setup-password', [
            'token' => $token,
            'email' => $setupToken->user?->email,
        ]);
    }

    public function complete(
        Request $request,
        string $token,
        SetupTokenService $setupTokenService,
    ) {
        $validated = $request->validate([
            'password' => [
                'required',
                'string',
                'min:12',
                'max:64',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[^A-Za-z0-9]/',
                'confirmed',
            ],
        ], [
            'password.regex' => 'Password must contain uppercase, lowercase, number, and a special character.',
        ]);

        $setupToken = $setupTokenService->resolveByPlainToken($token);
        if (! $setupToken || ! $setupTokenService->isUsable($setupToken)) {
            app(OnboardingAuditService::class)->record(
                'setup_failed',
                'failed',
                'Setup link is invalid or expired (complete).',
                $setupToken?->user,
                $setupToken?->user ? SignupIntent::query()->where('email', $setupToken->user->email)->latest('id')->first() : null,
                ['reason' => 'invalid_or_expired_complete']
            );
            return redirect()->route('setup.show', ['token' => $token])->with('error', 'Setup link is invalid or already expired.');
        }

        try {
            [$user, $activated] = DB::transaction(function () use ($setupToken, $validated, $setupTokenService) {
            $freshToken = SetupToken::query()->lockForUpdate()->findOrFail($setupToken->id);
            $user = User::query()->lockForUpdate()->findOrFail($setupToken->user_id);

            if (! $setupTokenService->isUsable($freshToken)) {
                app(OnboardingAuditService::class)->record(
                    'setup_failed',
                    'failed',
                    'Setup token was no longer usable during completion.',
                    $user,
                    SignupIntent::query()->where('email', $user->email)->latest('id')->first(),
                    ['reason' => 'race_or_used_token']
                );
                return [$user, false];
            }

            $user->forceFill([
                'password' => $validated['password'],
                'status' => 'active',
                'activation_state' => 'active',
                'email_verified_at' => $user->email_verified_at ?? now(),
                'activation_completed_at' => now(),
                'must_change_password' => false,
            ])->save();

            if ($user->tenant && $user->hasRole('account-owner') && $user->tenant->status === 'inactive') {
                $user->tenant->update([
                    'status' => 'active',
                    'subscription_activated_at' => $user->tenant->subscription_activated_at ?? now(),
                ]);
            }

            $setupTokenService->consume($freshToken);

            $intent = SignupIntent::query()->where('email', $user->email)->latest('id')->first();
            if ($intent && $intent->status !== 'failed') {
                $transition = function (string $next, array $attributes = []) use ($intent): void {
                    if (! $intent->transitionTo($next, $attributes)) {
                        throw new \RuntimeException("Invalid signup lifecycle transition during setup: {$intent->lifecycle_state} -> {$next}");
                    }
                    $intent->refresh();
                };

                if ($intent->lifecycle_state === SignupIntent::STATE_ACCOUNT_CREATED_PENDING_ACTIVATION) {
                    $transition(SignupIntent::STATE_EMAIL_SENT, [
                        'email_sent_at' => $intent->email_sent_at ?? now(),
                    ]);
                }
                if ($intent->lifecycle_state === SignupIntent::STATE_EMAIL_SENT) {
                    $transition(SignupIntent::STATE_EMAIL_VERIFIED, [
                        'activated_at' => now(),
                    ]);
                }
                if ($intent->lifecycle_state === SignupIntent::STATE_EMAIL_VERIFIED) {
                    $transition(SignupIntent::STATE_PASSWORD_SET);
                }
                if ($intent->lifecycle_state === SignupIntent::STATE_PASSWORD_SET) {
                    $transition(SignupIntent::STATE_ACTIVE, [
                        'status' => 'completed',
                        'activated_at' => now(),
                        'completed_at' => $intent->completed_at ?? now(),
                    ]);
                }
            }

            app(OnboardingAuditService::class)->record(
                'setup_completed',
                'success',
                'User completed setup and password creation.',
                $user,
                $intent,
                ['activation_state' => $user->activation_state]
            );

            return [$user->fresh(['roles']), true];
            });
        } catch (\Throwable $e) {
            app(OnboardingAuditService::class)->record(
                'setup_failed',
                'failed',
                'Setup completion failed due to lifecycle or persistence error.',
                $setupToken->user,
                $setupToken->user ? SignupIntent::query()->where('email', $setupToken->user->email)->latest('id')->first() : null,
                ['exception' => $e->getMessage()]
            );

            return redirect()->route('setup.show', ['token' => $token])->with('error', 'Setup could not be completed right now. Please request a new activation email.');
        }

        if (! $activated) {
            return redirect()->route('setup.show', ['token' => $token])->with('error', 'Setup link is invalid or already expired.');
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        return $this->redirectByRole($user)->with('success', 'Your password has been set successfully. You are now logged in.');
    }

    public function resend(Request $request, SetupTokenService $setupTokenService)
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255',
        ]);

        $user = User::query()->where('email', $validated['email'])->first();
        if (! $user || $user->activation_state === 'active') {
            return back()->with('success', 'If your account is eligible, a new activation email has been queued.');
        }

        app(SignupOnboardingService::class)->queueSetupEmail($user, 'setup_link_expiring_soon', $setupTokenService);

        app(OnboardingAuditService::class)->record(
            'setup_resend_requested',
            'success',
            'User requested setup resend.',
            $user,
            SignupIntent::query()->where('email', $user->email)->latest('id')->first()
        );

        return back()->with('success', 'A new activation email has been queued.');
    }

    private function redirectByRole(User $user)
    {
        if ($user->hasRole('super-admin')) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->hasRole('account-owner')) {
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

        return redirect()->route('login')->with('error', 'Your role does not have access.');
    }
}
