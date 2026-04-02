@php($summary = $pipelineReports['summary'] ?? [])
@php($stageCounts = $pipelineReports['stage_counts'] ?? [])
@php($stageConversions = $pipelineReports['stage_conversions'] ?? [])
@php($stageAging = $pipelineReports['stage_aging'] ?? [])

<div class="card pipeline-report-card" style="margin-bottom: 20px;">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:16px;">
        <div>
            <h3 style="margin:0;">Pipeline Reporting</h3>
            <p style="margin:6px 0 0 0;color:var(--theme-muted, #6B7280);font-size:13px;">
                Live stage counts, transition performance, aging, and win/loss health for the current workspace view.
            </p>
        </div>
        <button
            type="button"
            id="togglePipelineReportsBtn"
            class="pipeline-report-toggle-btn"
            aria-expanded="false"
            aria-controls="pipelineReportsContent">
            Show
        </button>
    </div>

    <div id="pipelineReportsContent" style="display: none;">
        <div class="pipeline-report-summary">
            <div class="pipeline-report-metric">
                <span class="pipeline-report-label">Total Leads</span>
                <strong>{{ $summary['total_leads'] ?? 0 }}</strong>
            </div>
            <div class="pipeline-report-metric">
                <span class="pipeline-report-label">Open Leads</span>
                <strong>{{ $summary['open_leads'] ?? 0 }}</strong>
            </div>
            <div class="pipeline-report-metric">
                <span class="pipeline-report-label">Won</span>
                <strong>{{ $summary['won_count'] ?? 0 }}</strong>
            </div>
            <div class="pipeline-report-metric">
                <span class="pipeline-report-label">Lost</span>
                <strong>{{ $summary['lost_count'] ?? 0 }}</strong>
            </div>
            <div class="pipeline-report-metric">
                <span class="pipeline-report-label">Win Rate</span>
                <strong>{{ number_format((float) ($summary['win_rate'] ?? 0), 1) }}%</strong>
            </div>
        </div>

        <div class="pipeline-report-grid">
            <div class="pipeline-report-panel">
                <h4>Leads By Stage</h4>
                <div class="pipeline-report-list">
                    @foreach($stageCounts as $item)
                        <div class="pipeline-report-row">
                            <span>{{ $item['label'] }}</span>
                            <strong>{{ $item['count'] }}</strong>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="pipeline-report-panel">
                <h4>Stage-to-Stage Conversion</h4>
                <div class="pipeline-report-list">
                    @foreach($stageConversions as $item)
                        <div class="pipeline-report-row pipeline-report-row-stack">
                            <div style="display:flex;justify-content:space-between;gap:12px;">
                                <span>{{ $item['label'] }}</span>
                                <strong>{{ number_format((float) $item['rate'], 1) }}%</strong>
                            </div>
                            <small style="color:var(--theme-muted, #6B7280);">
                                {{ $item['converted'] }} converted out of {{ $item['eligible'] }} leads that reached {{ \App\Models\Lead::PIPELINE_STATUSES[$item['from']] ?? ucfirst($item['from']) }}
                            </small>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="pipeline-report-panel" style="margin-top:16px;">
            <h4>Time In Stage / Aging</h4>
            <div class="pipeline-report-table-wrap">
                <table class="pipeline-report-table">
                    <thead>
                        <tr>
                            <th>Stage</th>
                            <th>Current Leads</th>
                            <th>Avg Days In Stage</th>
                            <th>7+ Days</th>
                            <th>14+ Days</th>
                            <th>30+ Days</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stageAging as $item)
                            <tr>
                                <td>{{ $item['label'] }}</td>
                                <td>{{ $item['lead_count'] }}</td>
                                <td>{{ number_format((float) $item['average_days'], 1) }}</td>
                                <td>{{ $item['older_than_7_days'] }}</td>
                                <td>{{ $item['older_than_14_days'] }}</td>
                                <td>{{ $item['older_than_30_days'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
