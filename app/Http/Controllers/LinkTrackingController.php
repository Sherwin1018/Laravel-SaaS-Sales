<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\LeadLinkClick;
use App\Services\LeadLinkTrackingService;
use App\Services\AutomationWebhookService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LinkTrackingController extends Controller
{
    public function redirect(string $token, Request $request): RedirectResponse
    {
        $service = app(LeadLinkTrackingService::class);
        $claims = $service->decodeToken($token);
        if (!is_array($claims)) {
            return redirect()->to(route('dashboard.owner'));
        }

        $tenantId = isset($claims['tenant_id']) ? (int) $claims['tenant_id'] : 0;
        $leadId = isset($claims['lead_id']) ? (int) $claims['lead_id'] : 0;
        $destinationUrl = isset($claims['destination_url']) ? (string) $claims['destination_url'] : '';
        $linkName = isset($claims['link_name']) ? (string) $claims['link_name'] : null;

        if ($tenantId < 1 || $leadId < 1 || $destinationUrl === '') {
            return redirect()->to(route('dashboard.owner'));
        }

        $lead = Lead::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->where('id', $leadId)
            ->first();

        if (!$lead) {
            return redirect()->to(route('dashboard.owner'));
        }

        $workflowId = isset($claims['workflow_id']) ? (int) $claims['workflow_id'] : null;
        $sequenceId = isset($claims['sequence_id']) ? (int) $claims['sequence_id'] : null;
        $sequenceStepOrder = isset($claims['sequence_step_order']) ? (int) $claims['sequence_step_order'] : null;

        $clickNumber = (LeadLinkClick::where('tenant_id', $tenantId)
            ->where('lead_id', $leadId)
            ->where('destination_url', $destinationUrl)
            ->count()) + 1;

        $oldStatus = (string) ($lead->status ?? '');
        $oldStatusLower = mb_strtolower(trim($oldStatus));
        $linkNameLower = mb_strtolower(trim((string) ($linkName ?? '')));

        $highIntentKeywords = ['book', 'call', 'demo', 'schedule'];
        $isHighIntentLink = false;
        foreach ($highIntentKeywords as $kw) {
            if ($kw !== '' && str_contains($linkNameLower, $kw)) {
                $isHighIntentLink = true;
                break;
            }
        }

        $newStatus = null;
        if ($isHighIntentLink && in_array($oldStatusLower, ['new', 'contacted'], true)) {
            $newStatus = 'proposal_sent';
        } elseif ($oldStatusLower === 'new') {
            $newStatus = 'contacted';
        }

        LeadLinkClick::create([
            'tenant_id' => $tenantId,
            'lead_id' => $leadId,
            'workflow_id' => $workflowId ?: null,
            'sequence_id' => $sequenceId ?: null,
            'sequence_step_order' => $sequenceStepOrder ?: null,
            'link_name' => $linkName ?: null,
            'destination_url' => $destinationUrl,
            'click_number' => $clickNumber,
            'clicked_at' => now(),
        ]);

        // Update lead score + activity similarly to the existing manual scoring flow.
        $lead->increment('score', 10);
        $lead->activities()->create([
            'activity_type' => 'Scoring',
            'notes' => 'Auto: Link Clicked' . ($linkName ? " ({$linkName})" : '') . " (+10 points)",
        ]);

        // Auto-update pipeline stage when a rule says so.
        if ($newStatus !== null && $newStatus !== $oldStatusLower) {
            $lead->status = $newStatus;
            $lead->save();

            $lead->activities()->create([
                'activity_type' => 'Scoring',
                'notes' => "Auto: Pipeline Stage updated to {$newStatus} (+0 points)",
            ]);

            $automation = app(AutomationWebhookService::class);
            $payload = $automation->buildLeadStatusChangedPayload($lead, $oldStatusLower, $newStatus, []);
            $automation->dispatchEvent('lead.status_changed', $payload);
        }

        return redirect()->away($destinationUrl);
    }
}

