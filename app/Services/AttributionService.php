<?php

namespace App\Services;

use App\Models\Funnel;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\SignupIntent;
use App\Models\User;
use Illuminate\Http\Request;

class AttributionService
{
    public function captureFunnelRequest(Request $request, Funnel $funnel): array
    {
        $sessionKey = $this->funnelSessionKey($funnel->id);
        $existing = $this->normalizePayload($request->session()->get($sessionKey, []));
        $incoming = $this->payloadFromRequest($request);

        $payload = $this->mergePayloads($existing, $incoming);
        $request->session()->put($sessionKey, $payload);

        return $payload;
    }

    public function funnelPayload(Request $request, Funnel $funnel): array
    {
        $sessionKey = $this->funnelSessionKey($funnel->id);

        return $this->mergePayloads(
            $this->normalizePayload($request->session()->get($sessionKey, [])),
            $this->payloadFromRequest($request)
        );
    }

    public function captureSignupRequest(Request $request): array
    {
        $sessionKey = $this->signupSessionKey();
        $existing = $this->normalizePayload($request->session()->get($sessionKey, []));
        $incoming = $this->payloadFromRequest($request);

        $payload = $this->mergePayloads($existing, $incoming);
        $request->session()->put($sessionKey, $payload);

        return $payload;
    }

    public function signupPayload(Request $request): array
    {
        return $this->mergePayloads(
            $this->normalizePayload($request->session()->get($this->signupSessionKey(), [])),
            $this->payloadFromRequest($request)
        );
    }

    public function applyToLead(Lead $lead, array $payload): void
    {
        $payload = $this->normalizePayload($payload);
        if ($payload === []) {
            return;
        }

        $lead->source_campaign = $payload['source_campaign'] ?: $lead->source_campaign;
        $lead->source_platform = $payload['source_platform'] ?: $lead->source_platform;
        $lead->source_medium = $payload['source_medium'] ?: $lead->source_medium;
        $lead->source_content = $payload['source_content'] ?: $lead->source_content;
        $lead->referrer_user_id = $payload['referrer_user_id'] ?: $lead->referrer_user_id;
        $lead->referral_code_snapshot = $payload['referral_code_snapshot'] ?: $lead->referral_code_snapshot;
        $lead->save();
    }

    public function applyToSignupIntent(SignupIntent $intent, array $payload): void
    {
        $payload = $this->normalizePayload($payload);
        if ($payload === []) {
            return;
        }

        $intent->source_platform = $payload['source_platform'] ?: $intent->source_platform;
        $intent->source_medium = $payload['source_medium'] ?: $intent->source_medium;
        $intent->source_campaign = $payload['source_campaign'] ?: $intent->source_campaign;
        $intent->source_content = $payload['source_content'] ?: $intent->source_content;
        $intent->referrer_user_id = $payload['referrer_user_id'] ?: $intent->referrer_user_id;
        $intent->referral_code_snapshot = $payload['referral_code_snapshot'] ?: $intent->referral_code_snapshot;
        $intent->save();
    }

    public function applyToPayment(Payment $payment, array $payload, ?Funnel $funnel = null, ?Lead $lead = null): void
    {
        $payload = $this->normalizePayload($payload);
        $lead ??= $payment->lead;
        $funnel ??= $payment->funnel;

        $payment->source_funnel_template_id = $payment->source_funnel_template_id
            ?: (int) ($funnel?->source_template_id ?? 0)
            ?: null;
        $payment->source_platform = $payload['source_platform'] ?: ($lead?->source_platform ?: $payment->source_platform);
        $payment->source_medium = $payload['source_medium'] ?: ($lead?->source_medium ?: $payment->source_medium);
        $payment->source_campaign = $payload['source_campaign'] ?: ($lead?->source_campaign ?: $payment->source_campaign);
        $payment->source_content = $payload['source_content'] ?: ($lead?->source_content ?: $payment->source_content);
        $payment->referrer_user_id = $payload['referrer_user_id'] ?: ($lead?->referrer_user_id ?: $payment->referrer_user_id);
        $payment->referral_code_snapshot = $payload['referral_code_snapshot'] ?: ($lead?->referral_code_snapshot ?: $payment->referral_code_snapshot);
        $payment->assigned_sales_user_id = $payment->assigned_sales_user_id ?: (int) ($lead?->assigned_to ?? 0) ?: null;
        $payment->save();
    }

    /**
     * @return array<string, int|string|null>
     */
    public function payloadFromRequest(Request $request): array
    {
        $referralCode = $this->normalizeNullableString(
            $request->query('ref', $request->query('affiliate', $request->query('ref_code')))
        , 40);

        $referrer = null;
        if ($referralCode) {
            $referrer = User::query()->where('referral_code', $referralCode)->first();
        }

        return $this->normalizePayload([
            'source_platform' => $this->normalizeNullableString(
                $request->query('utm_source', $request->query('source'))
            , 60),
            'source_medium' => $this->normalizeNullableString(
                $request->query('utm_medium', $request->query('medium'))
            , 60),
            'source_campaign' => $this->normalizeNullableString(
                $request->query('utm_campaign', $request->query('campaign'))
            , 120),
            'source_content' => $this->normalizeNullableString(
                $request->query('utm_content', $request->query('content'))
            , 150),
            'referrer_user_id' => $referrer?->id,
            'referral_code_snapshot' => $referrer?->referral_code ?: $referralCode,
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, int|string|null>
     */
    public function normalizePayload(array $payload): array
    {
        return [
            'source_platform' => $this->normalizeNullableString($payload['source_platform'] ?? null, 60),
            'source_medium' => $this->normalizeNullableString($payload['source_medium'] ?? null, 60),
            'source_campaign' => $this->normalizeNullableString($payload['source_campaign'] ?? null, 120),
            'source_content' => $this->normalizeNullableString($payload['source_content'] ?? null, 150),
            'referrer_user_id' => ! empty($payload['referrer_user_id']) ? (int) $payload['referrer_user_id'] : null,
            'referral_code_snapshot' => $this->normalizeNullableString($payload['referral_code_snapshot'] ?? null, 40),
        ];
    }

    /**
     * @param  array<string, int|string|null>  $base
     * @param  array<string, int|string|null>  $incoming
     * @return array<string, int|string|null>
     */
    private function mergePayloads(array $base, array $incoming): array
    {
        $merged = $base;
        foreach ($incoming as $key => $value) {
            if ($value !== null && $value !== '') {
                $merged[$key] = $value;
            }
        }

        return $this->normalizePayload($merged);
    }

    private function funnelSessionKey(int $funnelId): string
    {
        return 'funnel_attribution.' . $funnelId;
    }

    private function signupSessionKey(): string
    {
        return 'signup_attribution';
    }

    private function normalizeNullableString(mixed $value, int $max): ?string
    {
        $string = trim((string) $value);

        return $string !== '' ? mb_substr($string, 0, $max) : null;
    }
}
