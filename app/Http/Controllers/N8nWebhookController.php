<?php

namespace App\Http\Controllers;

use App\Models\SignupIntent;
use App\Models\User;
use App\Services\OnboardingAuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class N8nWebhookController extends Controller
{
    public function emailStatus(Request $request)
    {
        $expectedToken = (string) config('services.n8n.callback_bearer_token');
        if ($expectedToken !== '') {
            $authorization = (string) $request->header('Authorization');
            $receivedToken = trim(str_ireplace('Bearer', '', $authorization));
            if (! hash_equals($expectedToken, $receivedToken)) {
                return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
            }
        }

        $validated = $request->validate([
            'event_name' => 'required|string|max:120',
            'email' => 'required|email|max:255',
            'user_id' => 'nullable|integer',
            'status' => 'required|in:sent,failed',
            'sent_at' => 'nullable|date',
        ]);

        $user = null;
        if (! empty($validated['user_id'])) {
            $user = User::query()->find((int) $validated['user_id']);
        }
        if (! $user) {
            $user = User::query()->where('email', $validated['email'])->first();
        }

        if ($validated['status'] === 'sent') {
            if ($user && $user->activation_state !== 'active') {
                $user->update(['activation_state' => 'email_sent']);
            }

            $intent = SignupIntent::query()
                ->where('email', $validated['email'])
                ->whereIn('status', ['pending', 'paid', 'completed'])
                ->latest('id')
                ->first();

            if ($intent) {
                $intent->update([
                    'email_sent_at' => now(),
                    'email_delivery_status' => 'sent',
                    'email_last_attempt_at' => now(),
                    'email_last_error' => null,
                ]);
                if ($intent->lifecycle_state === SignupIntent::STATE_ACCOUNT_CREATED_PENDING_ACTIVATION) {
                    $intent->transitionTo(SignupIntent::STATE_EMAIL_SENT);
                }
            }
            app(OnboardingAuditService::class)->record(
                'onboarding_email_callback',
                'success',
                'n8n callback marked email as sent.',
                $user,
                $intent,
                ['event_name' => $validated['event_name']]
            );
        } else {
            $intent = SignupIntent::query()
                ->where('email', $validated['email'])
                ->whereIn('status', ['pending', 'paid', 'completed'])
                ->latest('id')
                ->first();
            if ($intent) {
                $intent->update([
                    'email_delivery_status' => 'failed',
                    'email_last_attempt_at' => now(),
                    'email_last_error' => 'n8n callback reported failure',
                ]);
            }
            app(OnboardingAuditService::class)->record(
                'onboarding_email_callback',
                'failed',
                'n8n callback reported email delivery failure.',
                $user,
                $intent,
                ['event_name' => $validated['event_name']]
            );
            Log::warning('n8n reported email delivery failure.', $validated);
        }

        return response()->json(['ok' => true]);
    }
}
