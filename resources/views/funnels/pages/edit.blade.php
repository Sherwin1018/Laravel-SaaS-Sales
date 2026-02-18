@extends('layouts.admin')

@section('title', 'Edit Page: ' . $page->title)

@section('content')
    <div class="top-header">
        <h1>Edit Page: {{ $page->title }}</h1>
        <p style="margin: 0; color: #6b7280; font-size: 14px;">{{ \App\Models\FunnelPage::TYPES[$page->type] ?? $page->type }}</p>
    </div>

    <div class="card" style="max-width: 700px; margin-bottom: 20px;">
        <h3>Page Content</h3>
        <form method="POST" action="{{ route('funnels.pages.update', [$funnel, $page]) }}">
            @csrf
            @method('PUT')
            <div style="margin-bottom: 20px;">
                <label for="title" style="display: block; margin-bottom: 8px; font-weight: bold;">Title</label>
                <input type="text" name="title" id="title" value="{{ old('title', $page->title) }}" required maxlength="255"
                    style="width: 100%; padding: 10px; border: 1px solid #DBEAFE; border-radius: 6px;">
                @error('title')<span style="color: red; font-size: 12px;">{{ $message }}</span>@enderror
            </div>
            <div style="margin-bottom: 20px;">
                <label for="content" style="display: block; margin-bottom: 8px; font-weight: bold;">Content (HTML allowed)</label>
                <textarea name="content" id="content" rows="8"
                    style="width: 100%; padding: 10px; border: 1px solid #DBEAFE; border-radius: 6px;">{{ old('content', $page->content) }}</textarea>
                @error('content')<span style="color: red; font-size: 12px;">{{ $message }}</span>@enderror
            </div>
            <div style="display: flex; gap: 10px;">
                <button type="submit" style="padding: 10px 20px; background-color: #2563EB; color: white; border: none; border-radius: 6px; cursor: pointer;">
                    Save
                </button>
                <a href="{{ route('funnels.edit', $funnel) }}" style="padding: 10px 20px; background-color: #1E40AF; color: white; text-decoration: none; border-radius: 6px;">Cancel</a>
            </div>
        </form>
    </div>
    <p style="margin-top: 1rem;"><a href="{{ route('funnels.edit', $funnel) }}" style="color: #6b7280; text-decoration: none;">&larr; Back to Funnel</a></p>
@endsection
