@extends('layouts.admin')

@section('title', $funnel->name)

@section('content')
    <div class="top-header">
        <h1>{{ $funnel->name }}</h1>
        <p style="margin: 0; color: #6b7280; font-size: 14px;">View and manage funnel pages</p>
    </div>

    <div class="card" style="margin-bottom: 20px;">
        <p><strong>Slug:</strong> <code>{{ $funnel->slug }}</code></p>
        @if($funnel->description)<p>{{ $funnel->description }}</p>@endif
        <p>Status: {{ $funnel->is_active ? 'Active' : 'Inactive' }}</p>
        <a href="{{ route('funnels.edit', $funnel) }}" style="display: inline-block; padding: 8px 16px; background-color: #2563EB; color: white; text-decoration: none; border-radius: 6px;">
            <i class="fas fa-edit"></i> Edit Funnel
        </a>
    </div>

    <div class="card">
        <h3>Pages</h3>
        <ul style="list-style: none; padding: 0;">
            @foreach($funnel->pages as $page)
                <li style="padding: 8px 0; border-bottom: 1px solid #eee;">
                    <strong>{{ $page->title }}</strong> ({{ \App\Models\FunnelPage::TYPES[$page->type] ?? $page->type }}) —
                    <a href="{{ route('funnels.public.page', [$funnel->slug, $page->slug]) }}" target="_blank" style="color: #2563EB;">View public page</a>
                </li>
            @endforeach
        </ul>
        <a href="{{ route('funnels.pages.create', $funnel) }}" style="display: inline-block; margin-top: 12px; padding: 8px 16px; background-color: #2563EB; color: white; text-decoration: none; border-radius: 6px;">
            <i class="fas fa-plus"></i> Add Page
        </a>
    </div>

    <p style="margin-top: 1rem;"><a href="{{ route('funnels.index') }}" style="color: #6b7280; text-decoration: none;">&larr; Back to Funnels</a></p>
@endsection
