<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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

        $this->ensureWorkflowIdentifier();

        $workflow = $this->fetchWorkflow();
        if (! is_array($workflow)) {
            throw new RuntimeException('Failed to fetch workflow status from n8n.');
        }

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

        $this->ensureWorkflowIdentifier();

        try {
            $current = $this->status();
            if (($current['active'] ?? null) === $active) {
                return true;
            }
        } catch (\Throwable $e) {
            Log::info('Proceeding with n8n workflow toggle despite status lookup failure.', [
                'message' => $e->getMessage(),
                'workflow_id' => $this->workflowId(),
                'workflow_name' => $this->workflowName(),
            ]);
        }

        $endpoint = $this->workflowEndpoint();

        $response = $this->request()->patch($endpoint, [
            'active' => $active,
        ]);

        if ($response->successful()) {
            return true;
        }

        if ($response->status() === 404 && $this->discoverWorkflowIdByName()) {
            $endpoint = $this->workflowEndpoint();
            $retryResponse = $this->request()->patch($endpoint, [
                'active' => $active,
            ]);

            if ($retryResponse->successful()) {
                return true;
            }

            $response = $retryResponse;
        }

        // Compatibility fallback for n8n versions that use explicit activate/deactivate endpoints.
        $fallback = $this->request()
            ->withHeaders(['Content-Type' => 'application/json'])
            ->withBody('{}', 'application/json')
            ->post($endpoint.'/'.($active ? 'activate' : 'deactivate'));

        if ($fallback->successful()) {
            return true;
        }

        throw $this->requestExceptionForResponse($fallback->status() >= 400 ? $fallback : $response);
    }

    public function isConfigured(): bool
    {
        return $this->baseUrl() !== '' && $this->apiKey() !== '' && ($this->workflowId() !== '' || $this->workflowName() !== '');
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchWorkflow(): array
    {
        $response = $this->request()->get($this->workflowEndpoint());
        if ($response->successful()) {
            $data = $response->json();

            return is_array($data) ? (is_array($data['data'] ?? null) ? $data['data'] : $data) : [];
        }

        if ($response->status() === 404 && $this->discoverWorkflowIdByName()) {
            $retry = $this->request()->get($this->workflowEndpoint());
            if ($retry->successful()) {
                $data = $retry->json();

                return is_array($data) ? (is_array($data['data'] ?? null) ? $data['data'] : $data) : [];
            }

            throw $this->requestExceptionForResponse($retry);
        }

        throw $this->requestExceptionForResponse($response);
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

    private function ensureWorkflowIdentifier(): void
    {
        if ($this->workflowId() !== '') {
            return;
        }

        if (! $this->discoverWorkflowIdByName()) {
            throw new RuntimeException('The configured n8n workflow could not be found. Update N8N_WORKFLOW_ID or N8N_WORKFLOW_NAME to the current workflow.');
        }
    }

    private function discoverWorkflowIdByName(): bool
    {
        $workflowName = $this->workflowName();
        if ($workflowName === '') {
            return false;
        }

        $response = $this->request()->get(rtrim($this->baseUrl(), '/').'/api/v1/workflows', [
            'limit' => 100,
        ]);

        if (! $response->successful()) {
            return false;
        }

        $payload = $response->json();
        $workflows = is_array($payload) ? ($payload['data'] ?? $payload) : [];
        if (! is_array($workflows)) {
            return false;
        }

        foreach ($workflows as $workflow) {
            if (! is_array($workflow)) {
                continue;
            }

            if ((string) ($workflow['name'] ?? '') !== $workflowName) {
                continue;
            }

            $resolvedId = trim((string) ($workflow['id'] ?? ''));
            if ($resolvedId === '') {
                continue;
            }

            config(['services.n8n.workflow_id' => $resolvedId]);

            return true;
        }

        return false;
    }

    private function requestExceptionForResponse($response): RuntimeException
    {
        $status = method_exists($response, 'status') ? (int) $response->status() : 0;
        $body = method_exists($response, 'json') ? $response->json() : null;
        $message = is_array($body) ? (string) ($body['message'] ?? '') : '';
        $workflowName = $this->workflowName();

        if ($status === 401) {
            return new RuntimeException('The n8n API key is invalid or expired. Generate a new key in n8n Settings > n8n API and update N8N_API_KEY.');
        }

        if ($status === 404) {
            $workflowLabel = $workflowName !== '' ? sprintf(' "%s"', $workflowName) : '';

            return new RuntimeException('The configured n8n workflow could not be found. Update N8N_WORKFLOW_ID'.($workflowLabel !== '' ? ' or N8N_WORKFLOW_NAME' : '').' to the current workflow.');
        }

        if ($message !== '') {
            return new RuntimeException('n8n API request failed: '.$message);
        }

        return new RuntimeException('Failed to communicate with n8n.');
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

    private function workflowName(): string
    {
        return trim((string) config('services.n8n.workflow_name'));
    }
}
