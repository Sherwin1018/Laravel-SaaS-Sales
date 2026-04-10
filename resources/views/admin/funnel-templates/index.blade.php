@extends('layouts.admin')

@section('title', 'Funnel Templates')

@section('content')
    <div class="top-header">
        <h1>Shared Funnel Templates</h1>
    </div>

    <div class="actions" style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap;">
        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <a href="{{ route('admin.funnel-templates.create') }}" class="btn-create"><i class="fas fa-plus"></i> New Template</a>
            <a href="{{ route('admin.funnel-templates.import') }}" class="btn-create" style="background:#fff; color:var(--theme-primary, #240E35); border:1px solid var(--theme-border, #E6E1EF);"><i class="fas fa-file-import"></i> Import JSON Template</a>
        </div>
        <form method="GET" action="{{ route('admin.funnel-templates.index') }}">
            @if(!empty($showLegacy))
                <input type="hidden" name="legacy" value="1">
            @endif
            <input
                type="text"
                name="search"
                value="{{ $search ?? '' }}"
                placeholder="Search templates..."
                style="width:min(320px, 100%); padding:10px 12px; border:1px solid var(--theme-border, #E6E1EF); border-radius:10px; background:#fff;">
        </form>
    </div>

    <div class="card" style="margin-top: 16px;">
        <div style="margin-bottom: 12px; color:#64748b; font-size:13px;">
            Super admins can build, import, and publish templates here. Published templates appear in builder mode for reusable application.
        </div>
        <div style="margin-bottom: 14px; color:#64748b; font-size:13px;">
            @if(!empty($showLegacy))
                Showing uncategorized legacy templates too.
                <a href="{{ route('admin.funnel-templates.index', array_filter(['search' => $search ?? null])) }}" style="font-weight:700;">Hide legacy templates</a>
            @else
                Legacy templates without a purpose are hidden from this list and from the builder libraries.
                <a href="{{ route('admin.funnel-templates.index', array_filter(['search' => $search ?? null, 'legacy' => 1])) }}" style="font-weight:700;">Show legacy templates</a>
            @endif
        </div>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Purpose</th>
                    <th>Status</th>
                    <th>Pages</th>
                    <th>Slug</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @include('admin.funnel-templates._rows', ['templates' => $templates])
            </tbody>
        </table>

        <div style="margin-top:18px;">
            {{ $templates->links('pagination::bootstrap-4') }}
        </div>
    </div>
@endsection
