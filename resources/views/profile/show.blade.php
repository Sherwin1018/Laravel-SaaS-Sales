@extends('layouts.admin')

@section('title', 'Manage Profile')

@php
    $roleName = optional($user->roles->first())->name ?? ucwords(str_replace('-', ' ', $user->role ?? 'User'));
    $profileNameSource = trim((string) $user->name);
    $profileInitials = collect(preg_split('/\s+/', $profileNameSource))
        ->filter()
        ->take(2)
        ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
        ->implode('');
    $profileInitials = $profileInitials !== '' ? $profileInitials : 'U';
    $profileHue = abs(crc32($profileNameSource ?: 'user')) % 360;
    $profileBg = "hsl({$profileHue}, 65%, 45%)";

    $companyName = optional($user->tenant)->company_name ?? 'No Company';
    $companyInitials = collect(preg_split('/\s+/', trim($companyName)))
        ->filter()
        ->take(2)
        ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
        ->implode('');
    $companyInitials = $companyInitials !== '' ? $companyInitials : 'NC';
    $companyHue = abs(crc32($companyName ?: 'company')) % 360;
    $companyBg = "hsl({$companyHue}, 60%, 42%)";
@endphp

@section('content')
    <div class="top-header">
        <h1>Manage Profile</h1>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 18px;">
        <div class="card">
            <h3>Profile Picture</h3>
            <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 14px;">
                <div style="width: 74px; height: 74px; border-radius: 50%; overflow: hidden; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 24px; background: {{ $profileBg }};">
                    @if($user->profile_photo_path)
                        <img src="{{ asset('storage/' . $user->profile_photo_path) }}" alt="Profile Picture" style="width: 100%; height: 100%; object-fit: cover;">
                    @else
                        {{ $profileInitials }}
                    @endif
                </div>

                <form action="{{ route('profile.avatar.update') }}" method="POST" enctype="multipart/form-data" style="display: flex; gap: 8px; align-items: center;">
                    @csrf
                    <label for="profile_photo" style="width: 34px; height: 34px; border-radius: 50%; background: #0EA5E9; color: white; display: inline-flex; align-items: center; justify-content: center; cursor: pointer;">
                        <i class="fas fa-camera"></i>
                    </label>
                    <input type="file" id="profile_photo" name="profile_photo" accept="image/*" style="display: none;" onchange="this.form.submit()">
                </form>

                <form action="{{ route('profile.avatar.delete') }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" style="padding: 8px 12px; border: none; border-radius: 6px; background: #DC2626; color: white; cursor: pointer;">
                        Delete
                    </button>
                </form>
            </div>
            @error('profile_photo')
                <span style="color: red; font-size: 12px;">{{ $message }}</span>
            @enderror

            <form action="{{ route('profile.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div style="margin-bottom: 14px;">
                    <label for="name" style="display:block;margin-bottom:6px;font-weight:700;">Name</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                        style="width:100%;padding:10px;border:1px solid #DBEAFE;border-radius:6px;">
                </div>

                <div style="margin-bottom: 14px;">
                    <label for="email" style="display:block;margin-bottom:6px;font-weight:700;">Email (Read Only)</label>
                    <input type="email" id="email" value="{{ $user->email }}" readonly
                        style="width:100%;padding:10px;border:1px solid #E2E8F0;border-radius:6px;background:#F8FAFC;">
                </div>

                <div style="margin-bottom: 14px;">
                    <label for="phone" style="display:block;margin-bottom:6px;font-weight:700;">Phone</label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" pattern="^09\d{9}$" maxlength="11"
                        placeholder="09XXXXXXXXX"
                        style="width:100%;padding:10px;border:1px solid #DBEAFE;border-radius:6px;">
                </div>

                <div style="margin-bottom: 14px;">
                    <label for="secondary_phone" style="display:block;margin-bottom:6px;font-weight:700;">Secondary Phone</label>
                    <input type="text" id="secondary_phone" name="secondary_phone" value="{{ old('secondary_phone', $user->secondary_phone) }}" pattern="^09\d{9}$" maxlength="11"
                        placeholder="{{ $user->secondary_phone ?: 'N/A' }}"
                        style="width:100%;padding:10px;border:1px solid #DBEAFE;border-radius:6px;">
                    <label style="margin-top:6px;display:flex;align-items:center;gap:8px;font-size:12px;font-weight:600;color:#475569;">
                        <input type="checkbox" name="remove_secondary_phone" value="1"> Delete secondary phone
                    </label>
                </div>

                <button type="submit" style="padding:10px 16px;border:none;border-radius:6px;background:#2563EB;color:#fff;cursor:pointer;">
                    Save Profile
                </button>
            </form>
        </div>

        <div class="card">
            <h3>Account Details</h3>
            <div style="margin-bottom: 12px; font-weight: 700; color: #334155;">Role (Read Only): {{ $roleName }}</div>
            <div style="margin-bottom: 12px; font-weight: 700; color: #334155;">Last Login: {{ optional($user->last_login_at)->format('Y-m-d H:i') ?? 'N/A' }}</div>
            <div style="margin-bottom: 16px; font-weight: 700; color: #334155;">Account Created Date: {{ $user->created_at->format('Y-m-d H:i') }}</div>

            <div style="margin-bottom: 16px; padding: 12px; border: 1px solid #E2E8F0; border-radius: 8px;">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <span style="font-weight: 700;">Notification Toggle (Static)</span>
                    <label style="display:inline-block;position:relative;width:44px;height:24px;">
                        <input type="checkbox" checked disabled style="opacity:0;width:0;height:0;">
                        <span style="position:absolute;inset:0;background:#2563EB;border-radius:999px;"></span>
                        <span style="position:absolute;top:3px;left:22px;width:18px;height:18px;background:#fff;border-radius:50%;"></span>
                    </label>
                </div>
            </div>

            <button type="button" id="togglePasswordSection" style="padding:10px 16px;border:none;border-radius:6px;background:#0EA5E9;color:#fff;cursor:pointer;margin-bottom:12px;">
                Change Password
            </button>

            <form id="passwordFormSection" action="{{ route('profile.password.update') }}" method="POST" style="display:none;">
                @csrf
                @method('PUT')
                <div style="margin-bottom: 12px;">
                    <label style="display:block;margin-bottom:6px;font-weight:700;">Old Password</label>
                    <input type="password" name="old_password" required style="width:100%;padding:10px;border:1px solid #DBEAFE;border-radius:6px;">
                </div>
                <div style="margin-bottom: 12px;">
                    <label style="display:block;margin-bottom:6px;font-weight:700;">New Password</label>
                    <input type="password" name="new_password" required style="width:100%;padding:10px;border:1px solid #DBEAFE;border-radius:6px;">
                </div>
                <div style="margin-bottom: 12px;">
                    <label style="display:block;margin-bottom:6px;font-weight:700;">Confirm New Password</label>
                    <input type="password" name="new_password_confirmation" required style="width:100%;padding:10px;border:1px solid #DBEAFE;border-radius:6px;">
                </div>
                <p style="margin-bottom: 12px; color:#475569; font-size:12px; font-weight:600;">12-14 chars, uppercase, lowercase, number, and special character.</p>
                <button type="submit" style="padding:10px 16px;border:none;border-radius:6px;background:#2563EB;color:#fff;cursor:pointer;">
                    Confirm Password Change
                </button>
            </form>
        </div>
    </div>

    @if($user->tenant)
        <div class="card" style="margin-top: 18px;">
            <h3>Company</h3>
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:10px;">
                <div style="width:58px;height:58px;border-radius:50%;overflow:hidden;display:flex;align-items:center;justify-content:center;color:#fff;font-size:20px;font-weight:700;background:{{ $companyBg }};">
                    @if($user->tenant->logo_path)
                        <img src="{{ asset('storage/' . $user->tenant->logo_path) }}" alt="Company Logo" style="width:100%;height:100%;object-fit:cover;">
                    @else
                        {{ $companyInitials }}
                    @endif
                </div>
                <div style="font-weight:700;">{{ $companyName }}</div>
            </div>

            @if($user->hasRole('account-owner'))
                <div style="display:flex; gap:8px;">
                    <form action="{{ route('profile.company-logo.update') }}" method="POST" enctype="multipart/form-data" style="display:flex;align-items:center;gap:8px;">
                        @csrf
                        <label for="company_logo" style="padding:8px 12px;border:none;border-radius:6px;background:#0EA5E9;color:#fff;cursor:pointer;">
                            <i class="fas fa-camera"></i> Upload Company Logo
                        </label>
                        <input id="company_logo" type="file" name="company_logo" accept="image/*" style="display:none;" onchange="this.form.submit()">
                    </form>
                    <form action="{{ route('profile.company-logo.delete') }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" style="padding:8px 12px;border:none;border-radius:6px;background:#DC2626;color:#fff;cursor:pointer;">
                            Delete Company Logo
                        </button>
                    </form>
                </div>
            @endif
        </div>
    @endif
@endsection

@section('scripts')
    <script>
        const togglePasswordSectionBtn = document.getElementById('togglePasswordSection');
        const passwordFormSection = document.getElementById('passwordFormSection');

        if (togglePasswordSectionBtn && passwordFormSection) {
            togglePasswordSectionBtn.addEventListener('click', function () {
                passwordFormSection.style.display = passwordFormSection.style.display === 'none' ? 'block' : 'none';
            });
        }
    </script>
@endsection
