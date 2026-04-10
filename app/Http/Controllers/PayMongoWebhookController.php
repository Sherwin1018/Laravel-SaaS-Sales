<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\SignupIntent;
use App\Services\FunnelTrackingService;
use App\Services\SignupOnboardingService;
use App\Services\SubscriptionLifecycleService;
use Illuminate\Http\Request;
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

        $eventData = data_get($payload, 'data.attributes.data');
        if ($eventType === 'checkout_session.payment.paid') {
            $this->handleCheckoutSessionPaid(is_array($eventData) ? $eventData : []);
        } elseif ($eventType === 'payment.paid') {
            $this->handlePaymentPaid(is_array($eventData) ? $eventData : []);
        } elseif ($eventType === 'payment.failed') {
            $this->handlePaymentFailed(is_array($eventData) ? $eventData : []);
        }

        return response()->json(['ok' => true]);
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
        }

        $metadata = data_get($session, 'attributes.metadata');
        if (is_array($metadata) && ($metadata['flow'] ?? null) === 'trial_upgrade' && $payment) {
            $this->activateTrialUpgrade($payment, (string) ($metadata['plan_code'] ?? ''), $method);
        }

        $signupIntent = SignupIntent::query()->where('provider', 'paymongo')->where('provider_reference', $id)->first();
        if ($signupIntent) {
            $this->completeSignupIntent($signupIntent, $method);
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
        }

        if (($meta['flow'] ?? null) === 'trial_upgrade' && $payment) {
            $this->activateTrialUpgrade($payment, (string) ($meta['plan_code'] ?? ''), is_string($method) ? $method : null);
        }

        $signupIntentId = isset($meta['signup_intent_id']) ? (int) $meta['signup_intent_id'] : 0;
        if ($signupIntentId > 0) {
            $signupIntent = SignupIntent::query()->find($signupIntentId);
            if ($signupIntent) {
                $this->completeSignupIntent($signupIntent, is_string($method) ? $method : null);
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
    }

    private function completeSignupIntent(SignupIntent $signupIntent, ?string $method = null): void
    {
        if ($signupIntent->status === 'completed') {
            return;
        }

        try {
            $service = app(SignupOnboardingService::class);
            $signupIntent = $service->markPaid($signupIntent, $method);
            $service->finalize($signupIntent);
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
