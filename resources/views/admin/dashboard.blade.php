@extends('layouts.admin')

@section('title', 'Super Admin Dashboard')

@section('content')
    <div class="top-header">
        <h1>Welcome, Super Admin</h1>
    </div>

    <div class="kpi-cards">
        <div class="card" onclick="window.location='{{ route('admin.tenants.index') }}'" style="cursor: pointer;">
            <h3>Total Tenants</h3>
            <p>{{ $tenantCount }}</p>
        </div>
        <div class="card" onclick="window.location='{{ route('admin.tenants.index') }}'" style="cursor: pointer;">
            <h3>Active Tenants</h3>
            <p>{{ $activeTenantCount }}</p>
        </div>
        <div class="card" onclick="window.location='{{ route('admin.tenants.index') }}'" style="cursor: pointer;">
            <h3>Trial Tenants</h3>
            <p>{{ $trialTenantCount }}</p>
        </div>
        <div class="card" onclick="window.location='{{ route('admin.users.index') }}'" style="cursor: pointer;">
            <h3>Total Users</h3>
            <p>{{ $userCount }}</p>
        </div>
        <div class="card" onclick="window.location='{{ route('admin.leads.index') }}'" style="cursor: pointer;">
            <h3>Total Leads</h3>
            <p>{{ $leadCount }}</p>
        </div>
        <div class="card">
            <h3>MRR (Paid This Month)</h3>
            <p>₱{{ number_format($mrr, 2) }}</p>
        </div>
    </div>

    <div class="charts">
        <div class="chart">
            <h3>Platform Lead Volume Trend</h3>
            <canvas id="leadTrendChart"></canvas>
        </div>
        <div class="chart">
            <h3>Total Users by Role</h3>
            <canvas id="usersByRoleChart"></canvas>
        </div>
    </div>

    <div class="card" style="margin-bottom: 20px;">
        <h3>Payment Status Totals</h3>
        <table>
            <thead>
                <tr>
                    <th>Status</th>
                    <th>Transactions</th>
                    <th>Total Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach(['paid', 'pending', 'failed'] as $status)
                    @php
                        $row = $paymentStatusTotals->get($status);
                    @endphp
                    <tr>
                        <td>{{ ucfirst($status) }}</td>
                        <td>{{ (int) ($row->count ?? 0) }}</td>
                        <td>₱{{ number_format((float) ($row->total ?? 0), 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="card">
        <h3>Needs Action Now</h3>
        <table>
            <thead>
                <tr>
                    <th>Company</th>
                    <th>Status</th>
                    <th>Plan</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                @forelse($actionableTenants as $tenant)
                    <tr>
                        <td>{{ $tenant->company_name }}</td>
                        <td>{{ ucfirst($tenant->status) }}</td>
                        <td>{{ $tenant->subscription_plan }}</td>
                        <td>{{ $tenant->created_at->format('Y-m-d') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">No trial/inactive tenants found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div style="margin-top: 16px;">
            {{ $actionableTenants->links('pagination::bootstrap-4') }}
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        const leadTrendCtx = document.getElementById('leadTrendChart').getContext('2d');
        new Chart(leadTrendCtx, {
            type: 'line',
            data: {
                labels: @json($leadTrendLabels),
                datasets: [{
                    label: 'Leads',
                    data: @json($leadTrendValues),
                    borderColor: '#2563EB',
                    backgroundColor: 'rgba(37, 99, 235, 0.18)',
                    fill: true,
                    tension: 0.35
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        const roleCtx = document.getElementById('usersByRoleChart').getContext('2d');
        new Chart(roleCtx, {
            type: 'bar',
            data: {
                labels: @json($usersByRole->pluck('name')->values()),
                datasets: [{
                    label: 'Users',
                    data: @json($usersByRole->pluck('users_count')->values()),
                    backgroundColor: '#3B82F6'
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
