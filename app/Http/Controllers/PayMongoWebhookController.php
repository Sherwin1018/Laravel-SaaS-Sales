<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\SignupIntent;
use App\Models\WebhookReceipt;
use App\Services\CommissionService;
use App\Services\FunnelTrackingService;
use App\Services\SignupOnboardingService;
use App\Services\SubscriptionLifecycleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PayMongoWebhookController extends Controller
{
    public function __invoke(Request $request)
    {
        if (! $this->signatureValid($request)) {
            Log::warning('PayMongo webhook rejected: invalid signature');

            return response()->json(['ok' => false], 401);
        }

        $payload = $request->json()->all();
        $eventType = data_get($payload, 'data.attributes.type');
        if (! is_string($eventType)) {
            return response()->json(['ok' => true]);
        }

        $eventId = trim((string) data_get($payload, 'data.id'));
        if ($eventId === '') {
            $eventId = sha1($request->getContent());
        }

        $payloadHash = sha1($request->getContent());

        try {
            $duplicate = DB::transaction(function () use ($eventId, $eventType, $payloadHash, $payload) {
                $receipt = WebhookReceipt::query()
                    ->where('provider', 'paymongo')
                    ->where('event_id', $eventId)
                    ->lockForUpdate()
                    ->first();

                if ($receipt && $receipt->status === 'processed') {
                    $receipt->update([
                        'attempts' => (int) $receipt->attempts + 1,
                        'last_error' => $receipt->payload_hash !== $payloadHash
                            ? 'Duplicate event ID received with a different payload hash.'
                            : null,
                        'meta' => array_merge(is_array($receipt->meta) ? $receipt->meta : [], [
                            'latest_payload' => $payload,
                            'duplicate' => true,
                            'payload_hash_mismatch' => $receipt->payload_hash !== $payloadHash,
                        ]),
                    ]);

                    return true;
                }

                if ($receipt) {
                    if ($receipt->payload_hash !== $payloadHash) {
                        $receipt->update([
                            'status' => 'failed',
                            'attempts' => (int) $receipt->attempts + 1,
                            'last_error' => 'Payload hash mismatch for an existing PayMongo event ID.',
                            'meta' => array_merge(is_array($receipt->meta) ? $receipt->meta : [], [
                                'latest_payload' => $payload,
                                'payload_hash_mismatch' => true,
                            ]),
                        ]);

                        return true;
                    }

                    $receipt->update([
                        'event_type' => $eventType,
                        'payload_hash' => $payloadHash,
                        'status' => 'processing',
                        'attempts' => (int) $receipt->attempts + 1,
                        'last_error' => null,
                        'meta' => ['payload' => $payload],
                    ]);
                } else {
                    $receipt = WebhookReceipt::query()->create([
                        'provider' => 'paymongo',
                        'event_id' => $eventId,
                        'event_type' => $eventType,
                        'payload_hash' => $payloadHash,
                        'status' => 'processing',
                        'attempts' => 1,
                        'meta' => ['payload' => $payload],
                    ]);
                }

                $eventData = data_get($payload, 'data.attributes.data');
                if ($eventType === 'checkout_session.payment.paid') {
                    $this->handleCheckoutSessionPaid(is_array($eventData) ? $eventData : []);
                } elseif ($eventType === 'payment.paid') {
                    $this->handlePaymentPaid(is_array($eventData) ? $eventData : []);
                } elseif ($eventType === 'payment.failed') {
                    $this->handlePaymentFailed(is_array($eventData) ? $eventData : []);
                }

                $receipt->update([
                    'status' => 'processed',
                    'processed_at' => now(),
                    'last_error' => null,
                ]);

                return false;
            });

            return response()->json(['ok' => true, 'duplicate' => $duplicate]);
        } catch (\Throwable $e) {
            Log::warning('PayMongo webhook processing failed.', [
                'event_id' => $eventId,
                'event_type' => $eventType,
                'message' => $e->getMessage(),
            ]);

            WebhookReceipt::query()->updateOrCreate(
                [
                    'provider' => 'paymongo',
                    'event_id' => $eventId,
                ],
                [
                    'event_type' => $eventType,
                    'payload_hash' => $payloadHash,
                    'status' => 'failed',
                    'last_error' => $e->getMessage(),
                    'meta' => ['payload' => $payload],
                ]
            );

            return response()->json(['ok' => false], 500);
        }
    }

    private function signatureValid(Request $request): bool
    {
        $secret = config('services.paymongo.webhook_secret');
        if (! is_string($secret) || $secret === '') {
            return true;
        }

        $header = $request->header('Paymongo-Signature');
        if (! is_string($header) || $header === '') {
            return false;
        }

        $expected = hash_hmac('sha256', $request->getContent(), $secret);

        return hash_equals($expected, $header);
    }

    private function handleCheckoutSessionPaid(array $session): void
    {
        $id = isset($session['id']) ? (string) $session['id'] : '';
        if ($id === '' || ! str_starts_with($id, 'cs_')) {
            return;
        }

        $payment = Payment::query()->where('provider', 'paymongo')->where('provider_reference', $id)->first();
        $method = $this->extractPaymentMethodFromSession($session);
        if ($payment && $payment->status !== 'paid') {
            $payment->update([
                'status' => 'paid',
                'payment_method' => $method ?? $payment->payment_method,
                'payment_date' => now()->toDateString(),
            ]);
            app(FunnelTrackingService::class)->trackPaymentPaid($payment->fresh(), ['source' => 'paymongo_webhook.checkout_session_paid']);
            app(CommissionService::class)->syncPayment($payment->fresh());
        }

        $metadata = data_get($session, 'attributes.metadata');
        if (is_array($metadata) && ($metadata['flow'] ?? null) === 'trial_upgrade' && $payment) {
            $this->activateTrialUpgrade($payment, (string) ($metadata['plan_code'] ?? ''), $method);
        }

        $signupIntent = SignupIntent::query()->where('provider', 'paymongo')->where('provider_reference', $id)->first();
        if ($signupIntent) {
            $this->completeSignupIntent(
                $signupIntent,
                $method,
                is_array($metadata) ? (string) ($metadata['flow'] ?? '') : '',
                is_array($metadata) ? (string) ($metadata['google_id'] ?? '') : '',
            );
        }
    }

    private function handlePaymentPaid(array $paymentResource): void
    {
        $meta = data_get($paymentResource, 'attributes.metadata');
        if (! is_array($meta)) {
            return;
        }

        $paymentId = isset($meta['payment_id']) ? (int) $meta['payment_id'] : 0;
        if ($paymentId <= 0) {
            return;
        }

        $source = data_get($paymentResource, 'attributes.source');
        $method = is_array($source) ? ($source['type'] ?? null) : null;

        $payment = Payment::query()->where('id', $paymentId)->where('provider', 'paymongo')->first();
        if ($payment && $payment->status !== 'paid') {
            $payment->update([
                'status' => 'paid',
                'payment_method' => is_string($method) ? $method : $payment->payment_method,
                'payment_date' => now()->toDateString(),
            ]);
            app(FunnelTrackingService::class)->trackPaymentPaid($payment->fresh(), ['source' => 'paymongo_webhook.payment_paid']);
            app(CommissionService::class)->syncPayment($payment->fresh());
        }

        if (($meta['flow'] ?? null) === 'trial_upgrade' && $payment) {
            $this->activateTrialUpgrade($payment, (string) ($meta['plan_code'] ?? ''), is_string($method) ? $method : null);
        }

        $signupIntentId = isset($meta['signup_intent_id']) ? (int) $meta['signup_intent_id'] : 0;
        if ($signupIntentId > 0) {
            $signupIntent = SignupIntent::query()->find($signupIntentId);
            if ($signupIntent) {
                $this->completeSignupIntent(
                    $signupIntent,
                    is_string($method) ? $method : null,
                    (string) ($meta['flow'] ?? ''),
                    (string) ($meta['google_id'] ?? ''),
                );
            }
        }
    }

    private function handlePaymentFailed(array $paymentResource): void
    {
        $meta = data_get($paymentResource, 'attributes.metadata');
        if (! is_array($meta)) {
            return;
        }

        $paymentId = isset($meta['payment_id']) ? (int) $meta['payment_id'] : 0;
        if ($paymentId <= 0) {
            return;
        }

        $payment = Payment::query()->where('id', $paymentId)->where('provider', 'paymongo')->first();
        if (! $payment) {
            $signupIntentId = isset($meta['signup_intent_id']) ? (int) $meta['signup_intent_id'] : 0;
            if ($signupIntentId > 0) {
                $signupIntent = SignupIntent::query()->find($signupIntentId);
                if ($signupIntent && $signupIntent->status !== 'completed') {
                    app(SignupOnboardingService::class)->markFailed($signupIntent);
                }
            }

            return;
        }

        if ($payment->isPlatformSubscription()) {
            app(SubscriptionLifecycleService::class)->markPaymentFailed($payment);

            return;
        }

        $payment->update([
            'status' => 'failed',
            'payment_date' => $payment->payment_date ?: now()->toDateString(),
        ]);
        app(CommissionService::class)->reverseForPayment($payment, 'paymongo_payment_failed');
    }

    private function completeSignupIntent(
        SignupIntent $signupIntent,
        ?string $method = null,
        string $flow = '',
        string $googleId = '',
    ): void
    {
        if ($signupIntent->status === 'completed') {
            return;
        }

        try {
            $service = app(SignupOnboardingService::class);
            $signupIntent = $service->markPaid($signupIntent, $method);
            $service->finalize($signupIntent, [
                'auto_activate' => $flow === 'signup_google',
                'google_id' => $flow === 'signup_google' ? $googleId : null,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Signup intent finalization failed', [
                'signup_intent_id' => $signupIntent->id,
                'message' => $e->getMessage(),
            ]);
        }
    }

    private function extractPaymentMethodFromSession(array $session): ?string
    {
        $payments = data_get($session, 'attributes.payments');
        if (! is_array($payments) || $payments === []) {
            return null;
        }

        $first = $payments[0] ?? null;
        if (! is_array($first)) {
            return null;
        }

        $type = data_get($first, 'attributes.source.type');

        return is_string($type) ? $type : null;
    }

    private function activateTrialUpgrade(Payment $payment, string $planCode, ?string $method = null): void
    {
        if ($planCode === '') {
            return;
        }

        try {
            $service = app(SignupOnboardingService::class);
            $plan = $service->findPlan($planCode);
            $service->activateTenantSubscriptionFromPayment($payment, $plan, $method);
        } catch (\Throwable $e) {
            Log::warning('Trial upgrade activation failed', [
                'payment_id' => $payment->id,
                'plan_code' => $planCode,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
