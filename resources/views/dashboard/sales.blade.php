@extends('layouts.admin')

@section('title', 'Sales Dashboard')

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
            <h3>My Assigned Leads</h3>
            <p>{{ $myAssignedLeadsCount }}</p>
        </div>
        <div class="card">
            <h3>Overdue Follow-ups</h3>
            <p>{{ $overdueFollowUpsCount }}</p>
        </div>
        <div class="card">
            <h3>Today Tasks</h3>
            <p>{{ $todayTaskCount }}</p>
        </div>
    </div>

    <div class="charts">
        <div class="chart">
            <h3>My Pipeline Stage Counts</h3>
            <canvas id="salesPipelineChart"></canvas>
        </div>
        <div class="chart">
            <h3>Needs Action Now</h3>
            <table>
                <thead>
                    <tr>
                        <th>Lead</th>
                        <th>Status</th>
                        <th>Last Update</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($overdueLeads as $lead)
                        <tr>
                            <td>{{ $lead->name }}</td>
                            <td>{{ ucwords(str_replace('_', ' ', $lead->status)) }}</td>
                            <td>{{ $lead->updated_at->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3">No overdue follow-ups.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div style="margin-top: 12px;">
                {{ $overdueLeads->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>

    <div class="card">
        <h3>My Recent Assigned Leads</h3>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Status</th>
                    <th>Updated</th>
                </tr>
            </thead>
            <tbody>
                @forelse($myRecentLeads as $lead)
                    <tr>
                        <td>{{ $lead->name }}</td>
                        <td>{{ ucwords(str_replace('_', ' ', $lead->status)) }}</td>
                        <td>{{ $lead->updated_at->format('Y-m-d H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">No assigned leads found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div style="margin-top: 16px;">
            {{ $myRecentLeads->links('pagination::bootstrap-4') }}
        </div>
    </div>
@endsection

@section('scripts')
    @php
        $salesPipelineLabels = array_map(function ($status) {
            return ucwords(str_replace('_', ' ', $status));
        }, array_keys($pipelineStageCounts));
    @endphp
    <script>
        const salesPipelineCtx = document.getElementById('salesPipelineChart').getContext('2d');
        new Chart(salesPipelineCtx, {
            type: 'bar',
            data: {
                labels: @json($salesPipelineLabels),
                datasets: [{
                    label: 'Leads',
                    data: @json(array_values($pipelineStageCounts)),
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
