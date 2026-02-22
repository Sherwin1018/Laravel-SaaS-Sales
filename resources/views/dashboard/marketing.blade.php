@extends('layouts.admin')

@section('title', 'Marketing Dashboard')

@php
    $companyName = optional(auth()->user()->tenant)->company_name ?? 'No Company';
    $companyInitials = collect(preg_split('/\s+/', trim($companyName)))
        ->filter()
        ->take(2)
        ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
        ->implode('');
    $companyInitials = $companyInitials !== '' ? $companyInitials : 'NC';
    $companyHue = abs(crc32($companyName ?: 'company')) % 360;
    $companyBg = "hsl({$companyHue}, 60%, 42%)";
@endphp

@section('content')
    <div class="top-header">
        <h1>Welcome, {{ auth()->user()->name }}</h1>
        <div class="company-chip">
            <div class="company-chip-avatar" style="background: {{ $companyBg }};">
                @if(optional(auth()->user()->tenant)->logo_path)
                    <img src="{{ asset('storage/' . auth()->user()->tenant->logo_path) }}" alt="Company Logo">
                @else
                    {{ $companyInitials }}
                @endif
            </div>
            <div class="company-chip-content">
                <span class="company-chip-label">Company</span>
                <span class="company-chip-name">{{ $companyName }}</span>
            </div>
        </div>
    </div>

    <div class="kpi-cards">
        <div class="card">
            <h3>Leads Generated</h3>
            <p>{{ (int) $sourceBreakdownChart->sum('total') }}</p>
        </div>
        <div class="card">
            <h3>MQL Volume</h3>
            <p>{{ $mqlCount }}</p>
        </div>
        <div class="card">
            <h3>Quality Proxy</h3>
            <p>{{ number_format($avgLeadScore, 1) }}</p>
        </div>
        <div class="card">
            <h3>Cost Proxy</h3>
            <p style="font-size: 18px;">Ad Spend Data N/A</p>
        </div>
    </div>

    <div class="charts">
        <div class="chart">
            <h3>MQL Trend (Score >= {{ $mqlThreshold }})</h3>
            <canvas id="mqlTrendChart"></canvas>
        </div>
        <div class="chart">
            <h3>Leads by Source/Campaign</h3>
            <canvas id="sourceChart"></canvas>
        </div>
    </div>

    <div class="card">
        <h3>Needs Action Now</h3>
        <table>
            <thead>
                <tr>
                    <th>Source/Campaign</th>
                    <th>Leads</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sourceBreakdown as $row)
                    <tr>
                        <td>{{ $row->source_label }}</td>
                        <td>{{ $row->total }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2">No lead source data found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div style="margin-top: 16px;">
            {{ $sourceBreakdown->links('pagination::bootstrap-4') }}
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        const mqlTrendCtx = document.getElementById('mqlTrendChart').getContext('2d');
        new Chart(mqlTrendCtx, {
            type: 'line',
            data: {
                labels: @json($trendLabels),
                datasets: [{
                    label: 'MQL Leads',
                    data: @json($trendValues),
                    borderColor: '#0EA5E9',
                    backgroundColor: 'rgba(14, 165, 233, 0.15)',
                    fill: true,
                    tension: 0.35
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });

        const sourceCtx = document.getElementById('sourceChart').getContext('2d');
        new Chart(sourceCtx, {
            type: 'bar',
            data: {
                labels: @json($sourceBreakdownChart->pluck('source_label')->values()),
                datasets: [{
                    label: 'Leads',
                    data: @json($sourceBreakdownChart->pluck('total')->values()),
                    backgroundColor: '#2563EB'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
            }
        });
    </script>
@endsection
