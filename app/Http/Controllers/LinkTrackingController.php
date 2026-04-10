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

        // Capture UTM data from session (stored by FunnelPortalController)
        $utmData = $this->getUtmDataForLead($lead);

        LeadLinkClick::create([
            'tenant_id' => $tenantId,
            'lead_id' => $leadId,
            'workflow_id' => $workflowId ?: null,
            'sequence_id' => $sequenceId ?: null,
            'sequence_step_order' => $sequenceStepOrder ?: null,
            'link_name' => $linkName ?: null,
            'destination_url' => $destinationUrl,
            'utm_source' => $utmData['utm_source'] ?? null,
            'utm_medium' => $utmData['utm_medium'] ?? null,
            'utm_campaign' => $utmData['utm_campaign'] ?? null,
            'utm_term' => $utmData['utm_term'] ?? null,
            'utm_content' => $utmData['utm_content'] ?? null,
            'utm_id' => $utmData['utm_id'] ?? null,
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

    /**
     * Retrieve UTM data for a lead from session storage.
     * Since we don't have direct funnel-to-lead relationship, we'll check
     * all possible UTM session keys and return the most recent one.
     */
    private function getUtmDataForLead(Lead $lead): array
    {
        // Try to find UTM data from any funnel session
        $utmData = [];
        
        // Check if we have any UTM data stored for this tenant's funnels
        $sessionKeys = array_filter(session()->all(), function($key) {
            return str_starts_with($key, 'link_tracking_utm_');
        }, ARRAY_FILTER_USE_KEY);
        
        foreach ($sessionKeys as $sessionKey => $value) {
            if (is_array($value) && isset($value['utm_source'])) {
                $utmData = $value;
                break; // Take the first one found
            }
        }
        
        // Fallback: try to get from lead's source_campaign if no session UTM
        if (empty($utmData) && $lead->source_campaign) {
            $utmData['utm_source'] = $lead->source_campaign;
        }
        
        return $utmData;
    }
}
