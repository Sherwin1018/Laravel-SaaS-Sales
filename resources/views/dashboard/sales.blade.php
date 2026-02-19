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
        <div>
            <h1>Welcome, {{ auth()->user()->name }}</h1>
            <p>This is your Sales Dashboard.</p>
        </div>
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

    <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <h3>Your Leads</h3>
        <p>View and manage your assigned leads.</p>
        
        <div style="margin-top: 20px; display: flex; gap: 20px; flex-wrap: wrap;">
            <a href="{{ route('leads.index') }}" style="background: var(--theme-primary, #2563EB); color: white; padding: 10px 20px; text-decoration: none; border-radius: 6px; font-weight: 600;">View All Leads</a>
            <a href="{{ route('leads.create') }}" style="background: var(--theme-accent, #0EA5E9); color: white; padding: 10px 20px; text-decoration: none; border-radius: 6px; font-weight: 600;">Add New Lead</a>
        </div>
    </div>
@endsection
