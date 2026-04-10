<?php

use App\Http\Controllers\Api\TenantAutomationRunController;
use App\Http\Controllers\Api\EmailStatusController;
use Illuminate\Support\Facades\Route;

Route::post('automation/tenant/run', [TenantAutomationRunController::class, 'run']);
// Alias so n8n can call POST /api/automation (same handler as /api/automation/tenant/run)
Route::post('automation', [TenantAutomationRunController::class, 'run']);

// Email status endpoint for n8n to notify Laravel of email delivery
Route::post('n8n/email-status', [EmailStatusController::class, 'update']);
