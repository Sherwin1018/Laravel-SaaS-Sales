@extends('layouts.admin')

@section('title', 'Add New Lead')

@section('content')
    <div class="top-header">
        <h1>Add New Lead</h1>
    </div>

    <div class="card" style="max-width: 700px; margin: 0 auto;">
        <form action="{{ route('leads.store') }}" method="POST">
            @csrf

            <div style="margin-bottom: 20px;">
                <label for="name" style="display: block; margin-bottom: 8px; font-weight: bold;">Name</label>
                <input type="text" name="name" id="name" required
                    style="width: 100%; padding: 10px; border: 1px solid #DBEAFE; border-radius: 6px;"
                    value="{{ old('name') }}">
                @error('name')
                    <span style="color: red; font-size: 12px;">{{ $message }}</span>
                @enderror
            </div>

            <div style="margin-bottom: 20px;">
                <label for="email" style="display: block; margin-bottom: 8px; font-weight: bold;">Email</label>
                <input type="email" name="email" id="email" required
                    style="width: 100%; padding: 10px; border: 1px solid #DBEAFE; border-radius: 6px;"
                    value="{{ old('email') }}">
                @error('email')
                    <span style="color: red; font-size: 12px;">{{ $message }}</span>
                @enderror
            </div>

            <div style="margin-bottom: 20px;">
                <label for="phone" style="display: block; margin-bottom: 8px; font-weight: bold;">Phone</label>
                <input type="text" name="phone" id="phone"
                    style="width: 100%; padding: 10px; border: 1px solid #DBEAFE; border-radius: 6px;"
                    value="{{ old('phone') }}">
                @error('phone')
                    <span style="color: red; font-size: 12px;">{{ $message }}</span>
                @enderror
            </div>

            <div style="margin-bottom: 20px;">
                <label for="status" style="display: block; margin-bottom: 8px; font-weight: bold;">Pipeline Stage</label>
                <select name="status" id="status" required
                    style="width: 100%; padding: 10px; border: 1px solid #DBEAFE; border-radius: 6px;">
                    @foreach($statuses as $value => $label)
                        <option value="{{ $value }}" {{ old('status', 'new') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            @if(auth()->user()->hasRole('account-owner') || auth()->user()->hasRole('marketing-manager'))
                <div style="margin-bottom: 20px;">
                    <label for="assigned_to" style="display: block; margin-bottom: 8px; font-weight: bold;">Assign to Sales Agent</label>
                    <select name="assigned_to" id="assigned_to"
                        style="width: 100%; padding: 10px; border: 1px solid #DBEAFE; border-radius: 6px;">
                        <option value="">Unassigned</option>
                        @foreach($assignableAgents as $agent)
                            <option value="{{ $agent->id }}" {{ (string) old('assigned_to') === (string) $agent->id ? 'selected' : '' }}>
                                {{ $agent->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div style="display: flex; gap: 10px;">
                <button type="submit"
                    style="padding: 10px 20px; background-color: #2563EB; color: white; border: none; border-radius: 6px; cursor: pointer;">
                    Create Lead
                </button>
                <a href="{{ route('leads.index') }}"
                    style="padding: 10px 20px; background-color: #1E40AF; color: white; text-decoration: none; border-radius: 6px;">
                    Cancel
                </a>
            </div>
        </form>
    </div>
@endsection
