<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\AutomationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FunnelController;
use App\Http\Controllers\FunnelPortalController;
use App\Http\Controllers\LeadVerificationController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\LeadCustomFieldController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PayMongoWebhookController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicOnboardingController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\TrialSubscriptionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicOnboardingController::class, 'landing'])->name('landing');

Route::middleware('guest')->group(function () {
    Route::get('/register', [PublicOnboardingController::class, 'showRegister'])->name('register');
    Route::post('/register', [PublicOnboardingController::class, 'startRegistrationCheckout'])->name('register.post');
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});
Route::get('/logout', function () {
    if (! Auth::check()) {
        return redirect()->route('login');
    }

    $token = csrf_token();
    $action = route('logout');
    $html = <<<HTML
<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"></head>
<body>
    <form id="logoutFallbackForm" method="POST" action="{$action}">
        <input type="hidden" name="_token" value="{$token}">
        <noscript><button type="submit">Continue logout</button></noscript>
    </form>
    <script>document.getElementById('logoutFallbackForm').submit();</script>
</body>
</html>
HTML;

    return response($html);
})->middleware('auth');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware('signed')
        ->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::middleware('verified')->group(function () {
        Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
        Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar.update');
        Route::delete('/profile/avatar', [ProfileController::class, 'deleteAvatar'])->name('profile.avatar.delete');
        Route::post('/profile/company-logo', [ProfileController::class, 'updateCompanyLogo'])->name('profile.company-logo.update');
        Route::delete('/profile/company-logo', [ProfileController::class, 'deleteCompanyLogo'])->name('profile.company-logo.delete');
        Route::put('/profile/theme', [ProfileController::class, 'updateTheme'])->name('profile.theme.update');
    });
});

Route::middleware(['auth', 'verified', 'role:super-admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::post('/admin/landing-hero-video', [AdminController::class, 'updateLandingHeroVideo'])->name('admin.landing-video.update');
    Route::delete('/admin/landing-hero-video', [AdminController::class, 'deleteLandingHeroVideo'])->name('admin.landing-video.delete');

    Route::get('/admin/tenants', [TenantController::class, 'index'])->name('admin.tenants.index');
    Route::get('/admin/tenants/create', [TenantController::class, 'create'])->name('admin.tenants.create');
    Route::post('/admin/tenants', [TenantController::class, 'store'])->name('admin.tenants.store');
    Route::get('/admin/tenants/{tenant}/edit', [TenantController::class, 'edit'])->name('admin.tenants.edit');
    Route::put('/admin/tenants/{tenant}', [TenantController::class, 'update'])->name('admin.tenants.update');
    Route::delete('/admin/tenants/{tenant}', [TenantController::class, 'destroy'])->name('admin.tenants.destroy');
    Route::get('/admin/plans', [PlanController::class, 'index'])->name('admin.plans.index');
    Route::get('/admin/plans/create', [PlanController::class, 'create'])->name('admin.plans.create');
    Route::post('/admin/plans', [PlanController::class, 'store'])->name('admin.plans.store');
    Route::get('/admin/plans/{plan}/edit', [PlanController::class, 'edit'])->name('admin.plans.edit');
    Route::put('/admin/plans/{plan}', [PlanController::class, 'update'])->name('admin.plans.update');
    Route::delete('/admin/plans/{plan}', [PlanController::class, 'destroy'])->name('admin.plans.destroy');

    Route::get('/admin/users', [UserController::class, 'adminIndex'])->name('admin.users.index');
    Route::patch('/admin/users/{user}/status', [UserController::class, 'toggleOwnerStatus'])->name('admin.users.status');
    Route::get('/admin/leads', [LeadController::class, 'adminIndex'])->name('admin.leads.index');
});

Route::middleware(['auth', 'role:account-owner'])->group(function () {
    Route::get('/billing/trial-upgrade', [TrialSubscriptionController::class, 'show'])->name('trial.billing.show');
    Route::post('/billing/trial-upgrade', [TrialSubscriptionController::class, 'startCheckout'])->name('trial.billing.checkout');
    Route::get('/billing/trial-upgrade/return/{payment}', [TrialSubscriptionController::class, 'paymongoReturn'])
        ->middleware('signed')
        ->name('trial.billing.return');
});

Route::middleware(['auth', 'tenant.subscription', 'role:super-admin'])->group(function () {
    // Super admin tenant subscription routes can go here
});

Route::middleware(['auth', 'tenant.subscription', 'role:sales-agent,marketing-manager,account-owner,finance'])->group(function () {
    Route::get('/dashboard/owner', [DashboardController::class, 'owner'])->middleware('role:account-owner')->name('dashboard.owner');
    Route::get('/dashboard/marketing', [DashboardController::class, 'marketing'])->middleware('role:marketing-manager')->name('dashboard.marketing');
    Route::get('/marketing/funnel-analytics', [DashboardController::class, 'funnelAnalytics'])
        ->middleware('role:account-owner,marketing-manager')
        ->name('marketing.funnel_analytics');
    Route::get('/dashboard/sales', [DashboardController::class, 'sales'])->middleware('role:sales-agent')->name('dashboard.sales');
    Route::get('/dashboard/finance', [DashboardController::class, 'finance'])->middleware('role:finance')->name('dashboard.finance');

    Route::get('/leads', [LeadController::class, 'index'])->name('leads.index');
    Route::get('/leads/create', [LeadController::class, 'create'])->name('leads.create');
    Route::post('/leads', [LeadController::class, 'store'])->name('leads.store');
    Route::get('/crm/custom-fields', [LeadCustomFieldController::class, 'index'])->name('crm.custom-fields.index');
    Route::post('/crm/custom-fields', [LeadCustomFieldController::class, 'store'])->name('crm.custom-fields.store');
    Route::put('/crm/custom-fields/{field}', [LeadCustomFieldController::class, 'update'])->name('crm.custom-fields.update');
    Route::delete('/crm/custom-fields/{field}', [LeadCustomFieldController::class, 'destroy'])->name('crm.custom-fields.destroy');
    Route::get('/leads/{lead}/edit', [LeadController::class, 'edit'])->name('leads.edit');
    Route::put('/leads/{lead}', [LeadController::class, 'update'])->name('leads.update');
    Route::delete('/leads/{lead}', [LeadController::class, 'destroy'])->name('leads.destroy');
    Route::post('/leads/{lead}/activities', [LeadController::class, 'storeActivity'])->name('leads.activities.store');
    Route::post('/leads/{lead}/assign', [LeadController::class, 'assign'])->name('leads.assign');
    Route::post('/leads/{lead}/log-email', [LeadController::class, 'logEmail'])->name('leads.log-email');
    Route::post('/leads/{lead}/score-event', [LeadController::class, 'applyScoreEvent'])->name('leads.score-event');

    Route::middleware(['role:account-owner'])->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::post('/users/{user}/resend-verification', [UserController::class, 'resendVerification'])->name('users.resend-verification');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });

    Route::middleware(['role:account-owner,marketing-manager'])->group(function () {
        Route::get('/funnels', [FunnelController::class, 'index'])->name('funnels.index');

        Route::get('/automation', [AutomationController::class, 'overview'])->name('automation.overview');
        Route::get('/automation/sequences', [AutomationController::class, 'sequences'])->name('automation.sequences.index');
        Route::get('/automation/sequences/create', [AutomationController::class, 'createSequenceBuilder'])->name('automation.sequences.create');
        Route::post('/automation/sequences', [AutomationController::class, 'storeSequence'])->name('automation.sequences.store');
        Route::get('/automation/sequences/{sequence}/edit', [AutomationController::class, 'editSequenceBuilder'])->name('automation.sequences.edit');
        Route::put('/automation/sequences/{sequence}', [AutomationController::class, 'updateSequence'])->name('automation.sequences.update');
        Route::post('/automation/sequences/{sequence}/toggle', [AutomationController::class, 'toggleSequence'])->name('automation.sequences.toggle');
        Route::delete('/automation/sequences/{sequence}', [AutomationController::class, 'destroySequence'])->name('automation.sequences.destroy');
        Route::get('/automation/workflows', [AutomationController::class, 'workflows'])->name('automation.workflows.index');
        Route::get('/automation/workflows/create', [AutomationController::class, 'createWorkflow'])->name('automation.workflows.create');
        Route::post('/automation/workflows', [AutomationController::class, 'storeWorkflow'])->name('automation.workflows.store');
        Route::get('/automation/workflows/{workflow}/edit', [AutomationController::class, 'editWorkflow'])->name('automation.workflows.edit');
        Route::put('/automation/workflows/{workflow}', [AutomationController::class, 'updateWorkflow'])->name('automation.workflows.update');
        Route::post('/automation/workflows/{workflow}/toggle', [AutomationController::class, 'toggleWorkflow'])->name('automation.workflows.toggle');
        Route::post('/automation/workflows/{workflow}/duplicate', [AutomationController::class, 'duplicateWorkflow'])->name('automation.workflows.duplicate');
        Route::delete('/automation/workflows/{workflow}', [AutomationController::class, 'destroyWorkflow'])->name('automation.workflows.destroy');
        Route::get('/automation/logs', [AutomationController::class, 'logs'])->name('automation.logs.index');
        Route::get('/automation/logs/{id}', [AutomationController::class, 'showLog'])->name('automation.logs.show');

        Route::get('/funnels/create', [FunnelController::class, 'create'])->name('funnels.create');
        Route::post('/funnels', [FunnelController::class, 'store'])->name('funnels.store');
        Route::get('/funnels/{funnel}/edit', [FunnelController::class, 'edit'])->name('funnels.edit');
        Route::get('/funnels/{funnel}/preview/{step?}', [FunnelController::class, 'preview'])->name('funnels.preview');
        Route::put('/funnels/{funnel}', [FunnelController::class, 'update'])->name('funnels.update');
        Route::post('/funnels/{funnel}/builder/layout', [FunnelController::class, 'saveLayout'])->name('funnels.builder.layout.save');
        Route::get('/funnels/{funnel}/builder/assets', [FunnelController::class, 'builderAssets'])->name('funnels.builder.assets.index');
        Route::post('/funnels/{funnel}/builder/assets/delete', [FunnelController::class, 'destroyBuilderAssets'])->name('funnels.builder.assets.destroy');
        Route::post('/funnels/{funnel}/builder/upload-image', [FunnelController::class, 'uploadBuilderImage'])->name('funnels.builder.image.upload');
        Route::post('/funnels/{funnel}/publish', [FunnelController::class, 'publish'])->name('funnels.publish');
        Route::post('/funnels/{funnel}/unpublish', [FunnelController::class, 'unpublish'])->name('funnels.unpublish');
        Route::get('/funnels/{funnel}/analytics', [FunnelController::class, 'analytics'])->name('funnels.analytics');
        Route::get('/funnels/{funnel}/analytics/export', [FunnelController::class, 'exportAnalytics'])->name('funnels.analytics.export');
        Route::get('/funnels/{funnel}/events', [FunnelController::class, 'events'])->name('funnels.events');
        Route::delete('/funnels/{funnel}', [FunnelController::class, 'destroy'])->name('funnels.destroy');
        Route::post('/funnels/{funnel}/steps', [FunnelController::class, 'storeStep'])->name('funnels.steps.store');
        Route::put('/funnels/{funnel}/steps/{step}', [FunnelController::class, 'updateStep'])->name('funnels.steps.update');
        Route::delete('/funnels/{funnel}/steps/{step}', [FunnelController::class, 'destroyStep'])->name('funnels.steps.destroy');
        Route::post('/funnels/{funnel}/steps/{step}/versions', [FunnelController::class, 'storeVersion'])->name('funnels.steps.versions.store');
        Route::post('/funnels/{funnel}/steps/reorder', [FunnelController::class, 'reorderSteps'])->name('funnels.steps.reorder');

    });

    Route::middleware(['role:account-owner,finance'])->group(function () {
        Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');
    });
});

Route::middleware(['auth', 'verified', 'role:customer'])->group(function () {
    Route::get('/dashboard/customer', [DashboardController::class, 'customer'])->name('dashboard.customer');
});

Route::get('/f/{funnelSlug}/{stepSlug?}', [FunnelPortalController::class, 'show'])->middleware('throttle:funnel-public-view')->name('funnels.portal.step');
Route::get('/funnel/{funnelSlug}/{stepSlug?}', [FunnelPortalController::class, 'show'])->middleware('throttle:funnel-public-view')->name('funnels.portal.step.alias');
Route::post('/f/{funnelSlug}/{stepSlug}/opt-in', [FunnelPortalController::class, 'optIn'])->middleware('throttle:funnel-public-submit')->name('funnels.portal.optin');
Route::post('/f/{funnelSlug}/{stepSlug}/checkout', [FunnelPortalController::class, 'checkout'])->middleware('throttle:funnel-public-submit')->name('funnels.portal.checkout');
Route::get('/f/{funnelSlug}/{stepSlug}/paymongo/return/{payment}', [FunnelPortalController::class, 'paymongoReturn'])
    ->middleware('signed')
    ->name('funnels.portal.paymongo.return');
Route::post('/f/{funnelSlug}/{stepSlug}/offer', [FunnelPortalController::class, 'offer'])->middleware('throttle:funnel-public-submit')->name('funnels.portal.offer');
Route::get('/register/paymongo/return/{signupIntent}', [PublicOnboardingController::class, 'paymongoReturn'])
    ->middleware('signed')
    ->name('register.paymongo.return');

Route::post('/webhooks/paymongo', PayMongoWebhookController::class)->name('webhooks.paymongo');
