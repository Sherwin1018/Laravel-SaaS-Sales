<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class N8nWorkflowControlService
{
    /**
     * @return array{configured: bool, active: bool|null, name: string|null, raw: array<string, mixed>|null}
     */
    public function status(): array
    {
        if (! $this->isConfigured()) {
            return [
                'configured' => false,
                'active' => null,
                'name' => null,
                'raw' => null,
            ];
        }

        $response = $this->request()->get($this->workflowEndpoint());
        if (! $response->successful()) {
            throw new RuntimeException('Failed to fetch workflow status from n8n.');
        }

        $data = $response->json();
        $workflow = is_array($data) ? ($data['data'] ?? $data) : [];

        return [
            'configured' => true,
            'active' => isset($workflow['active']) ? (bool) $workflow['active'] : null,
            'name' => is_array($workflow) ? (($workflow['name'] ?? null) ? (string) $workflow['name'] : null) : null,
            'raw' => is_array($workflow) ? $workflow : null,
        ];
    }

    public function activate(): bool
    {
        return $this->setActive(true);
    }

    public function deactivate(): bool
    {
        return $this->setActive(false);
    }

    private function setActive(bool $active): bool
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('N8N workflow control is not configured.');
        }

        // No-op should be treated as success.
        $current = $this->status();
        if (($current['active'] ?? null) === $active) {
            return true;
        }

        $response = $this->request()->patch($this->workflowEndpoint(), [
            'active' => $active,
        ]);

        if ($response->successful()) {
            return true;
        }

        // Compatibility fallback for n8n versions that use explicit activate/deactivate endpoints.
        $fallback = $this->request()
            ->withHeaders(['Content-Type' => 'application/json'])
            ->withBody('{}', 'application/json')
            ->post($this->workflowEndpoint().'/'.($active ? 'activate' : 'deactivate'));

        return $fallback->successful();
    }

    public function isConfigured(): bool
    {
        return $this->baseUrl() !== '' && $this->apiKey() !== '' && $this->workflowId() !== '';
    }

    private function request()
    {
        return Http::acceptJson()
            ->withHeaders([
                'X-N8N-API-KEY' => $this->apiKey(),
            ])
            ->timeout(10);
    }

    private function workflowEndpoint(): string
    {
        return rtrim($this->baseUrl(), '/').'/api/v1/workflows/'.$this->workflowId();
    }

    private function baseUrl(): string
    {
        return (string) config('services.n8n.base_url');
    }

    private function apiKey(): string
    {
        return (string) config('services.n8n.api_key');
    }

    private function workflowId(): string
    {
        return (string) config('services.n8n.workflow_id');
    }
}
