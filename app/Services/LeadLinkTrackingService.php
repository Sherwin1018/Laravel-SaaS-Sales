<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class LeadLinkTrackingService
{
    private function trackingSecret(): string
    {
        $secret = env('LINK_TRACKING_SECRET');
        if (is_string($secret) && trim($secret) !== '') {
            return trim($secret);
        }

        $appKey = config('app.key');
        if (is_string($appKey) && trim($appKey) !== '') {
            return trim($appKey);
        }

        // In practice app.key should exist; throw to avoid generating unverifiable tokens.
        throw new \RuntimeException('Missing LINK_TRACKING_SECRET (and app.key is empty).');
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $data): string|false
    {
        $b64 = strtr($data, '-_', '+/');
        $padding = strlen($b64) % 4;
        if ($padding > 0) {
            $b64 .= str_repeat('=', 4 - $padding);
        }
        return base64_decode($b64, true);
    }

    public function generateToken(array $claims): string
    {
        $claims['v'] = 1;
        $claims['iat'] = time();

        $json = json_encode($claims, JSON_UNESCAPED_SLASHES);
        if (!is_string($json) || $json === '') {
            throw new \RuntimeException('Failed to encode tracking token claims.');
        }

        $payloadPart = $this->base64UrlEncode($json);
        $secret = $this->trackingSecret();

        // Sign the payload part (not the raw JSON) for stable length.
        $signature = hash_hmac('sha256', $payloadPart, $secret, true);
        $signaturePart = $this->base64UrlEncode($signature);

        return $payloadPart . '.' . $signaturePart;
    }

    public function decodeToken(string $token): ?array
    {
        $parts = explode('.', $token, 2);
        if (count($parts) !== 2) {
            return null;
        }

        [$payloadPart, $signaturePart] = $parts;
        $secret = $this->trackingSecret();

        $expectedSig = hash_hmac('sha256', $payloadPart, $secret, true);
        $expectedPart = $this->base64UrlEncode($expectedSig);

        if (!hash_equals($expectedPart, $signaturePart)) {
            return null;
        }

        $json = $this->base64UrlDecode($payloadPart);
        if ($json === false) {
            return null;
        }

        $decoded = json_decode($json, true);
        if (!is_array($decoded)) {
            return null;
        }

        return $decoded;
    }

    private function inferLinkNameFromUrl(string $destinationUrl): string
    {
        $path = parse_url($destinationUrl, PHP_URL_PATH) ?: '';
        $path = trim($path);
        if ($path === '' || $path === '/') {
            return 'Link';
        }

        $base = basename($path);
        $base = preg_replace('/[^a-z0-9\\-_ ]/i', ' ', (string) $base) ?: '';
        $base = trim(preg_replace('/\\s+/', ' ', $base));
        return $base !== '' ? mb_strimwidth($base, 0, 200) : 'Link';
    }

    private function normalizeLinkName(?string $name, string $fallbackUrl): ?string
    {
        $name = $name !== null ? trim($name) : '';
        if ($name === '') {
            return $this->inferLinkNameFromUrl($fallbackUrl);
        }

        $name = preg_replace('/\\s+/', ' ', $name) ?? '';
        return mb_strimwidth($name, 0, 200);
    }

    /**
     * Rewrites outbound links to our tracking redirect route.
     *
     * Supported cases:
     * 1) HTML anchor tags: <a href="https://...">...</a>
     * 2) Plain URLs in email bodies (no <a> tags): https://example.com/...
     *
     * We only rewrite http/https links (mailto/tel are skipped).
     */
    public function rewriteHtmlLinks(string $html, array $context): string
    {
        if (trim($html) === '') {
            return $html;
        }

        // If lead_id is missing, we cannot produce a meaningful token.
        if (empty($context['lead_id']) || empty($context['tenant_id'])) {
            return $html;
        }

        $baseUrl = rtrim((string) url('/'), '/');

        // If the email contains anchor tags, only rewrite those anchors.
        // Rewriting plain URLs when anchor tags exist can create nested <a> tags.
        $containsAnchors = stripos($html, '<a ') !== false;
        if ($containsAnchors) {
            // Matches: <a ... href="https://example.com/path" ...>...</a>
            $pattern = '/<a\\b([^>]*?)href=(["\'])(https?:\\/\\/[^"\']+)\\2([^>]*)>(.*?)<\\/a>/is';

            return (string) preg_replace_callback($pattern, function ($matches) use ($context, $baseUrl) {
                $attrsBefore = $matches[1] ?? '';
                $hrefQuote = $matches[2] ?? '"';
                $href = $matches[3] ?? '';
                $attrsAfter = $matches[4] ?? '';
                $innerHtml = $matches[5] ?? '';

                if ($href === '' || !is_string($href)) {
                    return $matches[0];
                }

                // Avoid double-wrapping if someone already used our redirect route.
                if (str_contains($href, $baseUrl . '/r/')) {
                    return $matches[0];
                }

                $anchorText = trim(html_entity_decode(strip_tags((string) $innerHtml)));
                $linkName = $this->normalizeLinkName($anchorText, $href);

                try {
                    $token = $this->generateToken([
                        'tenant_id' => (int) $context['tenant_id'],
                        'lead_id' => (int) $context['lead_id'],
                        'workflow_id' => $context['workflow_id'] ?? null,
                        'sequence_id' => $context['sequence_id'] ?? null,
                        'sequence_step_order' => $context['sequence_step_order'] ?? null,
                        'link_name' => $linkName,
                        'destination_url' => $href,
                    ]);
                } catch (\Throwable $e) {
                    // If token generation fails, keep original link to avoid breaking email.
                    report($e);
                    return $matches[0];
                }

                $trackedHref = $baseUrl . '/r/' . $token;

                // Rebuild only href part, keep original inner HTML.
                return '<a' . $attrsBefore . 'href=' . $hrefQuote . $trackedHref . $hrefQuote . $attrsAfter . '>' . $innerHtml . '</a>';
            }, $html);
        }

        // Case 2: no <a> tags -> rewrite plain https://... into tracked anchors.
        $pattern = '/\\b(https?:\\/\\/[^\\s<>"\']+)/i';

        return (string) preg_replace_callback($pattern, function ($matches) use ($context, $baseUrl) {
            $url = $matches[1] ?? '';
            if (!is_string($url) || $url === '') {
                return $matches[0];
            }

            // Avoid double-rewriting if already tracked.
            if (str_contains($url, $baseUrl . '/r/')) {
                return $matches[0];
            }

            $linkName = $this->normalizeLinkName(null, $url);

            try {
                $token = $this->generateToken([
                    'tenant_id' => (int) $context['tenant_id'],
                    'lead_id' => (int) $context['lead_id'],
                    'workflow_id' => $context['workflow_id'] ?? null,
                    'sequence_id' => $context['sequence_id'] ?? null,
                    'sequence_step_order' => $context['sequence_step_order'] ?? null,
                    'link_name' => $linkName,
                    'destination_url' => $url,
                ]);
            } catch (\Throwable $e) {
                report($e);
                return $matches[0];
            }

            $trackedHref = $baseUrl . '/r/' . $token;

            return '<a href="' . htmlspecialchars($trackedHref, ENT_QUOTES) . '">'
                . htmlspecialchars($url, ENT_QUOTES)
                . '</a>';
        }, $html);
    }
}

