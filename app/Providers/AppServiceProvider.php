<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('funnel-public-view', function (Request $request) {
            return [
                Limit::perMinute(120)->by((string) ($request->ip() ?: 'guest')),
            ];
        });

        RateLimiter::for('funnel-public-submit', function (Request $request) {
            $ip = (string) ($request->ip() ?: 'guest');
            $funnel = strtolower(trim((string) $request->route('funnelSlug')));
            $email = strtolower(trim((string) $request->input('email', '')));

            return [
                Limit::perMinute(20)->by($ip . '|' . $funnel . '|submit'),
                Limit::perMinute(6)->by($ip . '|' . $funnel . '|' . ($email !== '' ? $email : $request->path())),
            ];
        });
    }
}
