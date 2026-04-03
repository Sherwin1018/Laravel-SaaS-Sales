@extends('layouts.admin')

@section('title', 'Create Funnel Template')

@section('content')
    <div class="top-header">
        <h1>Create Funnel Template</h1>
    </div>

    <div class="card" style="max-width: 760px; margin: 0 auto;">
        <form method="POST" action="{{ route('admin.funnel-templates.store') }}">
            @csrf
            <div style="margin-bottom: 16px;">
                <label for="name" style="display:block; margin-bottom:8px; font-weight:700;">Template Name</label>
                <input id="name" name="name" type="text" required value="{{ old('name') }}"
                    style="width:100%; padding:10px; border:1px solid var(--theme-border, #E6E1EF); border-radius:6px;">
                @error('name')
                    <span style="color:red; font-size:12px;">{{ $message }}</span>
                @enderror
            </div>

            <div style="margin-bottom: 16px;">
                <label for="description" style="display:block; margin-bottom:8px; font-weight:700;">Description</label>
                <textarea id="description" name="description" rows="4"
                    style="width:100%; padding:10px; border:1px solid var(--theme-border, #E6E1EF); border-radius:6px;">{{ old('description') }}</textarea>
                @error('description')
                    <span style="color:red; font-size:12px;">{{ $message }}</span>
                @enderror
            </div>

            <p style="color:#475569; font-size:12px; font-weight:600; margin-bottom:16px;">
                A starter sequence will be created automatically so you can open the builder right away and publish it when ready.
            </p>

            <div style="display:flex; gap:10px;">
                <button type="submit" class="btn-create">Create Template</button>
                <a href="{{ route('admin.funnel-templates.index') }}" style="padding:10px 16px; border-radius:6px; text-decoration:none; background:var(--theme-primary-dark, #2E1244); color:#fff; font-weight:700;">Cancel</a>
            </div>
        </form>
    </div>
@endsection
