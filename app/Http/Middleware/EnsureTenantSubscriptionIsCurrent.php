<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureTenantSubscriptionIsCurrent
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (! $user) {
            return redirect()->route('login');
        }

        $tenant = $user->tenant;
        if (! $tenant) {
            return $next($request);
        }

        if ($tenant->isTrialExpired()) {
            if (! $user->hasRole('account-owner')) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()
                    ->route('login')
                    ->with('error', 'Your workspace trial has ended. Please contact your Account Owner to reactivate access.');
            }

            return redirect()
                ->route('trial.billing.show')
                ->with('error', 'Your 7-day free trial has ended. Complete payment to continue using your workspace.');
        }

        if ($tenant->isInactive()) {
            if (! $user->hasRole('account-owner')) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()
                    ->route('login')
                    ->with('error', 'Your workspace is inactive. Please contact your Account Owner to restore access.');
            }

            return redirect()
                ->route('trial.billing.show')
                ->with('error', 'Your workspace is inactive. Complete payment to restore access.');
        }

        return $next($request);
    }
}
