<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\AdminController;

// Login & Logout routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


// -----------------------------
// Admin Dashboard — Super Admin Only
Route::middleware(['auth', 'role:super-admin'])->group(function () {

    Route::get('/admin/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');

    // ✅ TENANTS ROUTES
    Route::get('/admin/tenants', [TenantController::class, 'index'])
        ->name('admin.tenants.index');

    Route::get('/admin/tenants/create', [TenantController::class, 'create'])
        ->name('admin.tenants.create');

    Route::post('/admin/tenants', [TenantController::class, 'store'])
        ->name('admin.tenants.store');

    Route::get('/admin/tenants/{tenant}/edit', [TenantController::class, 'edit'])
        ->name('admin.tenants.edit');

    Route::put('/admin/tenants/{tenant}', [TenantController::class, 'update'])
        ->name('admin.tenants.update');

    Route::delete('/admin/tenants/{tenant}', [TenantController::class, 'destroy'])
        ->name('admin.tenants.destroy');

    // ✅ USERS (Super Admin View)
    Route::get('/admin/users', [UserController::class, 'adminIndex'])
        ->name('admin.users.index');

    // ✅ LEADS (Super Admin View)
    Route::get('/admin/leads', [LeadController::class, 'adminIndex'])
        ->name('admin.leads.index');
});


// -----------------------------
// Leads & Team Routes — Accessible by Sales Agent, Marketing Manager, Account Owner
Route::middleware(['auth', 'role:sales-agent,marketing-manager,account-owner,finance'])->group(function () {
    
    // Role-Specific Dashboards
    Route::get('/dashboard/owner', function () { return view('dashboard.account-owner'); })->middleware('role:account-owner')->name('dashboard.owner');
    Route::get('/dashboard/marketing', function () { return view('dashboard.marketing'); })->middleware('role:marketing-manager')->name('dashboard.marketing');
    Route::get('/dashboard/sales', function () { return view('dashboard.sales'); })->middleware('role:sales-agent')->name('dashboard.sales');
    Route::get('/dashboard/finance', function () { return view('dashboard.finance'); })->middleware('role:finance')->name('dashboard.finance');

    Route::get('/leads', [LeadController::class, 'index'])->name('leads.index');
    Route::get('/leads/create', [LeadController::class, 'create'])->name('leads.create');
    Route::post('/leads', [LeadController::class, 'store'])->name('leads.store');
    Route::get('/leads/{lead}/edit', [LeadController::class, 'edit'])->name('leads.edit');
    Route::put('/leads/{lead}', [LeadController::class, 'update'])->name('leads.update');
    Route::delete('/leads/{lead}', [LeadController::class, 'destroy'])->name('leads.destroy');
    
    // Activities
    Route::post('/leads/{lead}/activities', [LeadController::class, 'storeActivity'])->name('leads.activities.store');

    // Team Management (Account Owner only)
    Route::middleware(['role:account-owner'])->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });
});

