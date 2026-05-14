<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOwnerPayoutSetupCompleted
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->hasRole('account-owner') || ! $user->tenant) {
            return $next($request);
        }

        if ($request->routeIs(
            'dashboard.owner',
            'auth.google.processing',
            'owner.payout-setup.show',
            'profile.payout.update',
            'logout'
        )) {
            return $next($request);
        }

        $user->loadMissing('tenant.defaultPayoutAccount');
        $payoutAccount = $user->tenant?->defaultPayoutAccount;

        if ($payoutAccount && $payoutAccount->hasDestinationDetails()) {
            return $next($request);
        }

        return redirect()
            ->route('dashboard.owner')
            ->with('error', 'Complete your payout account setup before continuing.');
    }
}
