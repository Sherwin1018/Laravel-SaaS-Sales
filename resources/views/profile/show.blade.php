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

    <div class="app-grid app-grid--2" style="gap:18px;">
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
                    <label for="profile_photo" style="width: 34px; height: 34px; border-radius: 50%; background: var(--theme-accent, #6B4A7A); color: white; display: inline-flex; align-items: center; justify-content: center; cursor: pointer;">
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
                        style="width:100%;padding:10px;border:1px solid var(--theme-border, #E6E1EF);border-radius:6px;">
                </div>

                <div style="margin-bottom: 14px;">
                    <label for="email" style="display:block;margin-bottom:6px;font-weight:700;">Email</label>
                    <input type="email" id="email" value="{{ $user->email }}" readonly
                        style="width:100%;padding:10px;border:1px solid var(--theme-border, #E6E1EF);border-radius:6px;background:var(--theme-surface-softer, #F7F7FB);">
                </div>

                <div style="margin-bottom: 14px;">
                    <label for="phone" style="display:block;margin-bottom:6px;font-weight:700;">Phone</label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" pattern="^09\d{9}$" maxlength="11" minlength="11" inputmode="numeric"
                        placeholder="09XXXXXXXXX"
                        style="width:100%;padding:10px;border:1px solid var(--theme-border, #E6E1EF);border-radius:6px;">
                    <p style="margin-top: 6px; color: var(--theme-muted, #6B7280); font-size: 12px; font-weight: 600;">
                        Enter an 11-digit Philippine number starting with 09 (numbers only).
                    </p>
                </div>

                <div style="margin-bottom: 14px;">
                    <label for="secondary_phone" style="display:block;margin-bottom:6px;font-weight:700;">Secondary Phone</label>
                    <input type="text" id="secondary_phone" name="secondary_phone" value="{{ old('secondary_phone', $user->secondary_phone) }}" pattern="^09\d{9}$" maxlength="11" minlength="11" inputmode="numeric"
                        placeholder="{{ $user->secondary_phone ?: '09XXXXXXXXX' }}"
                        style="width:100%;padding:10px;border:1px solid var(--theme-border, #E6E1EF);border-radius:6px;">
                    <p style="margin-top: 6px; color: var(--theme-muted, #6B7280); font-size: 12px; font-weight: 600;">
                        Enter an 11-digit Philippine number starting with 09 (numbers only).
                    </p>
                    <label style="margin-top:6px;display:flex;align-items:center;gap:8px;font-size:12px;font-weight:600;color:var(--theme-muted, #6B7280);">
                        <input type="checkbox" name="remove_secondary_phone" value="1"> Delete secondary phone
                    </label>
                </div>

                <button type="submit" style="padding:10px 16px;border:none;border-radius:6px;background:var(--theme-primary, #240E35);color:#fff;cursor:pointer;font-weight:600;">
                    Save Profile
                </button>
            </form>
        </div>

        <div class="card">
            <h3>Account Details</h3>
            <div style="margin-bottom: 12px; font-weight: 700; color: var(--theme-muted, #6B7280);">Role: {{ $roleName }}</div>
            <div style="margin-bottom: 12px; font-weight: 700; color: var(--theme-muted, #6B7280);">Last Login: {{ optional($user->last_login_at)->format('Y-m-d H:i') ?? $emptyDash }}</div>
            <div style="margin-bottom: 16px; font-weight: 700; color: var(--theme-muted, #6B7280);">Account Created Date: {{ $user->created_at->format('Y-m-d H:i') }}</div>

            <div style="margin-bottom: 16px; padding: 12px; border: 1px solid var(--theme-border, #E6E1EF); border-radius: 8px;">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <span style="font-weight: 700;">Notification Toggle</span>
                    <label style="display:inline-block;position:relative;width:44px;height:24px;">
                        <input type="checkbox" checked disabled style="opacity:0;width:0;height:0;">
                        <span style="position:absolute;inset:0;background:var(--theme-primary, #240E35);border-radius:999px;"></span>
                        <span style="position:absolute;top:3px;left:22px;width:18px;height:18px;background:#fff;border-radius:50%;"></span>
                    </label>
                </div>
            </div>

            <button type="button" id="openPasswordModal" style="padding:10px 16px;border:none;border-radius:6px;background:var(--theme-accent, #6B4A7A);color:#fff;cursor:pointer;margin-bottom:12px;font-weight:600;">
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

            <div style="display:flex; gap:8px; align-items:center;">
                <form action="{{ route('profile.company-logo.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <label for="company_logo" style="display:inline-block;padding:8px 12px;border:none;border-radius:6px;background:var(--theme-accent, #6B4A7A);color:#fff;cursor:pointer;font-weight:600;font-size:14px;height:38px;box-sizing:border-box;line-height:22px;vertical-align:middle;">
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
        </div>
    @endif

    @if($user->tenant && $user->hasRole('account-owner'))
        @php
            $payoutAccount = $user->tenant->defaultPayoutAccount;
            $payoutMeta = is_array($payoutAccount?->meta) ? $payoutAccount->meta : [];
            $payoutCardTypes = ['bank_transfer', 'provider_managed'];

            $maskPreviewName = function (?string $value): string {
                $value = trim((string) $value);
                if ($value === '') {
                    return '';
                }

                if (preg_match('/[*xX]/u', $value)) {
                    return $value;
                }

                return collect(preg_split('/\s+/', $value))
                    ->filter()
                    ->map(function ($part) {
                        $length = mb_strlen($part);

                        if ($length <= 1) {
                            return '*';
                        }

                        if ($length === 2) {
                            return mb_substr($part, 0, 1) . '*';
                        }

                        return mb_substr($part, 0, 1) . str_repeat('*', $length - 2) . mb_substr($part, -1);
                    })
                    ->implode(' ');
            };

            $maskPreviewValue = function (?string $value): string {
                $value = trim((string) $value);
                if ($value === '') {
                    return '';
                }

                if (preg_match('/[*xX]/u', $value)) {
                    return $value;
                }

                $compact = preg_replace('/\s+/', '', $value);
                $length = mb_strlen($compact);

                if ($length <= 1) {
                    return '*';
                }

                if ($length <= 4) {
                    return str_repeat('*', $length - 1) . mb_substr($compact, -1);
                }

                return str_repeat('*', $length - 4) . mb_substr($compact, -4);
            };

            $gcashSource = [
                'account_name' => trim((string) data_get(
                    $payoutMeta,
                    'gcash.account_name',
                    $payoutAccount?->destination_type === 'gcash' ? ($payoutAccount?->account_name ?? '') : ''
                )),
                'identifier' => trim((string) data_get(
                    $payoutMeta,
                    'gcash.masked_destination',
                    $payoutAccount?->destination_type === 'gcash' ? ($payoutAccount?->masked_destination ?? '') : ''
                )),
                'reference' => trim((string) data_get(
                    $payoutMeta,
                    'gcash.reference',
                    $payoutAccount?->destination_type === 'gcash' ? ($payoutAccount?->provider_destination_reference ?? '') : ''
                )),
            ];

            $cardSource = [
                'account_name' => trim((string) data_get(
                    $payoutMeta,
                    'card.account_name',
                    in_array($payoutAccount?->destination_type, $payoutCardTypes, true) ? ($payoutAccount?->account_name ?? '') : ''
                )),
                'identifier' => trim((string) data_get(
                    $payoutMeta,
                    'card.masked_destination',
                    in_array($payoutAccount?->destination_type, $payoutCardTypes, true) ? ($payoutAccount?->masked_destination ?? '') : ''
                )),
                'reference' => trim((string) data_get(
                    $payoutMeta,
                    'card.reference',
                    in_array($payoutAccount?->destination_type, $payoutCardTypes, true) ? ($payoutAccount?->provider_destination_reference ?? '') : ''
                )),
            ];

            $gcashPreview = [
                'account_name' => $maskPreviewName($gcashSource['account_name']),
                'identifier' => $maskPreviewValue($gcashSource['identifier']),
                'reference' => $gcashSource['reference'],
            ];

            $cardPreview = [
                'account_name' => $maskPreviewName($cardSource['account_name']),
                'identifier' => $maskPreviewValue($cardSource['identifier']),
                'reference' => $cardSource['reference'],
            ];

            $hasGcashPreview = collect($gcashPreview)->contains(fn ($value) => trim((string) $value) !== '');
            $hasCardPreview = collect($cardPreview)->contains(fn ($value) => trim((string) $value) !== '');
        @endphp
        <div class="card" style="margin-top: 18px;">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
                <h3 style="margin:0;">Payout Account</h3>
                <button type="button" id="openPayoutPreviewModal"
                    data-default-preview-target="{{ $payoutAccount?->destination_type === 'gcash' ? 'payoutPreviewGcash' : 'payoutPreviewCard' }}"
                    style="padding:10px 16px;border:1px solid var(--theme-border, #E6E1EF);border-radius:8px;background:#fff;color:var(--theme-primary, #240E35);cursor:pointer;font-weight:700;">
                    View
                </button>
            </div>
            <p style="margin-top: 10px; color:var(--theme-muted, #6B7280); font-size: 13px; font-weight: 600; line-height: 1.5;">
                Funnel earnings settle to this verified destination. We only store masked or provider-managed payout details for display and operations.
            </p>

            <form id="payoutAccountForm" action="{{ route('profile.payout.update') }}" method="POST" style="margin-top: 14px;">
                @csrf
                @method('PUT')

                <div class="app-form-grid app-form-grid--2" style="gap:12px;">
                    <div>
                        <label for="destination_type" style="display:block;margin-bottom:6px;font-weight:800;color:#0F172A;">Destination type</label>
                        <select id="destination_type" name="destination_type"
                            style="width:100%;padding:10px;border:1px solid var(--theme-border, #E6E1EF);border-radius:8px;">
                            @foreach(['gcash' => 'GCash', 'provider_managed' => 'Provider Managed', 'bank_transfer' => 'Bank Transfer'] as $value => $label)
                                <option value="{{ $value }}" {{ old('destination_type', $payoutAccount?->destination_type ?? 'gcash') === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="account_name" style="display:block;margin-bottom:6px;font-weight:800;color:#0F172A;">Account name</label>
                        <input type="text" id="account_name" name="account_name"
                            value="{{ old('account_name', $payoutAccount?->account_name ?? '') }}"
                            style="width:100%;padding:10px;border:1px solid var(--theme-border, #E6E1EF);border-radius:8px;">
                    </div>

                    <div>
                        <label for="destination_value" style="display:block;margin-bottom:6px;font-weight:800;color:#0F172A;">GCash / bank number </label>
                        <input type="text" id="destination_value" name="destination_value"
                            value="{{ old('destination_value', '') }}"
                            placeholder="Enter a new destination only when updating"
                            style="width:100%;padding:10px;border:1px solid var(--theme-border, #E6E1EF);border-radius:8px;">
                        <div style="margin-top:6px;color:var(--theme-muted, #6B7280);font-size:12px;font-weight:600;">
                            Current masked destination: {{ $payoutAccount?->masked_destination ?? $emptyDash }}
                        </div>
                    </div>

                    <div>
                        <label for="provider_destination_reference" style="display:block;margin-bottom:6px;font-weight:800;color:#0F172A;">Provider-managed reference</label>
                        <input type="text" id="provider_destination_reference" name="provider_destination_reference"
                            value="{{ old('provider_destination_reference', $payoutAccount?->provider_destination_reference ?? '') }}"
                            placeholder="Optional external payout destination reference"
                            style="width:100%;padding:10px;border:1px solid var(--theme-border, #E6E1EF);border-radius:8px;">
                    </div>
                </div>

                <div style="margin-top:12px;">
                    <label for="payout_notes" style="display:block;margin-bottom:6px;font-weight:800;color:#0F172A;">Operations notes</label>
                    <textarea id="payout_notes" name="notes" rows="3"
                        style="width:100%;padding:10px;border:1px solid var(--theme-border, #E6E1EF);border-radius:8px;">{{ old('notes', data_get($payoutAccount, 'meta.notes', '')) }}</textarea>
                </div>

                <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-top:12px;">
                    <div style="font-size:13px;font-weight:700;color:{{
                        $payoutAccount?->isApproved() ? '#166534' : ($payoutAccount?->isRejected() ? '#B91C1C' : '#92400E')
                    }};">
                        Status: {{ $payoutAccount?->reviewStatusLabel() ?? 'Missing setup' }}
                    </div>
                    <button type="submit" id="savePayoutAccountButton"
                        style="padding:10px 16px;border:none;border-radius:6px;background:var(--theme-primary, #240E35);color:#fff;cursor:pointer;font-weight:600;">
                        Save Payout Account
                    </button>
                </div>

                @if($payoutAccount?->review_notes)
                    <div style="margin-top:10px;padding:12px;border-radius:10px;background:#F8FAFC;color:#334155;font-size:13px;font-weight:600;line-height:1.6;">
                        Review notes: {{ $payoutAccount->review_notes }}
                    </div>
                @endif
            </form>
        </div>
    @endif

    @if($user->tenant)
        <div class="card" style="margin-top: 18px;">
            <h3>Company Theme</h3>
            <form action="{{ route('profile.theme.update') }}" method="POST">
                @csrf
                @method('PUT')
                @php
                    $currentTheme = [
                        'primary' => old('theme_primary_color', $user->tenant->theme_primary_color ?? '#240E35'),
                        'accent' => old('theme_accent_color', $user->tenant->theme_accent_color ?? '#6B4A7A'),
                        'sidebar_bg' => old('theme_sidebar_bg', $user->tenant->theme_sidebar_bg ?? '#240E35'),
                        'sidebar_text' => old('theme_sidebar_text', $user->tenant->theme_sidebar_text ?? '#F8F4FB'),
                    ];

                    $recommendedThemes = [
                        [
                            'name' => 'Deep Purple (Brand)',
                            'primary' => '#240E35',
                            'accent' => '#6B4A7A',
                            'sidebar_bg' => '#240E35',
                            'sidebar_text' => '#F8F4FB',
                        ],
                        [
                            'name' => 'Blue + Orange (Complementary)',
                            'primary' => '#240E35',
                            'accent' => '#EA580C',
                            'sidebar_bg' => '#FFFFFF',
                            'sidebar_text' => '#1E3A8A',
                        ],
                        [
                            'name' => 'Yellow + Purple (Complementary)',
                            'primary' => '#EAB308',
                            'accent' => '#7E22CE',
                            'sidebar_bg' => '#FFFBEB',
                            'sidebar_text' => '#581C87',
                        ],
                        [
                            'name' => 'Red + Green (Complementary)',
                            'primary' => '#DC2626',
                            'accent' => '#16A34A',
                            'sidebar_bg' => '#FEF2F2',
                            'sidebar_text' => '#7F1D1D',
                        ],
                        [
                            'name' => 'Violet + Lime (Complementary)',
                            'primary' => '#7C3AED',
                            'accent' => '#65A30D',
                            'sidebar_bg' => '#F5F3FF',
                            'sidebar_text' => '#4C1D95',
                        ],
                        [
                            'name' => 'Teal + Coral (Near Complementary)',
                            'primary' => '#0F766E',
                            'accent' => '#F97316',
                            'sidebar_bg' => '#F0FDFA',
                            'sidebar_text' => '#134E4A',
                        ],
                        [
                            'name' => 'Navy + Amber (Complementary)',
                            'primary' => '#1E3A8A',
                            'accent' => '#F59E0B',
                            'sidebar_bg' => '#240E35',
                            'sidebar_text' => '#1E3A8A',
                        ],
                    ];
                @endphp

                <p style="margin-top: 10px; color:var(--theme-muted, #6B7280); font-size: 13px; font-weight: 600; line-height: 1.35;">
                    Customize your company's colors. These settings apply to <strong>all users in your company</strong> automatically.
                    Team members cannot change the theme.
                </p>

                <div style="margin-top: 14px;">
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
                        <h4 style="margin:0;color:var(--theme-body-text, #111827);">Recommended themes</h4>
                        <button type="button" id="themeResetDefault"
                            style="padding:8px 12px;border:1px solid var(--theme-border, #E6E1EF);border-radius:6px;background:var(--theme-surface-softer, #F7F7FB);color:var(--theme-muted, #6B7280);cursor:pointer;font-weight:600;">
                            Reset to default
                        </button>
                    </div>

                    <div class="app-grid app-grid--3" style="gap:10px;margin-top:10px;">
                        @foreach($recommendedThemes as $preset)
                            <button type="button" class="theme-preset-card"
                                data-primary="{{ $preset['primary'] }}"
                                data-accent="{{ $preset['accent'] }}"
                                data-sidebar-bg="{{ $preset['sidebar_bg'] }}"
                                data-sidebar-text="{{ $preset['sidebar_text'] }}"
                                style="text-align:left;padding:12px;border:1px solid var(--theme-border, #E6E1EF);border-radius:10px;background:#fff;cursor:pointer;">
                                <div style="font-weight:800;color:#0F172A;margin-bottom:8px;font-size:13px;">
                                    {{ $preset['name'] }}
                                </div>
                                <div style="display:flex;gap:6px;align-items:center;">
                                    <span title="Primary" style="width:18px;height:18px;border-radius:6px;background:{{ $preset['primary'] }};border:1px solid var(--theme-border, #E6E1EF);"></span>
                                    <span title="Accent" style="width:18px;height:18px;border-radius:6px;background:{{ $preset['accent'] }};border:1px solid var(--theme-border, #E6E1EF);"></span>
                                    <span title="Sidebar BG" style="width:18px;height:18px;border-radius:6px;background:{{ $preset['sidebar_bg'] }};border:1px solid var(--theme-border, #E6E1EF);"></span>
                                    <span title="Sidebar Text" style="width:18px;height:18px;border-radius:6px;background:{{ $preset['sidebar_text'] }};border:1px solid var(--theme-border, #E6E1EF);"></span>
                                </div>
                                <div style="margin-top:8px;color:var(--theme-muted, #6B7280);font-size:12px;font-weight:600;">
                                    Click to apply
                                </div>
                            </button>
                        @endforeach
                    </div>
                </div>

                <hr style="border:0;border-top:1px solid var(--theme-border, #E6E1EF);margin:16px 0;">

                <h4 style="margin:0 0 8px;color:var(--theme-body-text, #111827);">Custom colors</h4>
                <div class="app-form-grid app-form-grid--2" style="gap:12px;">
                    <div class="theme-field">
                        <label style="display:block;margin-bottom:6px;font-weight:800;color:#0F172A;">Primary color</label>
                        <div style="display:flex;gap:10px;align-items:center;">
                            <input type="color" id="themePrimaryPicker" name="theme_primary_color" value="{{ $currentTheme['primary'] }}"
                                   style="width:52px;height:38px;padding:0;border:1px solid var(--theme-border, #E6E1EF);border-radius:10px;cursor:pointer;">
                            <input type="text" id="themePrimaryHex" value="{{ $currentTheme['primary'] }}" inputmode="text" maxlength="7"
                                   style="flex:1;padding:10px;border:1px solid var(--theme-border, #E6E1EF);border-radius:8px;font-weight:700;color:#0F172A;"
                                   placeholder="#RRGGBB">
                        </div>
                        <div style="margin-top:6px;color:var(--theme-muted, #6B7280);font-size:12px;font-weight:600;">Used for buttons and highlights.</div>
                    </div>

                    <div class="theme-field">
                        <label style="display:block;margin-bottom:6px;font-weight:800;color:#0F172A;">Accent color</label>
                        <div style="display:flex;gap:10px;align-items:center;">
                            <input type="color" id="themeAccentPicker" name="theme_accent_color" value="{{ $currentTheme['accent'] }}"
                                   style="width:52px;height:38px;padding:0;border:1px solid var(--theme-border, #E6E1EF);border-radius:10px;cursor:pointer;">
                            <input type="text" id="themeAccentHex" value="{{ $currentTheme['accent'] }}" inputmode="text" maxlength="7"
                                   style="flex:1;padding:10px;border:1px solid var(--theme-border, #E6E1EF);border-radius:8px;font-weight:700;color:#0F172A;"
                                   placeholder="#RRGGBB">
                        </div>
                        <div style="margin-top:6px;color:var(--theme-muted, #6B7280);font-size:12px;font-weight:600;">Used for secondary actions and accents.</div>
                    </div>

                    <div class="theme-field">
                        <label style="display:block;margin-bottom:6px;font-weight:800;color:#0F172A;">Sidebar background</label>
                        <div style="display:flex;gap:10px;align-items:center;">
                            <input type="color" id="themeSidebarBgPicker" name="theme_sidebar_bg" value="{{ $currentTheme['sidebar_bg'] }}"
                                   style="width:52px;height:38px;padding:0;border:1px solid var(--theme-border, #E6E1EF);border-radius:10px;cursor:pointer;">
                            <input type="text" id="themeSidebarBgHex" value="{{ $currentTheme['sidebar_bg'] }}" inputmode="text" maxlength="7"
                                   style="flex:1;padding:10px;border:1px solid var(--theme-border, #E6E1EF);border-radius:8px;font-weight:700;color:#0F172A;"
                                   placeholder="#RRGGBB">
                        </div>
                        <div style="margin-top:6px;color:var(--theme-muted, #6B7280);font-size:12px;font-weight:600;">Sidebar panel background.</div>
                    </div>

                    <div class="theme-field">
                        <label style="display:block;margin-bottom:6px;font-weight:800;color:#0F172A;">Sidebar text</label>
                        <div style="display:flex;gap:10px;align-items:center;">
                            <input type="color" id="themeSidebarTextPicker" name="theme_sidebar_text" value="{{ $currentTheme['sidebar_text'] }}"
                                   style="width:52px;height:38px;padding:0;border:1px solid var(--theme-border, #E6E1EF);border-radius:10px;cursor:pointer;">
                            <input type="text" id="themeSidebarTextHex" value="{{ $currentTheme['sidebar_text'] }}" inputmode="text" maxlength="7"
                                   style="flex:1;padding:10px;border:1px solid var(--theme-border, #E6E1EF);border-radius:8px;font-weight:700;color:#0F172A;"
                                   placeholder="#RRGGBB">
                        </div>
                        <div style="margin-top:6px;color:var(--theme-muted, #6B7280);font-size:12px;font-weight:600;">Sidebar menu text.</div>
                    </div>
                </div>

                <button type="submit"
                    style="margin-top:14px;padding:10px 16px;border:none;border-radius:6px;background:var(--theme-primary, #240E35);color:#fff;cursor:pointer;font-weight:600;">
                    Save Theme
                </button>
            </form>
        </div>
    @endif

    @if($user->tenant && $user->hasRole('account-owner'))
        <div id="payoutPreviewModal" class="modal-overlay" style="display: none;">
            <div class="modal-box payout-preview-modal-box">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:16px;margin-bottom:16px;">
                    <div>
                        <h3 style="margin:0;">Payout Account Preview</h3>
                        <p style="margin:8px 0 0;color:var(--theme-muted, #6B7280);font-size:13px;font-weight:600;line-height:1.5;">
                            Review the masked payout cards saved for this account.
                        </p>
                    </div>
                    <button type="button" id="closePayoutPreviewModal" class="modal-close-btn">&times;</button>
                </div>

                <div class="payout-preview-tabs">
                    <button type="button" class="payout-preview-tab is-active" data-preview-target="payoutPreviewGcash">
                        GCash
                    </button>
                    <button type="button" class="payout-preview-tab" data-preview-target="payoutPreviewCard">
                        Card
                    </button>
                </div>

                <div id="payoutPreviewGcash" class="payout-preview-panel">
                    @if($hasGcashPreview)
                        <div class="payout-virtual-card payout-virtual-card--gcash">
                            <div class="payout-virtual-card__topline">
                                <span class="payout-virtual-card__chip"></span>
                                <span class="payout-virtual-card__brand">GCash</span>
                            </div>
                            <div class="payout-virtual-card__title">Payout destination</div>
                            <div class="payout-virtual-card__number">{{ $gcashPreview['identifier'] ?: $emptyDash }}</div>
                            <div class="payout-virtual-card__footer">
                                <div>
                                    <span class="payout-virtual-card__meta-label">Account name</span>
                                    <strong>{{ $gcashPreview['account_name'] ?: $emptyDash }}</strong>
                                </div>
                                <div style="text-align:right;">
                                    <span class="payout-virtual-card__meta-label">Status</span>
                                    <strong>{{ $payoutAccount?->reviewStatusLabel() ?? 'Pending' }}</strong>
                                </div>
                            </div>
                            @if($gcashPreview['reference'] !== '')
                                <div class="payout-virtual-card__reference">Ref: {{ $gcashPreview['reference'] }}</div>
                            @endif
                        </div>
                    @else
                        <div class="payout-preview-empty">
                            No saved GCash payout card to display.
                        </div>
                    @endif
                </div>

                <div id="payoutPreviewCard" class="payout-preview-panel" hidden>
                    @if($hasCardPreview)
                        <div class="payout-virtual-card payout-virtual-card--card">
                            <div class="payout-virtual-card__topline">
                                <span class="payout-virtual-card__chip"></span>
                                <span class="payout-virtual-card__brand">Card</span>
                            </div>
                            <div class="payout-virtual-card__title">Payout destination</div>
                            <div class="payout-virtual-card__number">{{ $cardPreview['identifier'] ?: $emptyDash }}</div>
                            <div class="payout-virtual-card__footer">
                                <div>
                                    <span class="payout-virtual-card__meta-label">Account name</span>
                                    <strong>{{ $cardPreview['account_name'] ?: $emptyDash }}</strong>
                                </div>
                                <div style="text-align:right;">
                                    <span class="payout-virtual-card__meta-label">Status</span>
                                    <strong>{{ $payoutAccount?->reviewStatusLabel() ?? 'Pending' }}</strong>
                                </div>
                            </div>
                            @if($cardPreview['reference'] !== '')
                                <div class="payout-virtual-card__reference">Ref: {{ $cardPreview['reference'] }}</div>
                            @endif
                        </div>
                    @else
                        <div class="payout-preview-empty">
                            No saved card payout card to display.
                        </div>
                    @endif
                </div>
            </div>
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
                            style="width: 100%; padding: 10px; padding-right: 40px; border: 1px solid var(--theme-border, #E6E1EF); border-radius: 6px;">
                        <i class="fas fa-eye toggle-password" id="toggleOldPassword"
                            style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; color: var(--theme-muted, #6B7280);"></i>
                    </div>
                </div>
                <div style="margin-bottom: 16px;">
                    <label for="new_password" style="display: block; margin-bottom: 6px; font-weight: 600;">New Password</label>
                    <div style="position: relative;">
                        <input type="password" id="new_password" name="new_password" required
                            style="width: 100%; padding: 10px; padding-right: 40px; border: 1px solid var(--theme-border, #E6E1EF); border-radius: 6px;">
                        <i class="fas fa-eye toggle-password" id="toggleNewPassword"
                            style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; color: var(--theme-muted, #6B7280);"></i>
                    </div>
                </div>
                <div style="margin-bottom: 16px;">
                    <label for="new_password_confirmation" style="display: block; margin-bottom: 6px; font-weight: 600;">Confirm New Password</label>
                    <div style="position: relative;">
                        <input type="password" id="new_password_confirmation" name="new_password_confirmation" required
                            style="width: 100%; padding: 10px; padding-right: 40px; border: 1px solid var(--theme-border, #E6E1EF); border-radius: 6px;">
                        <i class="fas fa-eye toggle-password" id="toggleNewPasswordConfirmation"
                            style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; color: var(--theme-muted, #6B7280);"></i>
                    </div>
                </div>
                <p style="margin-bottom: 16px; color: var(--theme-muted, #6B7280); font-size: 12px; font-weight: 600;">
                    12-64 chars, uppercase, lowercase, number, and special character.
                </p>
                <div style="display: flex; gap: 8px;">
                    <button type="submit" style="padding: 8px 16px; background-color: var(--theme-primary, #240E35); color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">
                        Confirm Password Change
                    </button>
                    <button type="button" id="cancelPasswordModal" style="padding: 8px 16px; background-color: var(--theme-border, #E6E1EF); color: var(--theme-muted, #6B7280); border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

        <link rel="stylesheet" href="{{ asset('css/extracted/profile-show-style1.css') }}">
@endsection

@section('scripts')
    <script>
        var payoutPreviewModal = document.getElementById('payoutPreviewModal');
        var openPayoutPreviewModal = document.getElementById('openPayoutPreviewModal');
        var closePayoutPreviewModal = document.getElementById('closePayoutPreviewModal');
        var payoutPreviewTabs = Array.from(document.querySelectorAll('.payout-preview-tab'));
        var payoutPreviewPanels = Array.from(document.querySelectorAll('.payout-preview-panel'));
        var payoutAccountForm = document.getElementById('payoutAccountForm');
        var savePayoutAccountButton = document.getElementById('savePayoutAccountButton');

        function setActivePayoutPreviewTab(targetId) {
            payoutPreviewTabs.forEach(function (tab) {
                var isActive = tab.getAttribute('data-preview-target') === targetId;
                tab.classList.toggle('is-active', isActive);
                tab.setAttribute('aria-pressed', isActive ? 'true' : 'false');
            });

            payoutPreviewPanels.forEach(function (panel) {
                panel.hidden = panel.id !== targetId;
            });
        }

        function closePayoutPreviewModalFunc() {
            if (payoutPreviewModal) {
                payoutPreviewModal.style.display = 'none';
            }
        }

        payoutPreviewTabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                setActivePayoutPreviewTab(tab.getAttribute('data-preview-target'));
            });
        });

        if (openPayoutPreviewModal && payoutPreviewModal) {
            openPayoutPreviewModal.addEventListener('click', function () {
                payoutPreviewModal.style.display = 'flex';
                setActivePayoutPreviewTab(openPayoutPreviewModal.getAttribute('data-default-preview-target') || 'payoutPreviewGcash');
            });
        }

        if (closePayoutPreviewModal) {
            closePayoutPreviewModal.addEventListener('click', closePayoutPreviewModalFunc);
        }

        if (payoutPreviewModal) {
            payoutPreviewModal.addEventListener('click', function (e) {
                if (e.target === payoutPreviewModal) {
                    closePayoutPreviewModalFunc();
                }
            });
        }

        if (payoutAccountForm && savePayoutAccountButton) {
            payoutAccountForm.addEventListener('submit', function () {
                savePayoutAccountButton.disabled = true;
                savePayoutAccountButton.textContent = 'Saving...';
            });
        }

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
                    primary: '#240E35',
                    accent: '#6B4A7A',
                    sidebarBg: '#240E35',
                    sidebarText: '#F8F4FB',
                });
            });
        }
    </script>
@endsection
