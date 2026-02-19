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
                    <label for="profile_photo" style="width: 34px; height: 34px; border-radius: 50%; background: var(--theme-accent, #0EA5E9); color: white; display: inline-flex; align-items: center; justify-content: center; cursor: pointer;">
                        <i class="fas fa-camera"></i>
                    </label>
                    <input type="file" id="profile_photo" name="profile_photo" accept="image/*" style="display: none;" onchange="this.form.submit()">
                </form>

                <form action="{{ route('profile.avatar.delete') }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" style="padding: 8px 12px; border: none; border-radius: 6px; background: #DC2626; color: white; cursor: pointer; font-weight: 600;">
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
                    <label for="email" style="display:block;margin-bottom:6px;font-weight:700;">Email</label>
                    <input type="email" id="email" value="{{ $user->email }}" readonly
                        style="width:100%;padding:10px;border:1px solid #E2E8F0;border-radius:6px;background:#F8FAFC;">
                </div>

                <div style="margin-bottom: 14px;">
                    <label for="phone" style="display:block;margin-bottom:6px;font-weight:700;">Phone</label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" pattern="^09\d{9}$" maxlength="11" minlength="11" inputmode="numeric"
                        placeholder="09XXXXXXXXX"
                        style="width:100%;padding:10px;border:1px solid #DBEAFE;border-radius:6px;">
                    <p style="margin-top: 6px; color: #475569; font-size: 12px; font-weight: 600;">
                        Enter an 11-digit Philippine number starting with 09 (numbers only).
                    </p>
                </div>

                <div style="margin-bottom: 14px;">
                    <label for="secondary_phone" style="display:block;margin-bottom:6px;font-weight:700;">Secondary Phone</label>
                    <input type="text" id="secondary_phone" name="secondary_phone" value="{{ old('secondary_phone', $user->secondary_phone) }}" pattern="^09\d{9}$" maxlength="11" minlength="11" inputmode="numeric"
                        placeholder="{{ $user->secondary_phone ?: '09XXXXXXXXX' }}"
                        style="width:100%;padding:10px;border:1px solid #DBEAFE;border-radius:6px;">
                    <p style="margin-top: 6px; color: #475569; font-size: 12px; font-weight: 600;">
                        Enter an 11-digit Philippine number starting with 09 (numbers only).
                    </p>
                    <label style="margin-top:6px;display:flex;align-items:center;gap:8px;font-size:12px;font-weight:600;color:#475569;">
                        <input type="checkbox" name="remove_secondary_phone" value="1"> Delete secondary phone
                    </label>
                </div>

                <button type="submit" style="padding:10px 16px;border:none;border-radius:6px;background:var(--theme-primary, #2563EB);color:#fff;cursor:pointer;font-weight:600;">
                    Save Profile
                </button>
            </form>
        </div>

        <div class="card">
            <h3>Account Details</h3>
            <div style="margin-bottom: 12px; font-weight: 700; color: #334155;">Role: {{ $roleName }}</div>
            <div style="margin-bottom: 12px; font-weight: 700; color: #334155;">Last Login: {{ optional($user->last_login_at)->format('Y-m-d H:i') ?? 'N/A' }}</div>
            <div style="margin-bottom: 16px; font-weight: 700; color: #334155;">Account Created Date: {{ $user->created_at->format('Y-m-d H:i') }}</div>

            <div style="margin-bottom: 16px; padding: 12px; border: 1px solid #E2E8F0; border-radius: 8px;">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <span style="font-weight: 700;">Notification Toggle (Static)</span>
                    <label style="display:inline-block;position:relative;width:44px;height:24px;">
                        <input type="checkbox" checked disabled style="opacity:0;width:0;height:0;">
                        <span style="position:absolute;inset:0;background:var(--theme-primary, #2563EB);border-radius:999px;"></span>
                        <span style="position:absolute;top:3px;left:22px;width:18px;height:18px;background:#fff;border-radius:50%;"></span>
                    </label>
                </div>
            </div>

            <button type="button" id="openPasswordModal" style="padding:10px 16px;border:none;border-radius:6px;background:var(--theme-accent, #0EA5E9);color:#fff;cursor:pointer;margin-bottom:12px;font-weight:600;">
                Change Password
            </button>
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
                <div style="display:flex; gap:8px; align-items:center;">
                    <form action="{{ route('profile.company-logo.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <label for="company_logo" style="display:inline-block;padding:8px 12px;border:none;border-radius:6px;background:var(--theme-accent, #0EA5E9);color:#fff;cursor:pointer;font-weight:600;font-size:14px;height:38px;box-sizing:border-box;line-height:22px;vertical-align:middle;">
                            <i class="fas fa-camera"></i> Upload Company Logo
                        </label>
                        <input id="company_logo" type="file" name="company_logo" accept="image/*" style="display:none;" onchange="this.form.submit()">
                    </form>
                    <form action="{{ route('profile.company-logo.delete') }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" style="padding:8px 12px;border:none;border-radius:6px;background:#DC2626;color:#fff;cursor:pointer;font-weight:600;font-size:14px;height:38px;box-sizing:border-box;line-height:22px;">
                            Delete Company Logo
                        </button>
                    </form>
                </div>
            @endif
        </div>
    @endif

    @if($user->tenant && $user->hasRole('account-owner'))
        <div class="card" style="margin-top: 18px;">
            <h3>Company Theme</h3>
            <form action="{{ route('profile.theme.update') }}" method="POST">
                @csrf
                @method('PUT')
                @php
                    $currentTheme = [
                        'primary' => old('theme_primary_color', $user->tenant->theme_primary_color ?? '#2563EB'),
                        'accent' => old('theme_accent_color', $user->tenant->theme_accent_color ?? '#0EA5E9'),
                        'sidebar_bg' => old('theme_sidebar_bg', $user->tenant->theme_sidebar_bg ?? '#FFFFFF'),
                        'sidebar_text' => old('theme_sidebar_text', $user->tenant->theme_sidebar_text ?? '#1E40AF'),
                    ];

                    $recommendedThemes = [
                        [
                            'name' => 'Classic Blue (Default)',
                            'primary' => '#2563EB',
                            'accent' => '#0EA5E9',
                            'sidebar_bg' => '#FFFFFF',
                            'sidebar_text' => '#1E40AF',
                        ],
                        [
                            'name' => 'Emerald',
                            'primary' => '#047857',
                            'accent' => '#10B981',
                            'sidebar_bg' => '#FFFFFF',
                            'sidebar_text' => '#064E3B',
                        ],
                        [
                            'name' => 'Violet',
                            'primary' => '#6D28D9',
                            'accent' => '#A78BFA',
                            'sidebar_bg' => '#FFFFFF',
                            'sidebar_text' => '#4C1D95',
                        ],
                        [
                            'name' => 'Slate',
                            'primary' => '#334155',
                            'accent' => '#0EA5E9',
                            'sidebar_bg' => '#F8FAFC',
                            'sidebar_text' => '#0F172A',
                        ],
                        [
                            'name' => 'Sunset',
                            'primary' => '#C2410C',
                            'accent' => '#F97316',
                            'sidebar_bg' => '#FFF7ED',
                            'sidebar_text' => '#7C2D12',
                        ],
                        [
                            'name' => 'Rose',
                            'primary' => '#BE123C',
                            'accent' => '#FB7185',
                            'sidebar_bg' => '#FFF1F2',
                            'sidebar_text' => '#881337',
                        ],
                    ];
                @endphp

                <p style="margin-top: 10px; color:#475569; font-size: 13px; font-weight: 600; line-height: 1.35;">
                    Customize your company’s colors. These settings apply to <strong>all users in your company</strong> automatically.
                    Team members cannot change the theme.
                </p>

                <div style="margin-top: 14px;">
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
                        <h4 style="margin:0;color:#1F2937;">Recommended themes</h4>
                        <button type="button" id="themeResetDefault"
                            style="padding:8px 12px;border:1px solid #E2E8F0;border-radius:6px;background:#F8FAFC;color:#334155;cursor:pointer;font-weight:600;">
                            Reset to default
                        </button>
                    </div>

                    <div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px;margin-top:10px;">
                        @foreach($recommendedThemes as $preset)
                            <button type="button" class="theme-preset-card"
                                data-primary="{{ $preset['primary'] }}"
                                data-accent="{{ $preset['accent'] }}"
                                data-sidebar-bg="{{ $preset['sidebar_bg'] }}"
                                data-sidebar-text="{{ $preset['sidebar_text'] }}"
                                style="text-align:left;padding:12px;border:1px solid #E2E8F0;border-radius:10px;background:#fff;cursor:pointer;">
                                <div style="font-weight:800;color:#0F172A;margin-bottom:8px;font-size:13px;">
                                    {{ $preset['name'] }}
                                </div>
                                <div style="display:flex;gap:6px;align-items:center;">
                                    <span title="Primary" style="width:18px;height:18px;border-radius:6px;background:{{ $preset['primary'] }};border:1px solid #E2E8F0;"></span>
                                    <span title="Accent" style="width:18px;height:18px;border-radius:6px;background:{{ $preset['accent'] }};border:1px solid #E2E8F0;"></span>
                                    <span title="Sidebar BG" style="width:18px;height:18px;border-radius:6px;background:{{ $preset['sidebar_bg'] }};border:1px solid #E2E8F0;"></span>
                                    <span title="Sidebar Text" style="width:18px;height:18px;border-radius:6px;background:{{ $preset['sidebar_text'] }};border:1px solid #E2E8F0;"></span>
                                </div>
                                <div style="margin-top:8px;color:#64748B;font-size:12px;font-weight:600;">
                                    Click to apply
                                </div>
                            </button>
                        @endforeach
                    </div>
                </div>

                <hr style="border:0;border-top:1px solid #DBEAFE;margin:16px 0;">

                <h4 style="margin:0 0 8px;color:#1F2937;">Custom colors</h4>
                <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;">
                    <div class="theme-field">
                        <label style="display:block;margin-bottom:6px;font-weight:800;color:#0F172A;">Primary color</label>
                        <div style="display:flex;gap:10px;align-items:center;">
                            <input type="color" id="themePrimaryPicker" name="theme_primary_color" value="{{ $currentTheme['primary'] }}"
                                   style="width:52px;height:38px;padding:0;border:1px solid #E2E8F0;border-radius:10px;cursor:pointer;">
                            <input type="text" id="themePrimaryHex" value="{{ $currentTheme['primary'] }}" inputmode="text" maxlength="7"
                                   style="flex:1;padding:10px;border:1px solid #DBEAFE;border-radius:8px;font-weight:700;color:#0F172A;"
                                   placeholder="#RRGGBB">
                        </div>
                        <div style="margin-top:6px;color:#64748B;font-size:12px;font-weight:600;">Used for buttons and highlights.</div>
                    </div>

                    <div class="theme-field">
                        <label style="display:block;margin-bottom:6px;font-weight:800;color:#0F172A;">Accent color</label>
                        <div style="display:flex;gap:10px;align-items:center;">
                            <input type="color" id="themeAccentPicker" name="theme_accent_color" value="{{ $currentTheme['accent'] }}"
                                   style="width:52px;height:38px;padding:0;border:1px solid #E2E8F0;border-radius:10px;cursor:pointer;">
                            <input type="text" id="themeAccentHex" value="{{ $currentTheme['accent'] }}" inputmode="text" maxlength="7"
                                   style="flex:1;padding:10px;border:1px solid #DBEAFE;border-radius:8px;font-weight:700;color:#0F172A;"
                                   placeholder="#RRGGBB">
                        </div>
                        <div style="margin-top:6px;color:#64748B;font-size:12px;font-weight:600;">Used for secondary actions and accents.</div>
                    </div>

                    <div class="theme-field">
                        <label style="display:block;margin-bottom:6px;font-weight:800;color:#0F172A;">Sidebar background</label>
                        <div style="display:flex;gap:10px;align-items:center;">
                            <input type="color" id="themeSidebarBgPicker" name="theme_sidebar_bg" value="{{ $currentTheme['sidebar_bg'] }}"
                                   style="width:52px;height:38px;padding:0;border:1px solid #E2E8F0;border-radius:10px;cursor:pointer;">
                            <input type="text" id="themeSidebarBgHex" value="{{ $currentTheme['sidebar_bg'] }}" inputmode="text" maxlength="7"
                                   style="flex:1;padding:10px;border:1px solid #DBEAFE;border-radius:8px;font-weight:700;color:#0F172A;"
                                   placeholder="#RRGGBB">
                        </div>
                        <div style="margin-top:6px;color:#64748B;font-size:12px;font-weight:600;">Sidebar panel background.</div>
                    </div>

                    <div class="theme-field">
                        <label style="display:block;margin-bottom:6px;font-weight:800;color:#0F172A;">Sidebar text</label>
                        <div style="display:flex;gap:10px;align-items:center;">
                            <input type="color" id="themeSidebarTextPicker" name="theme_sidebar_text" value="{{ $currentTheme['sidebar_text'] }}"
                                   style="width:52px;height:38px;padding:0;border:1px solid #E2E8F0;border-radius:10px;cursor:pointer;">
                            <input type="text" id="themeSidebarTextHex" value="{{ $currentTheme['sidebar_text'] }}" inputmode="text" maxlength="7"
                                   style="flex:1;padding:10px;border:1px solid #DBEAFE;border-radius:8px;font-weight:700;color:#0F172A;"
                                   placeholder="#RRGGBB">
                        </div>
                        <div style="margin-top:6px;color:#64748B;font-size:12px;font-weight:600;">Sidebar menu text.</div>
                    </div>
                </div>

                <button type="submit"
                    style="margin-top:14px;padding:10px 16px;border:none;border-radius:6px;background:var(--theme-primary, #2563EB);color:#fff;cursor:pointer;font-weight:600;">
                    Save Theme
                </button>
            </form>
        </div>
    @endif

    {{-- Modal: Change Password --}}
    <div id="passwordModal" class="modal-overlay" style="display: none;">
        <div class="modal-box password-modal-box">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h3 style="margin: 0;">Change Password</h3>
                <button type="button" id="closePasswordModal" class="modal-close-btn">&times;</button>
            </div>
            <form id="passwordModalForm" action="{{ route('profile.password.update') }}" method="POST">
                @csrf
                @method('PUT')
                <div style="margin-bottom: 16px;">
                    <label for="old_password" style="display: block; margin-bottom: 6px; font-weight: 600;">Old Password</label>
                    <div style="position: relative;">
                        <input type="password" id="old_password" name="old_password" required
                            style="width: 100%; padding: 10px; padding-right: 40px; border: 1px solid #E2E8F0; border-radius: 6px;">
                        <i class="fas fa-eye toggle-password" id="toggleOldPassword"
                            style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #64748B;"></i>
                    </div>
                </div>
                <div style="margin-bottom: 16px;">
                    <label for="new_password" style="display: block; margin-bottom: 6px; font-weight: 600;">New Password</label>
                    <div style="position: relative;">
                        <input type="password" id="new_password" name="new_password" required
                            style="width: 100%; padding: 10px; padding-right: 40px; border: 1px solid #E2E8F0; border-radius: 6px;">
                        <i class="fas fa-eye toggle-password" id="toggleNewPassword"
                            style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #64748B;"></i>
                    </div>
                </div>
                <div style="margin-bottom: 16px;">
                    <label for="new_password_confirmation" style="display: block; margin-bottom: 6px; font-weight: 600;">Confirm New Password</label>
                    <div style="position: relative;">
                        <input type="password" id="new_password_confirmation" name="new_password_confirmation" required
                            style="width: 100%; padding: 10px; padding-right: 40px; border: 1px solid #E2E8F0; border-radius: 6px;">
                        <i class="fas fa-eye toggle-password" id="toggleNewPasswordConfirmation"
                            style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #64748B;"></i>
                    </div>
                </div>
                <p style="margin-bottom: 16px; color: #475569; font-size: 12px; font-weight: 600;">
                    12-14 chars, uppercase, lowercase, number, and special character.
                </p>
                <div style="display: flex; gap: 8px;">
                    <button type="submit" style="padding: 8px 16px; background-color: var(--theme-primary, #2563EB); color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">
                        Confirm Password Change
                    </button>
                    <button type="button" id="cancelPasswordModal" style="padding: 8px 16px; background-color: #E2E8F0; color: #475569; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <style>
        .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; padding: 20px; }
        .modal-box { background: #fff; border-radius: 8px; padding: 24px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); }
        .password-modal-box { width: 100%; max-width: 480px; }
        .modal-close-btn { background: none; border: none; font-size: 28px; cursor: pointer; color: #64748B; line-height: 1; padding: 0 4px; }
        .modal-close-btn:hover { color: #1E293B; }
    </style>
@endsection

@section('scripts')
    <script>
        // Password Modal
        var passwordModal = document.getElementById('passwordModal');
        var openPasswordModal = document.getElementById('openPasswordModal');
        var closePasswordModal = document.getElementById('closePasswordModal');
        var cancelPasswordModal = document.getElementById('cancelPasswordModal');
        var passwordModalForm = document.getElementById('passwordModalForm');

        if (openPasswordModal && passwordModal) {
            openPasswordModal.addEventListener('click', function() {
                passwordModal.style.display = 'flex';
                if (passwordModalForm) {
                    passwordModalForm.reset();
                }
            });
        }

        function closePasswordModalFunc() {
            if (passwordModal) passwordModal.style.display = 'none';
            if (passwordModalForm) passwordModalForm.reset();
        }

        if (closePasswordModal) closePasswordModal.addEventListener('click', closePasswordModalFunc);
        if (cancelPasswordModal) cancelPasswordModal.addEventListener('click', closePasswordModalFunc);
        if (passwordModal) {
            passwordModal.addEventListener('click', function(e) {
                if (e.target === passwordModal) closePasswordModalFunc();
            });
        }

        function bindPasswordToggle(toggleId, inputId) {
            var toggle = document.getElementById(toggleId);
            var input = document.getElementById(inputId);
            if (!toggle || !input) return;

            toggle.addEventListener('click', function () {
                var type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', type);
                if (type === 'text') {
                    toggle.classList.remove('fa-eye');
                    toggle.classList.add('fa-eye-slash');
                } else {
                    toggle.classList.remove('fa-eye-slash');
                    toggle.classList.add('fa-eye');
                }
            });
        }

        bindPasswordToggle('toggleOldPassword', 'old_password');
        bindPasswordToggle('toggleNewPassword', 'new_password');
        bindPasswordToggle('toggleNewPasswordConfirmation', 'new_password_confirmation');

        // Phone fields - numbers only
        var phone = document.getElementById('phone');
        var secondaryPhone = document.getElementById('secondary_phone');

        function restrictToNumbers(input) {
            if (!input) return;
            input.addEventListener('input', function() {
                this.value = this.value.replace(/\D/g, '');
            });
            input.addEventListener('keypress', function(e) {
                if (!/[\d]/.test(e.key) && !e.ctrlKey && !e.metaKey && e.key !== 'Backspace' && e.key !== 'Tab') {
                    e.preventDefault();
                }
            });
        }

        restrictToNumbers(phone);
        restrictToNumbers(secondaryPhone);

        // Theme presets + hex syncing (Account Owner only)
        function isValidHex(hex) {
            return /^#[0-9A-Fa-f]{6}$/.test(hex);
        }

        function bindColorAndHex(pickerId, hexId) {
            var picker = document.getElementById(pickerId);
            var hex = document.getElementById(hexId);
            if (!picker || !hex) return;

            picker.addEventListener('input', function () {
                hex.value = picker.value.toUpperCase();
            });

            hex.addEventListener('input', function () {
                var v = hex.value.trim();
                if (v.length === 7 && isValidHex(v)) {
                    picker.value = v;
                }
            });

            hex.addEventListener('blur', function () {
                var v = hex.value.trim();
                if (v === '') {
                    hex.value = picker.value.toUpperCase();
                    return;
                }
                if (!isValidHex(v)) {
                    hex.value = picker.value.toUpperCase();
                } else {
                    hex.value = v.toUpperCase();
                    picker.value = v.toUpperCase();
                }
            });
        }

        function applyThemeValues(values) {
            var map = [
                { picker: 'themePrimaryPicker', hex: 'themePrimaryHex', value: values.primary },
                { picker: 'themeAccentPicker', hex: 'themeAccentHex', value: values.accent },
                { picker: 'themeSidebarBgPicker', hex: 'themeSidebarBgHex', value: values.sidebarBg },
                { picker: 'themeSidebarTextPicker', hex: 'themeSidebarTextHex', value: values.sidebarText },
            ];
            map.forEach(function (item) {
                var p = document.getElementById(item.picker);
                var h = document.getElementById(item.hex);
                if (p) p.value = item.value;
                if (h) h.value = item.value.toUpperCase();
            });
        }

        bindColorAndHex('themePrimaryPicker', 'themePrimaryHex');
        bindColorAndHex('themeAccentPicker', 'themeAccentHex');
        bindColorAndHex('themeSidebarBgPicker', 'themeSidebarBgHex');
        bindColorAndHex('themeSidebarTextPicker', 'themeSidebarTextHex');

        document.querySelectorAll('.theme-preset-card').forEach(function (btn) {
            btn.addEventListener('click', function () {
                applyThemeValues({
                    primary: btn.getAttribute('data-primary'),
                    accent: btn.getAttribute('data-accent'),
                    sidebarBg: btn.getAttribute('data-sidebar-bg'),
                    sidebarText: btn.getAttribute('data-sidebar-text'),
                });
            });
        });

        var resetBtn = document.getElementById('themeResetDefault');
        if (resetBtn) {
            resetBtn.addEventListener('click', function () {
                applyThemeValues({
                    primary: '#2563EB',
                    accent: '#0EA5E9',
                    sidebarBg: '#FFFFFF',
                    sidebarText: '#1E40AF',
                });
            });
        }
    </script>
@endsection
