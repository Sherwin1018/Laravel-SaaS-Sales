<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureTenantSubscriptionIsCurrent
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (! $user) {
            return redirect()->route('login');
        }

        if (! $user->hasRole('account-owner')) {
            return $next($request);
        }

        $tenant = $user->tenant;
        if (! $tenant) {
            return $next($request);
        }

        if ($tenant->isTrialExpired()) {
            return redirect()
                ->route('trial.billing.show')
                ->with('error', 'Your 7-day free trial has ended. Complete payment to continue using your workspace.');
        }

        if ($tenant->status === 'inactive') {
            return redirect()
                ->route('trial.billing.show')
                ->with('error', 'Your workspace is inactive. Complete payment to restore access.');
        }

        return $next($request);
    }
}
