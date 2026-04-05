<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AutomationController;
use App\Http\Controllers\AdminFunnelTemplateController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FunnelController;
use App\Http\Controllers\FunnelPortalController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\LeadCustomFieldController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\N8nWebhookController;
use App\Http\Controllers\N8nAutomationController;
use App\Http\Controllers\PayMongoWebhookController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicOnboardingController;
use App\Http\Controllers\SetupAccessController;
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
    Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect'])->name('auth.google.redirect');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');
    Route::post('/setup/resend', [SetupAccessController::class, 'resend'])->middleware('throttle:setup-resend')->name('setup.resend');
    Route::get('/setup/{token}', [SetupAccessController::class, 'show'])->middleware('throttle:setup-link-show')->name('setup.show');
    Route::post('/setup/{token}', [SetupAccessController::class, 'complete'])->middleware('throttle:setup-link-complete')->name('setup.complete');
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
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar.update');
    Route::delete('/profile/avatar', [ProfileController::class, 'deleteAvatar'])->name('profile.avatar.delete');
    Route::post('/profile/company-logo', [ProfileController::class, 'updateCompanyLogo'])->name('profile.company-logo.update');
    Route::delete('/profile/company-logo', [ProfileController::class, 'deleteCompanyLogo'])->name('profile.company-logo.delete');
    Route::put('/profile/theme', [ProfileController::class, 'updateTheme'])->name('profile.theme.update');
});

Route::middleware(['auth', 'role:account-owner'])->group(function () {
    Route::get('/billing/trial-upgrade', [TrialSubscriptionController::class, 'show'])->name('trial.billing.show');
    Route::post('/billing/trial-upgrade', [TrialSubscriptionController::class, 'startCheckout'])->name('trial.billing.checkout');
    Route::get('/billing/trial-upgrade/return/{payment}', [TrialSubscriptionController::class, 'paymongoReturn'])
        ->middleware('signed')
        ->name('trial.billing.return');
});

Route::middleware(['auth', 'tenant.subscription', 'role:super-admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::post('/admin/landing-hero-video', [AdminController::class, 'updateLandingHeroVideo'])->name('admin.landing-video.update');
    Route::delete('/admin/landing-hero-video', [AdminController::class, 'deleteLandingHeroVideo'])->name('admin.landing-video.delete');
    Route::get('/admin/funnel-templates', [AdminFunnelTemplateController::class, 'index'])->name('admin.funnel-templates.index');
    Route::get('/admin/funnel-templates/create', [AdminFunnelTemplateController::class, 'create'])->name('admin.funnel-templates.create');
    Route::get('/admin/funnel-templates/import', [AdminFunnelTemplateController::class, 'import'])->name('admin.funnel-templates.import');
    Route::post('/admin/funnel-templates', [AdminFunnelTemplateController::class, 'store'])->name('admin.funnel-templates.store');
    Route::post('/admin/funnel-templates/import', [AdminFunnelTemplateController::class, 'importStore'])->name('admin.funnel-templates.import.store');
    Route::get('/admin/funnel-templates/{funnel_template}/edit', [AdminFunnelTemplateController::class, 'edit'])->name('admin.funnel-templates.edit');
    Route::put('/admin/funnel-templates/{funnel_template}', [AdminFunnelTemplateController::class, 'update'])->name('admin.funnel-templates.update');
    Route::post('/admin/funnel-templates/{funnel_template}/publish', [AdminFunnelTemplateController::class, 'publish'])->name('admin.funnel-templates.publish');
    Route::post('/admin/funnel-templates/{funnel_template}/unpublish', [AdminFunnelTemplateController::class, 'unpublish'])->name('admin.funnel-templates.unpublish');
    Route::get('/admin/funnel-templates/{funnel_template}/preview/{step?}', [AdminFunnelTemplateController::class, 'preview'])->name('admin.funnel-templates.preview');
    Route::get('/admin/funnel-templates/{funnel_template}/test/{step?}', [AdminFunnelTemplateController::class, 'test'])->name('admin.funnel-templates.test');
    Route::post('/admin/funnel-templates/{funnel_template}/test/{step}/opt-in', [AdminFunnelTemplateController::class, 'testOptIn'])->name('admin.funnel-templates.test.optin');
    Route::post('/admin/funnel-templates/{funnel_template}/test/{step}/checkout', [AdminFunnelTemplateController::class, 'testCheckout'])->name('admin.funnel-templates.test.checkout');
    Route::post('/admin/funnel-templates/{funnel_template}/test/{step}/offer', [AdminFunnelTemplateController::class, 'testOffer'])->name('admin.funnel-templates.test.offer');
    Route::post('/admin/funnel-templates/{funnel_template}/builder/layout', [AdminFunnelTemplateController::class, 'saveLayout'])->name('admin.funnel-templates.builder.layout.save');
    Route::get('/admin/funnel-templates/{funnel_template}/builder/assets', [AdminFunnelTemplateController::class, 'builderAssets'])->name('admin.funnel-templates.builder.assets.index');
    Route::post('/admin/funnel-templates/{funnel_template}/builder/assets/delete', [AdminFunnelTemplateController::class, 'destroyBuilderAssets'])->name('admin.funnel-templates.builder.assets.destroy');
    Route::post('/admin/funnel-templates/{funnel_template}/builder/upload-image', [AdminFunnelTemplateController::class, 'uploadBuilderImage'])->name('admin.funnel-templates.builder.image.upload');
    Route::post('/admin/funnel-templates/{funnel_template}/steps', [AdminFunnelTemplateController::class, 'storeStep'])->name('admin.funnel-templates.steps.store');
    Route::put('/admin/funnel-templates/{funnel_template}/steps/{step}', [AdminFunnelTemplateController::class, 'updateStep'])->name('admin.funnel-templates.steps.update');
    Route::delete('/admin/funnel-templates/{funnel_template}/steps/{step}', [AdminFunnelTemplateController::class, 'destroyStep'])->name('admin.funnel-templates.steps.destroy');
    Route::post('/admin/funnel-templates/{funnel_template}/steps/{step}/versions', [AdminFunnelTemplateController::class, 'storeVersion'])->name('admin.funnel-templates.steps.versions.store');
    Route::post('/admin/funnel-templates/{funnel_template}/steps/reorder', [AdminFunnelTemplateController::class, 'reorderSteps'])->name('admin.funnel-templates.steps.reorder');

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
    Route::get('/admin/automation', [AutomationController::class, 'index'])->name('admin.automation.index');
    Route::post('/admin/automation/toggle', [AutomationController::class, 'toggle'])->name('admin.automation.toggle');
});

Route::middleware(['auth', 'tenant.subscription', 'role:sales-agent,marketing-manager,account-owner,finance'])->group(function () {
    Route::get('/dashboard/owner', [DashboardController::class, 'owner'])->middleware('role:account-owner')->name('dashboard.owner');
    Route::get('/dashboard/marketing', [DashboardController::class, 'marketing'])->middleware('role:marketing-manager')->name('dashboard.marketing');
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
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });

    Route::middleware(['role:account-owner,marketing-manager'])->group(function () {
        Route::get('/funnels', [FunnelController::class, 'index'])->name('funnels.index');
        Route::get('/funnels/create', [FunnelController::class, 'create'])->name('funnels.create');
        Route::get('/funnels/shared-templates', [FunnelController::class, 'sharedTemplates'])->name('funnels.shared-templates');
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

    Route::middleware(['role:account-owner'])->group(function () {
        Route::get('/automation', [AutomationController::class, 'index'])->name('automation.index');
        Route::post('/automation/toggle', [AutomationController::class, 'toggle'])->name('automation.toggle');
    });

    Route::middleware(['role:account-owner,finance'])->group(function () {
        Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');
    });
});

Route::middleware(['auth', 'role:customer'])->group(function () {
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
Route::post('/api/n8n/email-status', [N8nWebhookController::class, 'emailStatus'])->name('api.n8n.email-status');
Route::post('/api/n8n/lead-activity', [N8nAutomationController::class, 'leadActivity'])->name('api.n8n.lead-activity');
Route::post('/api/n8n/lead-score', [N8nAutomationController::class, 'leadScore'])->name('api.n8n.lead-score');
Route::post('/api/n8n/send-email', [N8nAutomationController::class, 'sendEmail'])->name('api.n8n.send-email');
Route::post('/api/n8n/send-sms', [N8nAutomationController::class, 'sendSms'])->name('api.n8n.send-sms');
Route::post('/api/n8n/agent-task', [N8nAutomationController::class, 'agentTask'])->name('api.n8n.agent-task');
Route::get('/api/n8n/invoice-status', [N8nAutomationController::class, 'invoiceStatus'])->name('api.n8n.invoice-status');
Route::post('/api/n8n/suspend-subscription', [N8nAutomationController::class, 'suspendSubscription'])->name('api.n8n.suspend-subscription');
Route::post('/api/n8n/payment-recovered', [N8nAutomationController::class, 'paymentRecovered'])->name('api.n8n.payment-recovered');
Route::post('/api/n8n/automation-log', [N8nAutomationController::class, 'automationLog'])->name('api.n8n.automation-log');
Route::get('/api/n8n/analytics-daily', [N8nAutomationController::class, 'analyticsDaily'])->name('api.n8n.analytics-daily');
Route::post('/api/n8n/analytics-store', [N8nAutomationController::class, 'analyticsStore'])->name('api.n8n.analytics-store');
Route::post('/api/n8n/send-owner-digest', [N8nAutomationController::class, 'sendOwnerDigest'])->name('api.n8n.send-owner-digest');
Route::post('/api/n8n/trial-inactive-recovery', [N8nAutomationController::class, 'trialInactiveRecovery'])->name('api.n8n.trial-inactive-recovery');
Route::post('/api/n8n/run-inactive-trial-recovery', [N8nAutomationController::class, 'runInactiveTrialRecovery'])->name('api.n8n.run-inactive-trial-recovery');
