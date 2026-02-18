@extends('layouts.admin')

@section('title', 'Super Admin Dashboard')

@section('content')
    <!-- Top Header -->
    <div class="top-header">
        <h1>Welcome, Super Admin</h1>
        <div class="header-right">
            <div class="notification-bell">
                <i class="fas fa-bell"></i>
                <span class="badge">3</span>
            </div>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="kpi-cards">
        <div class="card" onclick="window.location='{{ route('admin.tenants.index') }}'" style="cursor: pointer;">
            <h3>Total Tenants</h3>
            <p>{{ $tenantCount }}</p>
        </div>
        <div class="card" onclick="window.location='{{ route('admin.tenants.index') }}'" style="cursor: pointer;">
            <h3>Active Tenants</h3>
            <p>{{ $activeTenantCount }}</p>
        </div>
        <div class="card">
            <h3>Active Subscriptions (MRR)</h3>
            <p>${{ number_format($mrr) }}</p>
        </div>
        <div class="card" onclick="window.location='{{ route('admin.users.index') }}'" style="cursor: pointer;">
            <h3>Total Users</h3>
            <p>{{ $userCount }}</p>
        </div>
        <div class="card" onclick="window.location='{{ route('admin.leads.index') }}'" style="cursor: pointer;">
            <h3>Total Leads</h3>
            <p>{{ $leadCount }}</p>
        </div>
    </div>

    <!-- Charts -->
    <div class="charts">
        <div class="chart">
            <h3>Monthly Revenue</h3>
            <canvas id="revenueChart"></canvas>
        </div>
        <div class="chart">
            <h3>Active Tenants by Plan</h3>
            <!-- Still a placeholder for now as requested only one static chart, but adding canvas for consistency if needed later -->
            <canvas id="tenantsChart"></canvas>
        </div>
    </div>

    <!-- Tables -->
    <div class="actions">
        <button><i class="fas fa-download"></i> Export Reports</button>
    </div>

    <h3>Recent Activity</h3>
    <table>
        <thead>
            <tr>
                <th>Timestamp</th>
                <th>User</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <tr><td>2026-02-14 09:00</td><td>Admin</td><td>Created Tenant A</td></tr>
            <tr><td>2026-02-14 10:15</td><td>Admin</td><td>Added User X</td></tr>
            <tr><td>2026-02-14 11:30</td><td>Admin</td><td>Updated Funnel</td></tr>
        </tbody>
    </table>

    <h3>Platform Metrics</h3>
    <table>
        <thead>
            <tr>
                <th>Metric</th>
                <th>Count</th>
            </tr>
        </thead>
        <tbody>
            <tr><td>Total Tenants</td><td>{{ $tenantCount }}</td></tr>
            <tr><td>Active Tenants</td><td>{{ $activeTenantCount }}</td></tr>
            <tr><td>Total Users</td><td>{{ $userCount }}</td></tr>
            <tr><td>Total Leads</td><td>{{ $leadCount }}</td></tr>
        </tbody>
    </table>
@endsection

@section('scripts')
    <script>
        // Static Chart
        const ctx = document.getElementById('revenueChart').getContext('2d');
        const revenueChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Revenue ($)',
                    data: [12000, 19000, 3000, 5000, 2000, 30000],
                    borderColor: '#2563EB',
                    backgroundColor: 'rgba(37, 99, 235, 0.2)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        // Active Tenants by Plan Chart
        const ctxTenants = document.getElementById('tenantsChart').getContext('2d');
        const tenantsChart = new Chart(ctxTenants, {
            type: 'bar',
            data: {
                labels: ['Basic', 'Pro', 'Enterprise'],
                datasets: [{
                    label: 'Active Tenants',
                    data: [8, 15, 4],
                    backgroundColor: [
                        '#3B82F6',
                        '#2563EB',
                        '#1E40AF'
                    ],
                    borderColor: [
                        '#3B82F6',
                        '#2563EB',
                        '#1E40AF'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    </script>
@endsection
