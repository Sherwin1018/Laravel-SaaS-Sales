@extends('layouts.admin')

@section('title', 'Create Funnel')

@section('content')
    <div class="top-header">
        <h1>Create Funnel</h1>
        <p style="margin: 0; color: #6b7280; font-size: 14px;">Add a new sales funnel</p>
    </div>

    <div class="card" style="max-width: 700px; margin-bottom: 20px;">
        <h3>New Funnel</h3>
        <form method="POST" action="{{ route('funnels.store') }}">
            @csrf
            <div style="margin-bottom: 20px;">
                <label for="name" style="display: block; margin-bottom: 8px; font-weight: bold;">Name</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required maxlength="255"
                    style="width: 100%; padding: 10px; border: 1px solid #DBEAFE; border-radius: 6px;">
                @error('name')<span style="color: red; font-size: 12px;">{{ $message }}</span>@enderror
            </div>
            <div style="margin-bottom: 20px;">
                <label for="description" style="display: block; margin-bottom: 8px; font-weight: bold;">Description (optional)</label>
                <textarea name="description" id="description" rows="3"
                    style="width: 100%; padding: 10px; border: 1px solid #DBEAFE; border-radius: 6px;">{{ old('description') }}</textarea>
                @error('description')<span style="color: red; font-size: 12px;">{{ $message }}</span>@enderror
            </div>
            <div style="margin-bottom: 20px;">
                <label style="display: flex; align-items: center; gap: 0.5rem;">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                    Active
                </label>
            </div>
            <div style="display: flex; gap: 10px;">
                <button type="submit" style="padding: 10px 20px; background-color: #2563EB; color: white; border: none; border-radius: 6px; cursor: pointer;">
                    Create Funnel
                </button>
                <a href="{{ route('funnels.index') }}" style="padding: 10px 20px; background-color: #1E40AF; color: white; text-decoration: none; border-radius: 6px;">Cancel</a>
            </div>
        </form>
    </div>
@endsection
