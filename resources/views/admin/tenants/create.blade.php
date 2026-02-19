@extends('layouts.admin')

@section('title', 'Add New Tenant')

@section('content')
    <div class="top-header">
        <h1>Add New Tenant</h1>
    </div>

    <div class="card" style="max-width: 600px; margin: 0 auto;">
        <form action="{{ route('admin.tenants.store') }}" method="POST">
            @csrf

            <div style="margin-bottom: 20px;">
                <label for="company_name" style="display: block; margin-bottom: 8px; font-weight: bold;">Company Name</label>
                <input type="text" name="company_name" id="company_name" required 
                    style="width: 100%; padding: 10px; border: 1px solid #DBEAFE; border-radius: 6px;"
                    value="{{ old('company_name') }}">
                @error('company_name')
                    <span style="color: red; font-size: 12px;">{{ $message }}</span>
                @enderror
            </div>

            <div style="margin-bottom: 20px;">
                <label for="subscription_plan" style="display: block; margin-bottom: 8px; font-weight: bold;">Subscription Plan</label>
                <select name="subscription_plan" id="subscription_plan" required
                    style="width: 100%; padding: 10px; border: 1px solid #DBEAFE; border-radius: 6px;">
                    <option value="Basic">Basic</option>
                    <option value="Pro">Pro</option>
                    <option value="Enterprise">Enterprise</option>
                </select>
                @error('subscription_plan')
                    <span style="color: red; font-size: 12px;">{{ $message }}</span>
                @enderror
            </div>

            <hr style="margin: 20px 0; border: 0; border-top: 1px solid #DBEAFE;">
            <h3 style="font-size: 16px; margin-bottom: 15px; color: #1E40AF;">Admin User Details</h3>

            <div style="margin-bottom: 20px;">
                <label for="admin_name" style="display: block; margin-bottom: 8px; font-weight: bold;">Admin Name</label>
                <input type="text" name="admin_name" id="admin_name" required 
                    style="width: 100%; padding: 10px; border: 1px solid #DBEAFE; border-radius: 6px;"
                    value="{{ old('admin_name') }}">
                @error('admin_name')
                    <span style="color: red; font-size: 12px;">{{ $message }}</span>
                @enderror
            </div>

            <div style="margin-bottom: 20px;">
                <label for="admin_email" style="display: block; margin-bottom: 8px; font-weight: bold;">Admin Email</label>
                <input type="email" name="admin_email" id="admin_email" required 
                    style="width: 100%; padding: 10px; border: 1px solid #DBEAFE; border-radius: 6px;"
                    value="{{ old('admin_email') }}">
                @error('admin_email')
                    <span style="color: red; font-size: 12px;">{{ $message }}</span>
                @enderror
            </div>

            <div style="margin-bottom: 20px;">
                <label for="admin_password" style="display: block; margin-bottom: 8px; font-weight: bold;">Admin Password</label>
                <div style="position: relative;">
                    <input type="password" name="admin_password" id="admin_password" required 
                        style="width: 100%; padding: 10px; padding-right: 40px; border: 1px solid #DBEAFE; border-radius: 6px;">
                    <i class="fas fa-eye" id="toggleAdminPassword" 
                        style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #6B7280;"
                        onclick="toggleAdminPassword()"></i>
                </div>
                @error('admin_password')
                    <span style="color: red; font-size: 12px;">{{ $message }}</span>
                @enderror
                <p style="margin-top: 6px; color: #475569; font-size: 12px; font-weight: 600;">
                    12-14 characters with uppercase, lowercase, number, and special character.
                </p>
            </div>

            <div style="margin-bottom: 20px;">
                <label for="admin_password_confirmation" style="display: block; margin-bottom: 8px; font-weight: bold;">Confirm Admin Password</label>
                <input type="password" name="admin_password_confirmation" id="admin_password_confirmation" required
                    style="width: 100%; padding: 10px; border: 1px solid #DBEAFE; border-radius: 6px;">
            </div>

            <script>
                function toggleAdminPassword() {
                    const passwordInput = document.getElementById('admin_password');
                    const icon = document.getElementById('toggleAdminPassword');
                    if (passwordInput.type === 'password') {
                        passwordInput.type = 'text';
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                    } else {
                        passwordInput.type = 'password';
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                    }
                }
            </script>
            <hr style="margin: 20px 0; border: 0; border-top: 1px solid #DBEAFE;">

            <div style="margin-bottom: 20px;">
                <label for="status" style="display: block; margin-bottom: 8px; font-weight: bold;">Status</label>
                <select name="status" id="status" required
                    style="width: 100%; padding: 10px; border: 1px solid #DBEAFE; border-radius: 6px;">
                    <option value="trial">Trial</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
                @error('status')
                    <span style="color: red; font-size: 12px;">{{ $message }}</span>
                @enderror
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="submit" 
                    style="padding: 10px 20px; background-color: #2563EB; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">
                    Create Tenant
                </button>
                <a href="{{ route('admin.tenants.index') }}" 
                    style="padding: 10px 20px; background-color: #1E40AF; color: white; text-decoration: none; border-radius: 6px; font-weight: 600;">
                    Cancel
                </a>
            </div>

        </form>
    </div>
@endsection
