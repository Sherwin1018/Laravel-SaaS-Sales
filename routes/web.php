<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FunnelController;
use App\Http\Controllers\FunnelPortalController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

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

Route::middleware(['auth', 'role:super-admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');

    Route::get('/admin/tenants', [TenantController::class, 'index'])->name('admin.tenants.index');
    Route::get('/admin/tenants/create', [TenantController::class, 'create'])->name('admin.tenants.create');
    Route::post('/admin/tenants', [TenantController::class, 'store'])->name('admin.tenants.store');
    Route::get('/admin/tenants/{tenant}/edit', [TenantController::class, 'edit'])->name('admin.tenants.edit');
    Route::put('/admin/tenants/{tenant}', [TenantController::class, 'update'])->name('admin.tenants.update');
    Route::delete('/admin/tenants/{tenant}', [TenantController::class, 'destroy'])->name('admin.tenants.destroy');

    Route::get('/admin/users', [UserController::class, 'adminIndex'])->name('admin.users.index');
    Route::patch('/admin/users/{user}/status', [UserController::class, 'toggleOwnerStatus'])->name('admin.users.status');
    Route::get('/admin/leads', [LeadController::class, 'adminIndex'])->name('admin.leads.index');
});

Route::middleware(['auth', 'role:sales-agent,marketing-manager,account-owner,finance'])->group(function () {
    Route::get('/dashboard/owner', [DashboardController::class, 'owner'])->middleware('role:account-owner')->name('dashboard.owner');
    Route::get('/dashboard/marketing', [DashboardController::class, 'marketing'])->middleware('role:marketing-manager')->name('dashboard.marketing');
    Route::get('/dashboard/sales', [DashboardController::class, 'sales'])->middleware('role:sales-agent')->name('dashboard.sales');
    Route::get('/dashboard/finance', [DashboardController::class, 'finance'])->middleware('role:finance')->name('dashboard.finance');

    Route::get('/leads', [LeadController::class, 'index'])->name('leads.index');
    Route::get('/leads/create', [LeadController::class, 'create'])->name('leads.create');
    Route::post('/leads', [LeadController::class, 'store'])->name('leads.store');
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
        Route::post('/funnels', [FunnelController::class, 'store'])->name('funnels.store');
        Route::get('/funnels/{funnel}/edit', [FunnelController::class, 'edit'])->name('funnels.edit');
        Route::get('/funnels/{funnel}/preview/{step?}', [FunnelController::class, 'preview'])->name('funnels.preview');
        Route::put('/funnels/{funnel}', [FunnelController::class, 'update'])->name('funnels.update');
        Route::post('/funnels/{funnel}/builder/layout', [FunnelController::class, 'saveLayout'])->name('funnels.builder.layout.save');
        Route::post('/funnels/{funnel}/builder/upload-image', [FunnelController::class, 'uploadBuilderImage'])->name('funnels.builder.image.upload');
        Route::post('/funnels/{funnel}/publish', [FunnelController::class, 'publish'])->name('funnels.publish');
        Route::post('/funnels/{funnel}/unpublish', [FunnelController::class, 'unpublish'])->name('funnels.unpublish');
        Route::delete('/funnels/{funnel}', [FunnelController::class, 'destroy'])->name('funnels.destroy');
        Route::post('/funnels/{funnel}/steps', [FunnelController::class, 'storeStep'])->name('funnels.steps.store');
        Route::put('/funnels/{funnel}/steps/{step}', [FunnelController::class, 'updateStep'])->name('funnels.steps.update');
        Route::delete('/funnels/{funnel}/steps/{step}', [FunnelController::class, 'destroyStep'])->name('funnels.steps.destroy');
        Route::post('/funnels/{funnel}/steps/reorder', [FunnelController::class, 'reorderSteps'])->name('funnels.steps.reorder');

    });

    Route::middleware(['role:account-owner,finance'])->group(function () {
        Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');
    });
});

Route::middleware(['auth', 'role:customer'])->group(function () {
    Route::get('/dashboard/customer', [DashboardController::class, 'customer'])->name('dashboard.customer');
});

Route::get('/f/{funnelSlug}/{stepSlug?}', [FunnelPortalController::class, 'show'])->name('funnels.portal.step');
Route::get('/funnel/{funnelSlug}/{stepSlug?}', [FunnelPortalController::class, 'show'])->name('funnels.portal.step.alias');
Route::post('/f/{funnelSlug}/{stepSlug}/opt-in', [FunnelPortalController::class, 'optIn'])->name('funnels.portal.optin');
Route::post('/f/{funnelSlug}/{stepSlug}/checkout', [FunnelPortalController::class, 'checkout'])->name('funnels.portal.checkout');
Route::post('/f/{funnelSlug}/{stepSlug}/offer', [FunnelPortalController::class, 'offer'])->name('funnels.portal.offer');
