@extends('layouts.admin')

@section('title', 'Funnel Analytics')

@section('content')
    <div class="top-header">
        <h1>Funnel Analytics</h1>
        <p style="color: var(--theme-muted, #6B7280); margin-top: 6px;">
            Proxy funnel (visits logged in `funnel_visits` + opt-ins derived from leads created in the same date window).
        </p>
    </div>

    <div class="card" style="margin-top: 18px;">
        <form method="GET" action="{{ route('marketing.funnel_analytics') }}" style="display:flex; gap:12px; align-items:end; flex-wrap:wrap;">
            <div style="display:flex; flex-direction:column; gap:6px;">
                <label style="font-weight:700;">From</label>
                <input type="date" name="from" value="{{ $from?->format('Y-m-d') }}" style="padding: 10px; border: 1px solid var(--theme-border, #E6E1EF); border-radius: 6px;">
            </div>

            <div style="display:flex; flex-direction:column; gap:6px;">
                <label style="font-weight:700;">To</label>
                <input type="date" name="to" value="{{ $to?->format('Y-m-d') }}" style="padding: 10px; border: 1px solid var(--theme-border, #E6E1EF); border-radius: 6px;">
            </div>

            <div style="display:flex; flex-direction:column; gap:6px;">
                <label style="font-weight:700;">Source</label>
                <select name="source" style="padding: 10px; border: 1px solid var(--theme-border, #E6E1EF); border-radius: 6px;">
                    <option value="All" {{ ($selectedSource ?? 'All') === 'All' ? 'selected' : '' }}>All</option>
                    @foreach($sources as $src)
                        <option value="{{ $src->label }}" {{ ($selectedSource ?? 'All') === $src->label ? 'selected' : '' }}>
                            {{ $src->label }} ({{ (int) $src->visits }})
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" style="padding: 10px 16px; background-color: var(--theme-primary, #240E35); color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">
                Apply
            </button>
        </form>

        <p style="margin-top: 10px; color: var(--theme-muted, #6B7280); font-size: 12px;">
            Engagement insights (“high intent clicks”) use `lead_link_clicks.link_name` keywords: book, call, demo, schedule.
        </p>
    </div>

    <div style="display: grid; grid-template-columns: 1fr; gap: 20px; margin-top: 20px;">
        <div class="card">
            <h3>Proxy Funnel Summary (Selected Source)</h3>
            <table>
                <thead>
                    <tr>
                        <th>Stage</th>
                        <th>Count</th>
                        <th>Rate</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Visits</td>
                        <td>{{ (int) $sourceVisitsTotal }}</td>
                        <td>100%</td>
                    </tr>
                    <tr>
                        <td>Opt-ins (Leads created)</td>
                        <td>{{ (int) $sourceOptInsTotal }}</td>
                        <td>{{ (float) $sourceOptInsToVisitsRate }}%</td>
                    </tr>
                    <tr>
                        <td>In Pipeline</td>
                        <td>{{ (int) $sourceInPipelineTotal }}</td>
                        <td>{{ (float) $sourcePipelineRate }}%</td>
                    </tr>
                    <tr>
                        <td>Closed Won</td>
                        <td>{{ (int) $sourceClosedWonTotal }}</td>
                        <td>{{ (float) $sourceWonRate }}%</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="card">
            <h3>Engagement Insights (High-Intent Clicks)</h3>
            <div style="display:flex; gap: 18px; flex-wrap: wrap;">
                <div>
                    <p style="margin:0; color: var(--theme-muted, #6B7280); font-size: 12px; font-weight:700;">High-intent clickers</p>
                    <p style="margin:6px 0 0 0; font-size: 22px; font-weight:800;">{{ (int) $highIntentClickersCount }}</p>
                </div>
                <div>
                    <p style="margin:0; color: var(--theme-muted, #6B7280); font-size: 12px; font-weight:700;">High-intent -> Proposal Sent</p>
                    <p style="margin:6px 0 0 0; font-size: 22px; font-weight:800;">
                        {{ (int) $highIntentProposalSentCount }} ({{ (float) $highIntentProposalRate }}%)
                    </p>
                </div>
            </div>

            <hr style="border:0; border-top: 1px solid var(--theme-border, #E6E1EF); margin: 18px 0;">

            <h4 style="margin-bottom: 10px;">Top Leads (by last high-intent click)</h4>
            <table>
                <thead>
                    <tr>
                        <th>Lead</th>
                        <th>Status</th>
                        <th>Last High-Intent Click</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topHighIntentLeads as $lead)
                        <tr>
                            <td>{{ $lead->lead_name }}</td>
                            <td>{{ $lead->lead_status }}</td>
                            <td>
                                {{
                                    \Carbon\Carbon::parse($lead->last_high_intent_click_at)
                                        ->format('M d, H:i')
                                }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3">No high-intent click data for the selected range.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

