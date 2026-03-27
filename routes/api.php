<?php

use App\Http\Controllers\Api\TenantAutomationRunController;
use Illuminate\Support\Facades\Route;

Route::post('automation/tenant/run', [TenantAutomationRunController::class, 'run']);
// Alias so n8n can call POST /api/automation (same handler as /api/automation/tenant/run)
Route::post('automation', [TenantAutomationRunController::class, 'run']);
