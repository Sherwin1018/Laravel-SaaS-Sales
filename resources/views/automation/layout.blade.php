@extends('layouts.admin')

@section('title', $pageTitle ?? 'Automation')

@section('content')
    <div class="top-header">
        <h1>Automation</h1>
    </div>

    <nav class="automation-subnav" aria-label="Automation sections">
        <a href="{{ route('automation.overview') }}" class="{{ request()->routeIs('automation.overview') ? 'active' : '' }}">Overview</a>
        <a href="{{ route('automation.sequences.index') }}" class="{{ request()->routeIs('automation.sequences.*') ? 'active' : '' }}">Sequences</a>
        <a href="{{ route('automation.workflows.index') }}" class="{{ request()->routeIs('automation.workflows.*') ? 'active' : '' }}">Workflows</a>
        <a href="{{ route('automation.logs.index') }}" class="{{ request()->routeIs('automation.logs.*') ? 'active' : '' }}">Logs</a>
    </nav>

    @yield('automation_content')
@endsection

@push('styles')
<style>
    .automation-subnav {
        display: flex;
        gap: 4px;
        margin-bottom: 24px;
        flex-wrap: wrap;
        border-bottom: 1px solid #DBEAFE;
        padding-bottom: 0;
    }
    .automation-subnav a {
        padding: 10px 16px;
        text-decoration: none;
        font-weight: 600;
        color: var(--theme-sidebar-text, #1E40AF);
        border-bottom: 3px solid transparent;
        margin-bottom: -1px;
        border-radius: 6px 6px 0 0;
    }
    .automation-subnav a:hover {
        background: #EFF6FF;
    }
    .automation-subnav a.active {
        background: #EFF6FF;
        border-bottom-color: var(--theme-primary, #2563EB);
        color: var(--theme-primary, #2563EB);
    }
    @media (max-width: 768px) {
        .automation-subnav { gap: 2px; }
        .automation-subnav a { padding: 8px 12px; font-size: 14px; }
    }
</style>
@endpush
