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
                <label for="purpose" style="display:block; margin-bottom:8px; font-weight:700;">Funnel Purpose</label>
                <select id="purpose" name="purpose" required
                    style="width:100%; padding:10px; border:1px solid var(--theme-border, #E6E1EF); border-radius:6px; background:#fff;">
                    <option value="" disabled {{ old('purpose') ? '' : 'selected' }}>Select funnel purpose</option>
                    @foreach(($purposeOptions ?? []) as $value => $label)
                        <option value="{{ $value }}" {{ old('purpose') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <div style="margin-top:6px; color:#64748b; font-size:12px;">
                    This sets the starter flow. Physical product funnels start more like order funnels, while service funnels keep the lead-first flow.
                </div>
                @error('purpose')
                    <span style="color:red; font-size:12px;">{{ $message }}</span>
                @enderror
            </div>

            <div style="margin-bottom: 16px;">
                <label style="display:flex; align-items:center; gap:10px; cursor:pointer;">
                    <input type="checkbox" name="require_double_opt_in" value="1" {{ old('require_double_opt_in') ? 'checked' : '' }}>
                    <span style="font-weight:700;">Require email confirmation (double opt-in)</span>
                </label>
                <p style="color:#64748b; font-size:12px; margin:6px 0 0 28px;">When enabled, leads must click a verification link before automation runs.</p>
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
                Starter flow by purpose:
                <br><strong>Service / Lead</strong>: Landing -> Opt-in -> Sales -> Checkout -> Thank You
                <br><strong>Physical Product</strong>: Sales -> Checkout -> Thank You
                <br><br>Shared templates are available only inside the builder templates panel.
            </div>

            <div style="display:flex; gap:10px; margin-top:18px;">
                <button type="submit" class="btn-create">Create Funnel</button>
                <a href="{{ route('funnels.index') }}" style="padding:10px 16px; border-radius:6px; text-decoration:none; background:var(--theme-primary-dark, #2E1244); color:#fff; font-weight:700;">Cancel</a>
            </div>
        </form>
    </div>
@endsection
