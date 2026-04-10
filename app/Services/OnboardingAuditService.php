<?php

namespace App\Services;

use App\Models\OnboardingAuditLog;
use App\Models\SignupIntent;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class OnboardingAuditService
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function record(
        string $eventType,
        string $status,
        ?string $message = null,
        ?User $user = null,
        ?SignupIntent $signupIntent = null,
        array $context = [],
    ): void {
        try {
            OnboardingAuditLog::query()->create([
                'event_type' => $eventType,
                'status' => $status,
                'message' => $message,
                'user_id' => $user?->id,
                'signup_intent_id' => $signupIntent?->id,
                'context' => $context,
                'occurred_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed to persist onboarding audit log.', [
                'event_type' => $eventType,
                'status' => $status,
                'message' => $message,
                'exception' => $e->getMessage(),
            ]);
        }
    }
}

