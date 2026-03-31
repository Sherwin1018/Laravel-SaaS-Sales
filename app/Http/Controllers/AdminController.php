<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use App\Services\AnalyticsDashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    private const LANDING_VIDEO_PATH_KEY = 'landing_hero_video_path';
    private const LANDING_VIDEO_WIDTH_KEY = 'landing_hero_video_width';
    private const LANDING_VIDEO_HEIGHT_KEY = 'landing_hero_video_height';

    public function index(AnalyticsDashboardService $analytics)
    {
        $monthKeyExpression = DB::getDriverName() === 'pgsql'
            ? "TO_CHAR(created_at, 'YYYY-MM')"
            : "DATE_FORMAT(created_at, '%Y-%m')";

        $platformSummary = $analytics->platformSummary();
        $tenantCount = $platformSummary['tenant_count'];
        $activeTenantCount = $platformSummary['active_tenants'];
        $trialTenantCount = $platformSummary['trial_tenants'];
        $inactiveTenantCount = $platformSummary['inactive_tenants'];
        $userCount = User::count();
        $leadCount = Lead::withoutGlobalScope('tenant')->count();
        $mrr = $platformSummary['mrr'];
        $previousMonthMrr = $platformSummary['previous_month_mrr'];
        $mrrGrowthRate = $platformSummary['mrr_growth_rate'];
        $churnRate = $platformSummary['churn_rate'];
        $arpu = $platformSummary['arpu'];
        $payingTenantCount = $platformSummary['paying_tenants'];
        $usageMetrics = $platformSummary['usage_metrics'];
        $tenantGrowth = $platformSummary['tenant_growth'];

        $paymentStatusTotals = Payment::select('status', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total'))
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        $usersByRole = Role::withCount('users')
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        $leadTrendRaw = Lead::withoutGlobalScope('tenant')
            ->selectRaw("{$monthKeyExpression} as month_key, COUNT(*) as total")
            ->where('created_at', '>=', now()->copy()->subMonths(5)->startOfMonth())
            ->groupBy('month_key')
            ->pluck('total', 'month_key');

        $leadTrendLabels = [];
        $leadTrendValues = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->copy()->subMonths($i);
            $key = $month->format('Y-m');
            $leadTrendLabels[] = $month->format('M Y');
            $leadTrendValues[] = (int) ($leadTrendRaw[$key] ?? 0);
        }

        $actionableTenants = Tenant::whereIn('status', ['trial', 'inactive'])
            ->latest()
            ->paginate(10, ['id', 'company_name', 'status', 'subscription_plan', 'created_at'], 'tenants_page');

        $landingHeroVideoPath = AppSetting::getValue(self::LANDING_VIDEO_PATH_KEY);
        $landingHeroVideoWidth = AppSetting::getValue(self::LANDING_VIDEO_WIDTH_KEY, '1280');
        $landingHeroVideoHeight = AppSetting::getValue(self::LANDING_VIDEO_HEIGHT_KEY, '720');
        $landingHeroVideoUrl = null;
        if (is_string($landingHeroVideoPath) && $landingHeroVideoPath !== '' && Storage::disk('public')->exists($landingHeroVideoPath)) {
            $landingHeroVideoUrl = Storage::disk('public')->url($landingHeroVideoPath);
        }

        return view('admin.dashboard', compact(
            'tenantCount',
            'activeTenantCount',
            'trialTenantCount',
            'inactiveTenantCount',
            'userCount',
            'leadCount',
            'mrr',
            'previousMonthMrr',
            'mrrGrowthRate',
            'churnRate',
            'arpu',
            'payingTenantCount',
            'usageMetrics',
            'tenantGrowth',
            'paymentStatusTotals',
            'usersByRole',
            'leadTrendLabels',
            'leadTrendValues',
            'actionableTenants',
            'landingHeroVideoUrl',
            'landingHeroVideoWidth',
            'landingHeroVideoHeight'
        ));
    }

    public function updateLandingHeroVideo(Request $request)
    {
        $validated = $request->validate([
            'hero_video' => 'required|file|mimetypes:video/mp4|max:25600',
            'video_width' => 'required|integer|min:320|max:3840',
            'video_height' => 'required|integer|min:180|max:2160',
        ], [
            'hero_video.max' => 'Video must be 25 MB or less.',
            'hero_video.mimetypes' => 'Only MP4 videos are allowed.',
        ]);

        $existingPath = AppSetting::getValue(self::LANDING_VIDEO_PATH_KEY);
        if (is_string($existingPath) && $existingPath !== '' && Storage::disk('public')->exists($existingPath)) {
            Storage::disk('public')->delete($existingPath);
        }

        $storedPath = $request->file('hero_video')->store('landing/hero-videos', 'public');

        AppSetting::putValue(self::LANDING_VIDEO_PATH_KEY, $storedPath);
        AppSetting::putValue(self::LANDING_VIDEO_WIDTH_KEY, (string) $validated['video_width']);
        AppSetting::putValue(self::LANDING_VIDEO_HEIGHT_KEY, (string) $validated['video_height']);

        return redirect()->route('admin.dashboard')->with('success', 'Landing hero video was updated successfully.');
    }

    public function deleteLandingHeroVideo()
    {
        $existingPath = AppSetting::getValue(self::LANDING_VIDEO_PATH_KEY);
        if (is_string($existingPath) && $existingPath !== '' && Storage::disk('public')->exists($existingPath)) {
            Storage::disk('public')->delete($existingPath);
        }

        AppSetting::forgetKey(self::LANDING_VIDEO_PATH_KEY);
        AppSetting::forgetKey(self::LANDING_VIDEO_WIDTH_KEY);
        AppSetting::forgetKey(self::LANDING_VIDEO_HEIGHT_KEY);

        return redirect()->route('admin.dashboard')->with('success', 'Landing hero video was removed.');
    }
}
