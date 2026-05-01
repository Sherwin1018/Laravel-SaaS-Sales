<?php

namespace App\Providers;

use App\Models\InAppNotification;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\HtmlString;
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
        View::share('emptyDash', new HtmlString('<strong class="empty-value-dash">&mdash;</strong>'));

        View::composer('layouts.admin', function ($view) {
            if (! auth()->check()) {
                return;
            }

            $user = auth()->user();
            $baseQuery = InAppNotification::query()->where('user_id', $user->id);
            $recentNotifications = (clone $baseQuery)
                ->orderByDesc('occurred_at')
                ->orderByDesc('id')
                ->limit(6)
                ->get();

            $view->with('layoutNotificationUnreadCount', (clone $baseQuery)->whereNull('read_at')->count());
            $view->with('layoutRecentNotifications', $recentNotifications);
            $view->with('layoutLatestNotificationId', (int) ($recentNotifications->max('id') ?? 0));
        });

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

        RateLimiter::for('setup-link-show', function (Request $request) {
            $ip = (string) ($request->ip() ?: 'guest');
            $token = (string) $request->route('token');

            return [
                Limit::perMinute(30)->by($ip . '|setup-show'),
                Limit::perMinute(8)->by($ip . '|setup-show|' . substr($token, 0, 16)),
            ];
        });

        RateLimiter::for('setup-link-complete', function (Request $request) {
            $ip = (string) ($request->ip() ?: 'guest');
            $token = (string) $request->route('token');

            return [
                Limit::perMinute(15)->by($ip . '|setup-complete'),
                Limit::perMinute(6)->by($ip . '|setup-complete|' . substr($token, 0, 16)),
            ];
        });

        RateLimiter::for('setup-resend', function (Request $request) {
            $ip = (string) ($request->ip() ?: 'guest');
            $email = strtolower(trim((string) $request->input('email', '')));

            return [
                Limit::perMinute(6)->by($ip . '|setup-resend'),
                Limit::perMinute(3)->by($ip . '|setup-resend|' . ($email !== '' ? $email : 'unknown')),
            ];
        });
    }
}
