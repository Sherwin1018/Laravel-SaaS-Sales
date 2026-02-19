@extends('layouts.admin')

@section('title', 'Edit Tenant')

@section('content')
    <div class="top-header">
        <h1>Edit Tenant: {{ $tenant->company_name }}</h1>
    </div>

    <div class="card" style="max-width: 600px; margin: 0 auto;">
        <form action="{{ route('admin.tenants.update', $tenant->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div style="margin-bottom: 20px;">
                <label for="company_name" style="display: block; margin-bottom: 8px; font-weight: bold;">Company Name</label>
                <input type="text" name="company_name" id="company_name" required 
                    style="width: 100%; padding: 10px; border: 1px solid #DBEAFE; border-radius: 6px;"
                    value="{{ old('company_name', $tenant->company_name) }}">
                @error('company_name')
                    <span style="color: red; font-size: 12px;">{{ $message }}</span>
                @enderror
            </div>

            <div style="margin-bottom: 20px;">
                <label for="subscription_plan" style="display: block; margin-bottom: 8px; font-weight: bold;">Subscription Plan</label>
                <select name="subscription_plan" id="subscription_plan" required
                    style="width: 100%; padding: 10px; border: 1px solid #DBEAFE; border-radius: 6px;">
                    <option value="Basic" {{ $tenant->subscription_plan == 'Basic' ? 'selected' : '' }}>Basic</option>
                    <option value="Pro" {{ $tenant->subscription_plan == 'Pro' ? 'selected' : '' }}>Pro</option>
                    <option value="Enterprise" {{ $tenant->subscription_plan == 'Enterprise' ? 'selected' : '' }}>Enterprise</option>
                </select>
                @error('subscription_plan')
                    <span style="color: red; font-size: 12px;">{{ $message }}</span>
                @enderror
            </div>

            <div style="margin-bottom: 20px;">
                <label for="status" style="display: block; margin-bottom: 8px; font-weight: bold;">Status</label>
                <select name="status" id="status" required
                    style="width: 100%; padding: 10px; border: 1px solid #DBEAFE; border-radius: 6px;">
                    <option value="trial" {{ $tenant->status == 'trial' ? 'selected' : '' }}>Trial</option>
                    <option value="active" {{ $tenant->status == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ $tenant->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                @error('status')
                    <span style="color: red; font-size: 12px;">{{ $message }}</span>
                @enderror
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="submit" 
                    style="padding: 10px 20px; background-color: #2563EB; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">
                    Update Tenant
                </button>
                <a href="{{ route('admin.tenants.index') }}" 
                    style="padding: 10px 20px; background-color: #1E40AF; color: white; text-decoration: none; border-radius: 6px; font-weight: 600;">
                    Cancel
                </a>
            </div>

        </form>
    </div>
@endsection
