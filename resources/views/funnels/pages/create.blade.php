@extends('layouts.admin')

@section('title', 'Add Page to ' . $funnel->name)

@section('content')
    <div class="top-header">
        <h1>Add Page: {{ $funnel->name }}</h1>
        <p style="margin: 0; color: #6b7280; font-size: 14px;">Add a new page to this funnel</p>
    </div>

    <div class="card" style="max-width: 700px; margin-bottom: 20px;">
        <h3>New Page</h3>
        <form method="POST" action="{{ route('funnels.pages.store', $funnel) }}">
            @csrf
            <div style="margin-bottom: 20px;">
                <label for="type" style="display: block; margin-bottom: 8px; font-weight: bold;">Page Type</label>
                <select name="type" id="type" required style="width: 100%; padding: 10px; border: 1px solid #DBEAFE; border-radius: 6px;">
                    @foreach(\App\Models\FunnelPage::TYPES as $value => $label)
                        <option value="{{ $value }}" {{ old('type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('type')<span style="color: red; font-size: 12px;">{{ $message }}</span>@enderror
            </div>
            <div style="margin-bottom: 20px;">
                <label for="title" style="display: block; margin-bottom: 8px; font-weight: bold;">Title</label>
                <input type="text" name="title" id="title" value="{{ old('title') }}" required maxlength="255"
                    style="width: 100%; padding: 10px; border: 1px solid #DBEAFE; border-radius: 6px;">
                @error('title')<span style="color: red; font-size: 12px;">{{ $message }}</span>@enderror
            </div>
            <div style="margin-bottom: 20px;">
                <label for="content" style="display: block; margin-bottom: 8px; font-weight: bold;">Content (HTML allowed)</label>
                <textarea name="content" id="content" rows="6"
                    style="width: 100%; padding: 10px; border: 1px solid #DBEAFE; border-radius: 6px;">{{ old('content') }}</textarea>
                @error('content')<span style="color: red; font-size: 12px;">{{ $message }}</span>@enderror
            </div>
            <div style="display: flex; gap: 10px;">
                <button type="submit" style="padding: 10px 20px; background-color: #2563EB; color: white; border: none; border-radius: 6px; cursor: pointer;">
                    Add Page
                </button>
                <a href="{{ route('funnels.edit', $funnel) }}" style="padding: 10px 20px; background-color: #1E40AF; color: white; text-decoration: none; border-radius: 6px;">Cancel</a>
            </div>
        </form>
    </div>
    <p style="margin-top: 1rem;"><a href="{{ route('funnels.edit', $funnel) }}" style="color: #6b7280; text-decoration: none;">&larr; Back to Funnel</a></p>
@endsection
