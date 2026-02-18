@extends('layouts.admin')

@section('title', 'Finance Dashboard')

@section('content')
    <div class="top-header">
        <h1>Welcome, {{ auth()->user()->name }}</h1>
        <p>This is your Finance Dashboard.</p>
    </div>

    <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <h3>Financial Overview</h3>
        <p>Manage billing, invoices, and subscription details.</p>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 20px;">
            <div style="background: #fff7ed; padding: 15px; border-radius: 6px;">
                <h4 style="margin: 0; color: #b45309;">Outstanding Invoices</h4>
                <p style="font-size: 24px; font-weight: bold; margin: 10px 0;">$1,250.00</p>
            </div>
             <div style="background: #ecfdf5; padding: 15px; border-radius: 6px;">
                <h4 style="margin: 0; color: #047857;">MCR (Monthly Reoccurring)</h4>
                <p style="font-size: 24px; font-weight: bold; margin: 10px 0;">$4,500.00</p>
            </div>
        </div>
    </div>
@endsection
