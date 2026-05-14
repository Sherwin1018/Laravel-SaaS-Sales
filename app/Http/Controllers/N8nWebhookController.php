<?php

namespace App\Http\Controllers;

use App\Models\ExternalDeliveryLog;
use App\Models\SignupIntent;
use App\Models\User;
use App\Services\DeliveryLogService;
use App\Services\OnboardingAuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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
            'idempotency_key' => 'nullable|string|max:191',
            'provider' => 'nullable|string|max:80',
            'response_code' => 'nullable|integer',
            'error_message' => 'nullable|string',
        ]);

        $user = null;
        if (! empty($validated['user_id'])) {
            $user = User::query()->find((int) $validated['user_id']);
        }
        if (! $user) {
            $user = User::query()->where('email', $validated['email'])->first();
        }

        $this->syncEmailDeliveryLog($validated, $user);

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

    /**
     * @param  array<string, mixed>  $validated
     */
    private function syncEmailDeliveryLog(array $validated, ?User $user): void
    {
        $callbackAt = $this->parseCallbackTimestamp($validated['sent_at'] ?? null);
        $idempotencyKey = trim((string) ($validated['idempotency_key'] ?? ''));

        $query = ExternalDeliveryLog::query()
            ->where('channel', 'email')
            ->where('event_name', (string) $validated['event_name'])
            ->where('recipient', (string) $validated['email']);

        if ($idempotencyKey !== '') {
            $query->where('idempotency_key', $idempotencyKey);
        }

        if ($user) {
            $query->where(function ($builder) use ($user) {
                $builder->where('user_id', $user->id)
                    ->orWhereNull('user_id');
            });
        }

        /** @var ExternalDeliveryLog|null $log */
        $log = $query->latest('id')->first();

        $callbackMeta = array_filter([
            'status' => (string) $validated['status'],
            'sent_at' => $callbackAt?->toIso8601String(),
            'idempotency_key' => $idempotencyKey !== '' ? $idempotencyKey : null,
            'response_code' => isset($validated['response_code']) ? (int) $validated['response_code'] : null,
            'error_message' => trim((string) ($validated['error_message'] ?? '')) ?: null,
        ], static fn ($value) => $value !== null && $value !== '');

        if ($log) {
            $meta = is_array($log->meta) ? $log->meta : [];
            $meta['n8n_callback'] = $callbackMeta;

            $log->forceFill([
                'user_id' => $log->user_id ?: $user?->id,
                'provider' => trim((string) ($validated['provider'] ?? '')) ?: ($log->provider ?: 'n8n'),
                'status' => (string) $validated['status'],
                'response_code' => isset($validated['response_code']) ? (int) $validated['response_code'] : $log->response_code,
                'error_message' => $validated['status'] === 'failed'
                    ? (trim((string) ($validated['error_message'] ?? '')) ?: 'n8n callback reported email delivery failure')
                    : null,
                'meta' => $meta,
                'sent_at' => $validated['status'] === 'sent' ? ($callbackAt ?? now()) : null,
            ])->save();

            return;
        }

        app(DeliveryLogService::class)->record('email', [
            'tenant_id' => $user?->tenant_id,
            'user_id' => $user?->id,
            'event_name' => (string) $validated['event_name'],
            'recipient' => (string) $validated['email'],
            'provider' => trim((string) ($validated['provider'] ?? '')) ?: 'n8n',
            'status' => (string) $validated['status'],
            'response_code' => isset($validated['response_code']) ? (int) $validated['response_code'] : null,
            'error_message' => $validated['status'] === 'failed'
                ? (trim((string) ($validated['error_message'] ?? '')) ?: 'n8n callback reported email delivery failure')
                : null,
            'idempotency_key' => $idempotencyKey !== '' ? $idempotencyKey : null,
            'is_billable' => true,
            'meta' => [
                'n8n_callback' => $callbackMeta,
                'callback_only' => true,
            ],
        ]);
    }

    private function parseCallbackTimestamp(mixed $value): ?Carbon
    {
        $timestamp = trim((string) $value);
        if ($timestamp === '') {
            return null;
        }

        try {
            return Carbon::parse($timestamp);
        } catch (\Throwable) {
            return null;
        }
    }
}
