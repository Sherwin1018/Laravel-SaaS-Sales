@extends('layouts.admin')

@section('title', 'Create Funnel')

@section('content')
    <div class="top-header">
        <h1>Create Funnel</h1>
    </div>

    <div class="card" style="max-width: 820px; margin: 0 auto;">
        <form method="POST" action="{{ route('funnels.store') }}">
            @csrf

            <div style="margin-bottom: 16px;">
                <label for="name" style="display:block; margin-bottom:8px; font-weight:700;">Funnel Name</label>
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

            <div style="margin-bottom: 16px;">
                <label for="default_tags" style="display:block; margin-bottom:8px; font-weight:700;">Funnel Tags</label>
                <input id="default_tags" name="default_tags" type="text" value="{{ old('default_tags') }}"
                    placeholder="webinar, q2-campaign, lead-magnet"
                    style="width:100%; padding:10px; border:1px solid var(--theme-border, #E6E1EF); border-radius:6px;">
                @error('default_tags')
                    <span style="color:red; font-size:12px;">{{ $message }}</span>
                @enderror
            </div>

            <div style="margin:18px 0; padding:14px 16px; border-radius:12px; background:#fbf9fd; border:1px solid #ece2f5; color:#475569; font-size:13px; line-height:1.55;">
                New funnels now start from the default scratch flow:
                <strong>Landing</strong> ->
                <strong>Opt-in</strong> ->
                <strong>Sales</strong> ->
                <strong>Checkout</strong> ->
                <strong>Thank You</strong>.
                Shared templates are available only inside the builder templates panel.
            </div>

            <div style="display:flex; gap:10px; margin-top:18px;">
                <button type="submit" class="btn-create">Create Funnel</button>
                <a href="{{ route('funnels.index') }}" style="padding:10px 16px; border-radius:6px; text-decoration:none; background:var(--theme-primary-dark, #2E1244); color:#fff; font-weight:700;">Cancel</a>
            </div>
        </form>
    </div>
@endsection
