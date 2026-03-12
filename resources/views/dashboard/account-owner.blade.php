@extends('layouts.admin')

@section('title', 'Account Owner Dashboard')

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
            <h3>Leads This Month</h3>
            <p>{{ $leadsThisMonth }}</p>
        </div>
        <div class="card">
            <h3>Closed Won</h3>
            <p>{{ $wonCount }}</p>
        </div>
        <div class="card">
            <h3>Closed Lost</h3>
            <p>{{ $lostCount }}</p>
        </div>
        <div class="card">
            <h3>Conversion Rate</h3>
            <p>{{ $conversionRate }}%</p>
        </div>
        <div class="card">
            <h3>Paid Revenue</h3>
            <p>₱{{ number_format($revenueTotal, 2) }}</p>
        </div>
    </div>

    <div class="charts">
        <div class="chart">
            <h3>Pipeline Distribution</h3>
            <canvas id="pipelineDistributionChart"></canvas>
        </div>
        <div class="chart">
            <h3>Pipeline Aging</h3>
            <canvas id="pipelineAgingChart"></canvas>
        </div>
    </div>

    <div class="card" style="margin-bottom: 20px;">
        <h3>Revenue and Payment Status</h3>
        <table>
            <thead>
                <tr>
                    <th>Status</th>
                    <th>Total Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach(['paid', 'pending', 'failed'] as $status)
                    <tr>
                        <td>{{ ucfirst($status) }}</td>
                        <td>₱{{ number_format((float) ($paymentStatusTotals[$status] ?? 0), 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="card">
        <h3>Team Activity Snapshot</h3>
        <table>
            <thead>
                <tr>
                    <th>Time</th>
                    <th>Lead</th>
                    <th>Activity</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                @forelse($teamActivity as $activity)
                    <tr>
                        <td>{{ $activity->created_at->format('Y-m-d H:i') }}</td>
                        <td>{{ $activity->lead->name ?? 'N/A' }}</td>
                        <td>{{ $activity->activity_type }}</td>
                        <td>{{ $activity->notes ?: 'N/A' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">No recent team activity found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div style="margin-top: 16px;">
            {{ $teamActivity->links('pagination::bootstrap-4') }}
        </div>
    </div>
@endsection

@section('scripts')
    @php
        $pipelineStatusLabels = array_map(function ($status) {
            return ucwords(str_replace('_', ' ', $status));
        }, array_keys($pipelineDistribution));
    @endphp
    <script>
        const pipelineDistributionCtx = document.getElementById('pipelineDistributionChart').getContext('2d');
        new Chart(pipelineDistributionCtx, {
            type: 'bar',
            data: {
                labels: @json($pipelineStatusLabels),
                datasets: [{
                    label: 'Leads',
                    data: @json(array_values($pipelineDistribution)),
                    backgroundColor: '#240E35'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
            }
        });

        const pipelineAgingCtx = document.getElementById('pipelineAgingChart').getContext('2d');
        new Chart(pipelineAgingCtx, {
            type: 'doughnut',
            data: {
                labels: @json(array_keys($pipelineAging)),
                datasets: [{
                    data: @json(array_values($pipelineAging)),
                    backgroundColor: ['#240E35', '#6B4A7A', '#9E7BB5']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    </script>
@endsection
