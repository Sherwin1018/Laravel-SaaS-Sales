@extends('layouts.admin')

@section('title', 'Account Owner Dashboard')

@section('content')
    <div class="top-header">
        <h1>Welcome, {{ auth()->user()->name }}</h1>
        <p>Business Performance Snapshot</p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 16px; margin-bottom: 20px;">
        <div class="card">
            <h3>Total Leads</h3>
            <p style="font-size: 26px; font-weight: 700;">{{ $totalLeads }}</p>
        </div>
        <div class="card">
            <h3>Leads This Month</h3>
            <p style="font-size: 26px; font-weight: 700;">{{ $leadsThisMonth }}</p>
        </div>
        <div class="card">
            <h3>Conversion Rate</h3>
            <p style="font-size: 26px; font-weight: 700;">{{ $conversionRate }}%</p>
        </div>
        <div class="card">
            <h3>Paid Revenue</h3>
            <p style="font-size: 26px; font-weight: 700;">${{ number_format($revenueTotal, 2) }}</p>
        </div>
    </div>

    <div class="card">
        <h3>Leads by Status</h3>
        <table>
            <thead>
                <tr>
                    <th>Status</th>
                    <th>Count</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leadsByStatus as $status => $count)
                    <tr>
                        <td>{{ ucwords(str_replace('_', ' ', $status)) }}</td>
                        <td>{{ $count }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2">No lead data found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
