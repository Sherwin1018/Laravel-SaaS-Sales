<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Super Admin Dashboard')</title>
    <link rel="stylesheet" href="{{ asset('css/admin-dashboard.css') }}">
    @php
        $authUser = auth()->user();
        $tenant = $authUser?->tenant;
        $isPayoutAdmin = $authUser?->hasRole('payout-admin');
        $themePrimary = $isPayoutAdmin ? '#0F172A' : ($tenant->theme_primary_color ?? '#240E35');
        $themeAccent = $isPayoutAdmin ? '#0EA5E9' : ($tenant->theme_accent_color ?? '#6B4A7A');
        $themeSidebarBg = $isPayoutAdmin ? '#020617' : ($tenant->theme_sidebar_bg ?? '#240E35');
        $themeSidebarText = $isPayoutAdmin ? '#E2E8F0' : ($tenant->theme_sidebar_text ?? '#F8F4FB');
        $themeSidebarIcon = $isPayoutAdmin ? '#7DD3FC' : '#E7D8F0';
        $themeBodyBg = $isPayoutAdmin ? '#F8FAFC' : '#F7F7FB';
        $themeBodyText = '#111827';
        $themePrimaryDark = $isPayoutAdmin ? '#1E293B' : '#2E1244';
        $themeSurface = '#FFFFFF';
        $themeSurfaceSoft = $isPayoutAdmin ? '#E0F2FE' : '#F3EEF7';
        $themeSurfaceSofter = $isPayoutAdmin ? '#F8FAFC' : '#F7F7FB';
        $themeBorder = $isPayoutAdmin ? '#DBEAFE' : '#E6E1EF';
        $themeAccentStrong = $isPayoutAdmin ? '#38BDF8' : '#9E7BB5';
        $themeMuted = $isPayoutAdmin ? '#475569' : '#6B7280';
    @endphp
    <style>
        :root {
            --theme-primary: {{ $themePrimary }};
            --theme-primary-dark: {{ $themePrimaryDark }};
            --theme-accent: {{ $themeAccent }};
            --theme-accent-strong: {{ $themeAccentStrong }};
            --theme-sidebar-bg: {{ $themeSidebarBg }};
            --theme-sidebar-text: {{ $themeSidebarText }};
            --theme-sidebar-icon: {{ $themeSidebarIcon }};
            --theme-body-bg: {{ $themeBodyBg }};
            --theme-body-text: {{ $themeBodyText }};
            --theme-surface: {{ $themeSurface }};
            --theme-surface-soft: {{ $themeSurfaceSoft }};
            --theme-surface-softer: {{ $themeSurfaceSofter }};
            --theme-border: {{ $themeBorder }};
            --theme-muted: {{ $themeMuted }};
            --ui-btn-font-size: 14px;
            --ui-btn-px: 16px;
            --ui-btn-py: 10px;
            --ui-btn-radius: 8px;
            --ui-btn-min-height: 40px;
        }
        body.builder-full-width .main-content {
            margin-left: 0;
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
        }

        /* Consistent button sizing across app pages (colors remain unchanged). */
        .main-content a.btn-create,
        .main-content button.btn-create,
        .main-content button[type="submit"]:not(.ui-show-hide-toggle):not(.modal-close-btn):not(.status-toast-close):not(.toggle-btn):not(.dots-btn):not(.toggle-eye):not(.landing-video-close):not(.notification-dropdown__action):not([style*="background:none"]):not([style*="background: none"]):not([style*="padding:0"]):not([style*="padding: 0"]),
        .main-content button[type="button"]:not(.ui-show-hide-toggle):not(.modal-close-btn):not(.status-toast-close):not(.toggle-btn):not(.dots-btn):not(.toggle-eye):not(.landing-video-close):not(.notification-bell-btn):not([style*="background:none"]):not([style*="background: none"]):not([style*="padding:0"]):not([style*="padding: 0"]),
        .main-content input[type="submit"] {
            min-height: var(--ui-btn-min-height) !important;
            padding: var(--ui-btn-py) var(--ui-btn-px) !important;
            border-radius: var(--ui-btn-radius) !important;
            font-size: var(--ui-btn-font-size) !important;
            font-weight: 600 !important;
            line-height: 1.2 !important;
        }
        .login-splash {
            position: fixed;
            inset: 0;
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(9, 6, 16, 0.9);
            backdrop-filter: blur(3px);
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: opacity 0.2s ease;
        }
        .login-splash.is-visible {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }
        .login-splash__panel {
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 14px;
            text-align: center;
            padding: 26px 24px;
            border-radius: 18px;
            border: 1px solid rgba(214, 186, 242, 0.45);
            background: linear-gradient(160deg, rgba(96, 68, 131, 0.8) 0%, rgba(64, 41, 98, 0.74) 100%);
            backdrop-filter: blur(10px) saturate(120%);
            box-shadow: 0 18px 45px rgba(0, 0, 0, 0.4), 0 0 0 1px rgba(151, 118, 195, 0.28) inset;
        }
        .login-splash__panel::before {
            content: "";
            position: absolute;
            inset: 0;
            pointer-events: none;
            background:
                radial-gradient(ellipse at 50% 34%, rgba(255, 206, 178, 0.18) 0%, rgba(255, 206, 178, 0) 58%),
                radial-gradient(ellipse at 50% 72%, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0) 62%);
        }
        .login-splash__icon-stack {
            position: relative;
            z-index: 1;
            width: 104px;
            height: 104px;
            border-radius: 999px;
            display: grid;
            place-items: center;
            background: radial-gradient(circle at 32% 28%, rgba(255,255,255,0.22) 0%, rgba(255,255,255,0.08) 38%, rgba(255,255,255,0.02) 100%);
            border: 1px solid rgba(255,255,255,0.24);
            box-shadow: 0 14px 30px rgba(0,0,0,0.35), inset 0 0 0 1px rgba(255,255,255,0.12);
            animation: splashIconPulse 900ms ease-in-out infinite;
            transform-origin: center;
        }
        .login-splash__role-icon {
            font-size: 40px;
            color: #F8F5FF;
            line-height: 1;
            filter: drop-shadow(0 8px 18px rgba(0,0,0,0.45));
        }
        .login-splash__slow-icon {
            position: absolute;
            right: -4px;
            bottom: -4px;
            width: 34px;
            height: 34px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #f59e0b;
            color: #1f2937;
            border: 2px solid rgba(255,255,255,0.9);
            box-shadow: 0 6px 16px rgba(0,0,0,0.3);
            opacity: 0;
            transform: scale(0.72);
            transition: opacity .2s ease, transform .2s ease;
        }
        .login-splash.is-slow .login-splash__slow-icon {
            opacity: 1;
            transform: scale(1);
            animation: splashWarnPulse 880ms ease-in-out infinite;
        }
        .login-splash__text {
            position: relative;
            z-index: 1;
            margin: 0;
            color: #F8F5FF;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            animation: splashTextBlink 900ms ease-in-out infinite;
        }
        .login-splash__role-label {
            position: relative;
            z-index: 1;
            margin: 0;
            color: #F8F5FF;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.03em;
            text-transform: none;
        }
        @keyframes splashIconPulse {
            0% {
                transform: scale(0.96);
                opacity: 0.72;
            }
            100% {
                transform: scale(1.03);
                opacity: 1;
            }
        }
        @keyframes splashTextBlink {
            0% {
                opacity: 0.5;
            }
            100% {
                opacity: 1;
            }
        }
        @keyframes splashWarnPulse {
            0% { box-shadow: 0 0 0 0 rgba(245,158,11,.55), 0 6px 16px rgba(0,0,0,.3); }
            100% { box-shadow: 0 0 0 10px rgba(245,158,11,0), 0 6px 16px rgba(0,0,0,.3); }
        }
    </style>
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @yield('styles')
</head>
<body class="@if(request()->routeIs('funnels.edit') || request()->routeIs('admin.funnel-templates.edit')) builder-full-width @endif" data-auth-role="{{ match (true) { auth()->user()->hasRole('super-admin') => 'super_admin', auth()->user()->hasRole('payout-admin') => 'payout_admin', auth()->user()->hasRole('account-owner') => 'account_owner', auth()->user()->hasRole('marketing-manager') => 'marketing_manager', auth()->user()->hasRole('sales-agent') => 'sales_agent', auth()->user()->hasRole('finance') => 'finance', default => 'customer' } }}" data-auth-email="{{ strtolower((string) auth()->user()->email) }}">
    @php
        $primaryRole = auth()->user()->roles->first();
        $roleLabel = $primaryRole ? $primaryRole->name : ucwords(str_replace('-', ' ', auth()->user()->role ?? 'User'));
        $userNameSource = trim((string) auth()->user()->name);
        $userInitials = collect(preg_split('/\s+/', $userNameSource))
            ->filter()
            ->take(2)
            ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
            ->implode('');
        $userInitials = $userInitials !== '' ? $userInitials : 'U';
        $userHue = abs(crc32($userNameSource ?: 'user')) % 360;
        $userAvatarBg = "hsl({$userHue}, 65%, 45%)";
    @endphp

    <!-- Sidebar (hidden in funnel builder; use Exit Builder to leave) -->
    @unless(request()->routeIs('funnels.edit') || request()->routeIs('admin.funnel-templates.edit'))
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="logo-container">
                <img src="{{ asset('images/logo.png') }}" 
                    alt="Sales & Marketing Funnel System" 
                    class="sidebar-logo">
            </div>

            <button id="sidebarToggle" class="toggle-btn">
                <i class="fas fa-bars"></i>
            </button>
        </div>

        
        <div class="sidebar-menu">
            {{-- SUPER ADMIN LINKS --}}
            @if(auth()->user()->hasRole('super-admin'))
                <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt"></i> <span>Dashboard</span>
                </a>
                <a href="{{ route('admin.tenants.index') }}" class="{{ request()->routeIs('admin.tenants.*') ? 'active' : '' }}">
                    <i class="fas fa-building"></i> <span>Tenants</span>
                </a>
                <a href="{{ route('admin.plans.index') }}" class="{{ request()->routeIs('admin.plans.*') ? 'active' : '' }}">
                    <i class="fas fa-tags"></i> <span>Plans</span>
                </a>
                <a href="{{ route('admin.coupons.index') }}" class="{{ request()->routeIs('admin.coupons.*') ? 'active' : '' }}">
                    <i class="fas fa-ticket-alt"></i> <span>Coupons</span>
                </a>
                <a href="{{ route('admin.automation.index') }}" class="{{ request()->routeIs('admin.automation.*') ? 'active' : '' }}">
                    <i class="fas fa-clipboard-list"></i> <span>Automation</span>
                </a>
                <a href="{{ route('admin.receipts.index') }}" class="{{ request()->routeIs('admin.receipts.*') ? 'active' : '' }}">
                    <i class="fas fa-file-invoice-dollar"></i> <span>Receipts</span>
                </a>
                <a href="{{ route('admin.funnel-templates.index') }}" class="{{ request()->routeIs('admin.funnel-templates.*') ? 'active' : '' }}">
                    <i class="fas fa-layer-group"></i> <span>Templates</span>
                </a>
            @endif

            @if(auth()->user()->hasRole('payout-admin'))
                <a href="{{ route('platform.payouts.index') }}" class="{{ request()->routeIs('platform.payouts.*') ? 'active' : '' }}">
                    <i class="fas fa-scale-balanced"></i> <span>Payout Reviews</span>
                </a>
            @endif

            {{-- TENANT LINKS (Account Owner, Marketing, Sales, Finance, Customer) --}}

            {{-- Tenant Dashboards --}}
            @if(auth()->user()->hasRole('account-owner'))
                <a href="{{ route('dashboard.owner') }}" class="{{ request()->routeIs('dashboard.owner') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt"></i> <span>Dashboard</span>
                </a>
            @endif

            @if(auth()->user()->hasRole('marketing-manager'))
                <a href="{{ route('dashboard.marketing') }}" class="{{ request()->routeIs('dashboard.marketing') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt"></i> <span>Dashboard</span>
                </a>
            @endif

            @if(auth()->user()->hasRole('sales-agent'))
                <a href="{{ route('dashboard.sales') }}" class="{{ request()->routeIs('dashboard.sales') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt"></i> <span>Dashboard</span>
                </a>
            @endif

            @if(auth()->user()->hasRole('finance'))
                <a href="{{ route('dashboard.finance') }}" class="{{ request()->routeIs('dashboard.finance') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt"></i> <span>Dashboard</span>
                </a>
            @endif

            @if(auth()->user()->hasRole('customer'))
                <a href="{{ route('dashboard.customer') }}" class="{{ request()->routeIs('dashboard.customer') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt"></i> <span>Dashboard</span>
                </a>
            @endif
            
            {{-- Leads (Accessible by Owner, Marketing, Sales) --}}
            @if(auth()->user()->hasRole('account-owner') || auth()->user()->hasRole('marketing-manager') || auth()->user()->hasRole('sales-agent'))
                <a href="{{ route('leads.index') }}" class="{{ request()->routeIs('leads.*') ? 'active' : '' }}">
                    <i class="fas fa-user-tie"></i> <span>Leads</span>
                </a>
            @endif

            {{-- Users (Account Owner only) --}}
            @if(auth()->user()->hasRole('account-owner'))
                <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <i class="fas fa-users"></i> <span>Team</span>
                </a>
                <a href="{{ route('coupons.index') }}" class="{{ request()->routeIs('coupons.*') ? 'active' : '' }}">
                    <i class="fas fa-ticket-alt"></i> <span>Coupons</span>
                </a>
            @endif

            {{-- Funnels (Owner, Marketing) --}}
            @if(auth()->user()->hasRole('account-owner') || auth()->user()->hasRole('marketing-manager'))
                <a href="{{ route('funnels.index') }}" class="{{ request()->routeIs('funnels.*') ? 'active' : '' }}"><i class="fas fa-filter"></i> <span>Funnels</span></a>
            @endif

            {{-- Automation (Account Owner only) --}}
            @if(auth()->user()->hasRole('account-owner'))
                <a href="{{ route('automation.index') }}" class="{{ request()->routeIs('automation.*') ? 'active' : '' }}"><i class="fas fa-clipboard-list"></i> <span>Automation</span></a>
            @endif

            {{-- Billing (Owner, Finance) --}}
            @if(auth()->user()->hasRole('account-owner') || auth()->user()->hasRole('finance'))
                <a href="{{ route('payments.index') }}" class="{{ request()->routeIs('payments.*') ? 'active' : '' }}">
                    <i class="fas fa-file-invoice-dollar"></i> <span>Billing</span>
                </a>
            @endif

            @if(auth()->user()->hasRole('account-owner') && optional(auth()->user()->tenant)->status === 'trial')
                <a href="{{ route('trial.billing.show') }}" class="{{ request()->routeIs('trial.billing.*') ? 'active' : '' }}">
                    <i class="fas fa-bolt"></i> <span>Upgrade Plan</span>
                </a>
            @endif
            
            {{-- Analytics (Owner, Marketing, Finance) --}}
            @if(auth()->user()->hasRole('account-owner'))
                <a href="{{ route('reports.owner') }}" class="{{ request()->routeIs('reports.*') ? 'active' : '' }}">
                    <i class="fas fa-chart-line"></i> <span>Reports</span>
                </a>
            @endif

            <a href="{{ route('notifications.index') }}" class="{{ request()->routeIs('notifications.*') ? 'active' : '' }}">
                <i class="fas fa-bell"></i> <span>Notifications</span>
            </a>
        </div>

        <div class="account-info-wrapper">
            <div class="account-info">
                <div class="account-avatar" style="background: {{ $userAvatarBg }};">
                    @if(auth()->user()->profile_photo_path)
                        <img src="{{ asset('storage/' . auth()->user()->profile_photo_path) }}" alt="Profile Avatar" class="account-avatar-img">
                    @else
                        <span>{{ $userInitials }}</span>
                    @endif
                </div>

                <div class="account-details">
                    <strong>{{ auth()->user()->name }}</strong>
                    <small>{{ auth()->user()->email }}</small>
                    <small class="account-role">{{ $roleLabel }}</small>
                </div>

                <div class="account-menu">
                    <button class="dots-btn" onclick="toggleAccountMenu(event)">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>

                    <div id="accountDropdown" class="account-dropdown">
                        <a href="{{ route('profile.show') }}" class="dropdown-link">
                            <i class="fas fa-user-cog" aria-hidden="true"></i>
                            <span>Manage Profile</span>
                        </a>

                        <form method="POST" action="{{ route('logout') }}" data-logout-form>
                            @csrf
                            <button type="submit" class="dropdown-btn">
                                <i class="fas fa-sign-out-alt" aria-hidden="true"></i>
                                <span>Logout</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <button type="button" id="sidebarMobileOpen" class="sidebar-mobile-open-btn" aria-label="Open navigation" aria-controls="sidebar" aria-expanded="false">
        <i class="fas fa-bars" aria-hidden="true"></i>
    </button>
    <div id="sidebarBackdrop" class="sidebar-backdrop" aria-hidden="true"></div>
    @endunless

    <!-- Main Content -->
    <div class="main-content">
        <div class="global-utility-bar">
            <div class="notification-shell" data-notification-feed-url="{{ route('notifications.feed') }}" data-notification-index-url="{{ route('notifications.index') }}" data-initial-unread="{{ (int) ($layoutNotificationUnreadCount ?? 0) }}" data-initial-latest-id="{{ (int) ($layoutLatestNotificationId ?? 0) }}">
                <button type="button" id="notificationBellButton" class="notification-bell-btn {{ ($layoutNotificationUnreadCount ?? 0) > 0 ? 'has-unread' : '' }}" aria-label="Open notifications">
                    <i class="fas fa-bell"></i>
                    <span class="notification-bell-badge" {{ ($layoutNotificationUnreadCount ?? 0) > 0 ? '' : 'hidden' }}>{{ min((int) ($layoutNotificationUnreadCount ?? 0), 99) }}</span>
                </button>

                <div id="notificationDropdown" class="notification-dropdown">
                    <div class="notification-dropdown__header">
                        <div>
                            <strong>Notifications</strong>
                            <p><span id="notificationUnreadText">{{ (int) ($layoutNotificationUnreadCount ?? 0) }}</span> unread</p>
                        </div>
                        <button type="button" id="notificationBrowserAlertsButton" class="notification-dropdown__action" hidden>Enable alerts</button>
                        <form method="POST" action="{{ route('notifications.mark-all-read') }}">
                            @csrf
                            <button type="submit" class="notification-dropdown__action">Mark all read</button>
                        </form>
                    </div>

                    <div id="notificationDropdownList" class="notification-dropdown__list">
                        @forelse(($layoutRecentNotifications ?? collect()) as $notification)
                            <a href="{{ $notification->action_url ?: route('notifications.index') }}"
                                class="notification-dropdown__item {{ $notification->read_at ? '' : 'is-unread' }}"
                                data-notification-id="{{ $notification->id }}"
                                data-notification-read-url="{{ route('notifications.read', $notification) }}">
                                <div class="notification-dropdown__item-top">
                                    <span class="notification-dropdown__title">{{ $notification->title }}</span>
                                    <span class="notification-dropdown__time">{{ optional($notification->occurred_at)->diffForHumans() }}</span>
                                </div>
                                <div class="notification-dropdown__message">{{ $notification->message }}</div>
                            </a>
                        @empty
                            <div class="notification-dropdown__empty">
                                No notifications yet.
                            </div>
                        @endforelse
                    </div>

                    <a href="{{ route('notifications.index') }}" class="notification-dropdown__footer">
                        View full notification log
                    </a>
                </div>
            </div>
        </div>

        @yield('content')
    </div>

    <div id="notificationToastStack" class="notification-toast-stack" aria-live="polite" aria-atomic="false"></div>

    <div id="loginSplash" class="login-splash" aria-hidden="true">
        <div class="login-splash__panel">
            <div class="login-splash__icon-stack" aria-hidden="true">
                <i id="loginSplashRoleIcon" class="fas fa-user-circle login-splash__role-icon"></i>
                <span class="login-splash__slow-icon" title="Slow connection detected">
                    <i class="fas fa-wifi"></i>
                </span>
            </div>
            <p id="loginSplashRoleLabel" class="login-splash__role-label">Customer</p>
            <p class="login-splash__text">Signing you out...</p>
        </div>
    </div>

    <script>

        function toggleAccountMenu(event) {
            event.stopPropagation(); // Prevent window click from firing
            const dropdown = document.getElementById("accountDropdown");
            const triggerBtn = event.currentTarget;

            if (!dropdown || !triggerBtn) return;

            const placeAccountDropdown = () => {
                const rect = triggerBtn.getBoundingClientRect();
                const dropdownWidth = 160;
                const gap = 10;
                const viewportWidth = window.innerWidth || document.documentElement.clientWidth;
                const viewportHeight = window.innerHeight || document.documentElement.clientHeight;
                const preferredLeft = rect.right + gap;
                const fitsRight = preferredLeft + dropdownWidth <= viewportWidth - 8;
                const left = fitsRight ? preferredLeft : Math.max(8, rect.left - dropdownWidth - gap);
                const top = Math.min(Math.max(8, rect.bottom - 12), Math.max(8, viewportHeight - dropdown.offsetHeight - 8));

                dropdown.style.position = "fixed";
                dropdown.style.left = `${left}px`;
                dropdown.style.top = `${top}px`;
                dropdown.style.right = "auto";
                dropdown.style.bottom = "auto";
                dropdown.style.zIndex = "2100";
            };

            if (dropdown.style.display === "block") {
                dropdown.style.display = "none";
            } else {
                dropdown.style.display = "block";
                placeAccountDropdown();
            }
        }

        // Close dropdown when clicking anywhere outside
        document.addEventListener("click", function(event) {
            const dropdown = document.getElementById("accountDropdown");
            const menu = document.querySelector(".account-menu");

            if (!dropdown || !menu) return;

            if (!menu.contains(event.target) && !dropdown.contains(event.target)) {
                dropdown.style.display = "none";
            }
        });

        // Sidebar Toggle
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('sidebarToggle');
        const sidebarBackdrop = document.getElementById('sidebarBackdrop');
        const sidebarMobileOpenButton = document.getElementById('sidebarMobileOpen');
        const notificationBellButton = document.getElementById('notificationBellButton');
        const notificationDropdown = document.getElementById('notificationDropdown');
        const notificationShell = document.querySelector('.notification-shell');
        const notificationDropdownList = document.getElementById('notificationDropdownList');
        const notificationUnreadText = document.getElementById('notificationUnreadText');
        const notificationToastStack = document.getElementById('notificationToastStack');
        const notificationBrowserAlertsButton = document.getElementById('notificationBrowserAlertsButton');

        (function () {
            const header = document.querySelector('.main-content .top-header');
            const utilityBar = document.querySelector('.global-utility-bar');

            if (!header || !utilityBar) {
                return;
            }

            const companyChip = header.querySelector('.company-chip');
            const landingVideoTrigger = header.querySelector('.landing-video-trigger');
            const existingActionGroup = header.querySelector('.top-header-inline-actions');
            const actionGroup = existingActionGroup || document.createElement('div');

            if (!existingActionGroup) {
                actionGroup.className = 'top-header-inline-actions';
            }

            if (companyChip) {
                const companyChipName = companyChip.querySelector('.company-chip-name');
                const companyTooltip = companyChipName ? companyChipName.textContent.trim() : '';

                if (companyTooltip) {
                    companyChip.setAttribute('data-company-tooltip', companyTooltip);
                    companyChip.setAttribute('aria-label', companyTooltip);
                    companyChip.setAttribute('tabindex', '0');
                }

                header.classList.add('top-header--has-company-actions');
                header.classList.remove('top-header--has-compact-actions');
                if (!actionGroup.parentNode) {
                    header.appendChild(actionGroup);
                }
                actionGroup.appendChild(companyChip);
                actionGroup.appendChild(utilityBar);
                return;
            }

            if (landingVideoTrigger) {
                header.classList.add('top-header--has-compact-actions');
                header.classList.remove('top-header--has-company-actions');
                if (!actionGroup.parentNode) {
                    header.appendChild(actionGroup);
                }
                actionGroup.appendChild(landingVideoTrigger);
                actionGroup.appendChild(utilityBar);
                return;
            }

            header.classList.add('top-header--has-compact-actions');
            header.classList.remove('top-header--has-company-actions');
            if (!actionGroup.parentNode) {
                header.appendChild(actionGroup);
            }
            actionGroup.appendChild(utilityBar);
        })();
        
        if (sidebar && toggleBtn) {
            const mobileSidebarMedia = window.matchMedia('(max-width: 768px)');
            const setMobileSidebarOpen = (isOpen) => {
                document.body.classList.toggle('sidebar-mobile-open', isOpen);

                if (sidebarMobileOpenButton) {
                    sidebarMobileOpenButton.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                }

                if (sidebarBackdrop) {
                    sidebarBackdrop.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
                }
            };

            const syncSidebarResponsiveState = () => {
                if (mobileSidebarMedia.matches) {
                    sidebar.classList.remove('collapsed');
                    setMobileSidebarOpen(false);
                    sidebar.dataset.mobileInit = '1';
                } else {
                    setMobileSidebarOpen(false);
                    sidebar.classList.remove('collapsed');
                    delete sidebar.dataset.mobileInit;
                }
            };

            syncSidebarResponsiveState();
            if (typeof mobileSidebarMedia.addEventListener === 'function') {
                mobileSidebarMedia.addEventListener('change', syncSidebarResponsiveState);
            } else if (typeof mobileSidebarMedia.addListener === 'function') {
                mobileSidebarMedia.addListener(syncSidebarResponsiveState);
            }

            toggleBtn.addEventListener('click', () => {
                if (mobileSidebarMedia.matches) {
                    const nextOpen = !document.body.classList.contains('sidebar-mobile-open');
                    setMobileSidebarOpen(nextOpen);
                    sidebar.dataset.mobileInit = '1';
                    return;
                }

                sidebar.classList.toggle('collapsed');
            });

            if (sidebarMobileOpenButton) {
                sidebarMobileOpenButton.addEventListener('click', () => {
                    if (mobileSidebarMedia.matches) {
                        setMobileSidebarOpen(true);
                    }
                });
            }

            if (sidebarBackdrop) {
                sidebarBackdrop.addEventListener('click', () => {
                    if (mobileSidebarMedia.matches) {
                        setMobileSidebarOpen(false);
                    }
                });
            }

            Array.from(sidebar.querySelectorAll('a') || []).forEach((link) => {
                link.addEventListener('click', () => {
                    if (mobileSidebarMedia.matches) {
                        setMobileSidebarOpen(false);
                    }
                });
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && mobileSidebarMedia.matches) {
                    setMobileSidebarOpen(false);
                }
            });
        }

        if (notificationBellButton && notificationDropdown) {
            notificationBellButton.addEventListener('click', function (event) {
                event.stopPropagation();
                notificationDropdown.classList.toggle('is-open');
            });

            document.addEventListener('click', function (event) {
                if (!notificationDropdown.contains(event.target) && !notificationBellButton.contains(event.target)) {
                    notificationDropdown.classList.remove('is-open');
                }
            });
        }

        (function () {
            if (!notificationShell || !notificationBellButton || !notificationDropdownList || !notificationUnreadText || !notificationToastStack) {
                return;
            }

            const feedUrl = notificationShell.getAttribute('data-notification-feed-url');
            const notificationsIndexUrl = notificationShell.getAttribute('data-notification-index-url');
            const initialUnread = parseInt(notificationShell.getAttribute('data-initial-unread') || '0', 10) || 0;
            const initialLatestId = parseInt(notificationShell.getAttribute('data-initial-latest-id') || '0', 10) || 0;
            const storageKey = 'notifications.lastSeen.' + (document.body.getAttribute('data-auth-email') || 'user');
            const alertsPreferenceKey = storageKey + '.alerts-enabled';
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const originalTitle = document.title;
            const supportsBrowserAlerts = typeof window.Notification !== 'undefined';
            const supportsAudioAlerts = typeof window.AudioContext !== 'undefined' || typeof window.webkitAudioContext !== 'undefined';
            let pollTimer = null;
            let isFetching = false;
            let audioContext = null;

            const readLastSeen = () => {
                try {
                    const stored = parseInt(localStorage.getItem(storageKey) || '0', 10);
                    return Number.isFinite(stored) ? stored : 0;
                } catch (_e) {
                    return 0;
                }
            };

            const writeLastSeen = (value) => {
                try {
                    localStorage.setItem(storageKey, String(value));
                } catch (_e) {}
            };

            const readAlertsEnabled = () => {
                try {
                    return localStorage.getItem(alertsPreferenceKey) === '1';
                } catch (_e) {
                    return false;
                }
            };

            const writeAlertsEnabled = (enabled) => {
                try {
                    localStorage.setItem(alertsPreferenceKey, enabled ? '1' : '0');
                } catch (_e) {}
            };

            if (!readLastSeen()) {
                writeLastSeen(initialLatestId);
            }

            const escapeHtml = (value) => String(value || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');

            const levelLabel = (value) => {
                const map = {
                    info: 'Info',
                    success: 'Success',
                    warning: 'Warning',
                    error: 'Urgent'
                };

                return map[value] || 'Notice';
            };

            const ensureAudioContext = () => {
                if (!supportsAudioAlerts) {
                    return null;
                }

                if (!audioContext) {
                    const AudioContextClass = window.AudioContext || window.webkitAudioContext;
                    audioContext = AudioContextClass ? new AudioContextClass() : null;
                }

                if (audioContext && audioContext.state === 'suspended' && typeof audioContext.resume === 'function') {
                    audioContext.resume().catch(function () {});
                }

                return audioContext;
            };

            const playAlertTone = () => {
                if (!readAlertsEnabled() || !supportsAudioAlerts) {
                    return;
                }

                const context = ensureAudioContext();
                if (!context) {
                    return;
                }

                const oscillator = context.createOscillator();
                const gainNode = context.createGain();
                oscillator.type = 'sine';
                oscillator.frequency.setValueAtTime(880, context.currentTime);
                gainNode.gain.setValueAtTime(0.0001, context.currentTime);
                gainNode.gain.exponentialRampToValueAtTime(0.03, context.currentTime + 0.02);
                gainNode.gain.exponentialRampToValueAtTime(0.0001, context.currentTime + 0.26);
                oscillator.connect(gainNode);
                gainNode.connect(context.destination);
                oscillator.start();
                oscillator.stop(context.currentTime + 0.28);
            };

            const updateDocumentTitle = (unreadCount) => {
                document.title = unreadCount > 0 ? '(' + unreadCount + ') ' + originalTitle : originalTitle;
            };

            const updateAlertsButton = () => {
                if (!notificationBrowserAlertsButton) {
                    return;
                }

                if (!supportsBrowserAlerts && !supportsAudioAlerts) {
                    notificationBrowserAlertsButton.hidden = true;
                    return;
                }

                notificationBrowserAlertsButton.hidden = false;

                if (supportsBrowserAlerts && Notification.permission === 'denied') {
                    notificationBrowserAlertsButton.disabled = false;
                    notificationBrowserAlertsButton.textContent = readAlertsEnabled() ? 'Disable sound alerts' : 'Enable sound alerts';
                    return;
                }

                notificationBrowserAlertsButton.disabled = false;
                notificationBrowserAlertsButton.textContent = readAlertsEnabled() ? 'Disable alerts' : 'Enable alerts';
            };

            const updateBellState = (unreadCount, shouldPulse = false) => {
                const badge = notificationBellButton.querySelector('.notification-bell-badge');
                if (badge) {
                    badge.textContent = String(Math.min(unreadCount, 99));
                    badge.hidden = unreadCount <= 0;
                }

                notificationBellButton.classList.toggle('has-unread', unreadCount > 0);
                if (shouldPulse) {
                    notificationBellButton.classList.remove('is-alerting');
                    void notificationBellButton.offsetWidth;
                    notificationBellButton.classList.add('is-alerting');
                    window.setTimeout(function () {
                        notificationBellButton.classList.remove('is-alerting');
                    }, 1200);
                }

                notificationUnreadText.textContent = String(unreadCount);
                updateDocumentTitle(unreadCount);
            };

            const renderDropdown = (notifications) => {
                if (!notifications.length) {
                    notificationDropdownList.innerHTML = '<div class="notification-dropdown__empty">No notifications yet.</div>';
                    return;
                }

                const markup = notifications.slice().reverse().map(function (notification) {
                    const unreadClass = notification.read_at ? '' : ' is-unread';
                    return '' +
                        '<a href="' + escapeHtml(notification.action_url || notificationsIndexUrl) + '" class="notification-dropdown__item' + unreadClass + '" data-notification-id="' + notification.id + '" data-notification-read-url="' + escapeHtml(notification.read_url || '') + '">' +
                            '<div class="notification-dropdown__item-top">' +
                                '<span class="notification-dropdown__title">' + escapeHtml(notification.title) + '</span>' +
                                '<span class="notification-dropdown__time">' + escapeHtml(notification.occurred_at_human || 'Just now') + '</span>' +
                            '</div>' +
                            '<div class="notification-dropdown__message">' + escapeHtml(notification.message) + '</div>' +
                        '</a>';
                }).join('');

                notificationDropdownList.innerHTML = markup;
            };

            const removeToast = (toast) => {
                if (!toast || !toast.parentNode) {
                    return;
                }

                toast.classList.add('is-leaving');
                window.setTimeout(function () {
                    if (toast.parentNode) {
                        toast.parentNode.removeChild(toast);
                    }
                }, 220);
            };

            const pushToast = (notification) => {
                const toast = document.createElement('div');
                toast.className = 'notification-toast notification-toast--' + (notification.level || 'info');
                toast.setAttribute('role', notification.level === 'error' ? 'alert' : 'status');
                toast.innerHTML = '' +
                    '<div class="notification-toast__eyebrow">' + escapeHtml(levelLabel(notification.level)) + '</div>' +
                    '<div class="notification-toast__title">' + escapeHtml(notification.title) + '</div>' +
                    '<div class="notification-toast__message">' + escapeHtml(notification.message) + '</div>' +
                    '<div class="notification-toast__actions">' +
                        '<a class="notification-toast__open" href="' + escapeHtml(notification.action_url || notificationsIndexUrl) + '">Open</a>' +
                        '<button type="button" class="notification-toast__dismiss" aria-label="Dismiss notification">Dismiss</button>' +
                    '</div>';

                const dismissButton = toast.querySelector('.notification-toast__dismiss');
                if (dismissButton) {
                    dismissButton.addEventListener('click', function () {
                        removeToast(toast);
                    });
                }

                notificationToastStack.prepend(toast);

                const timeoutMs = notification.level === 'error'
                    ? 0
                    : (notification.level === 'warning' ? 12000 : 7000);

                if (timeoutMs > 0) {
                    window.setTimeout(function () {
                        removeToast(toast);
                    }, timeoutMs);
                }
            };

            const pushBrowserNotification = (notification) => {
                if (!readAlertsEnabled()) {
                    return;
                }

                if (supportsBrowserAlerts && Notification.permission === 'granted' && (document.hidden || !document.hasFocus())) {
                    try {
                        const desktopNotification = new Notification(notification.title, {
                            body: notification.message,
                            tag: 'app-notification-' + notification.id,
                        });
                        desktopNotification.onclick = function () {
                            window.focus();
                            window.location.href = notification.action_url || notificationsIndexUrl;
                            desktopNotification.close();
                        };
                    } catch (_e) {}
                }

                playAlertTone();
            };

            const parseUnreadCount = () => parseInt(notificationUnreadText.textContent || '0', 10) || 0;

            const markNotificationAsRead = (notificationLink) => {
                if (!notificationLink || !notificationLink.classList.contains('is-unread')) {
                    return Promise.resolve();
                }

                const readUrl = notificationLink.getAttribute('data-notification-read-url');
                if (!readUrl || !csrfToken) {
                    return Promise.resolve();
                }

                if (notificationLink.dataset.readState === 'pending' || notificationLink.dataset.readState === 'done') {
                    return Promise.resolve();
                }

                notificationLink.dataset.readState = 'pending';

                return fetch(readUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin',
                    keepalive: true,
                    body: JSON.stringify({})
                })
                    .then(function (response) {
                        if (!response.ok) {
                            throw new Error('Failed to mark notification as read.');
                        }

                        return response.json();
                    })
                    .then(function (data) {
                        notificationLink.classList.remove('is-unread');
                        notificationLink.dataset.readState = 'done';
                        updateBellState(typeof data.unread_count === 'number' ? data.unread_count : Math.max(0, parseUnreadCount() - 1), false);
                    })
                    .catch(function () {
                        delete notificationLink.dataset.readState;
                    });
            };

            const syncFeed = (data, shouldToastNew) => {
                const unreadCount = parseInt(data.unread_count || 0, 10) || 0;
                const latestId = parseInt(data.latest_id || 0, 10) || 0;
                const notifications = Array.isArray(data.notifications) ? data.notifications : [];
                const lastSeenId = readLastSeen();
                const freshNotifications = notifications.filter(function (notification) {
                    return Number(notification.id) > lastSeenId;
                });

                renderDropdown(notifications);
                updateBellState(unreadCount, shouldToastNew && freshNotifications.length > 0);

                if (shouldToastNew && freshNotifications.length > 0) {
                    freshNotifications.forEach(function (notification) {
                        pushToast(notification);
                        pushBrowserNotification(notification);
                    });
                }

                if (latestId > lastSeenId) {
                    writeLastSeen(latestId);
                }
            };

            const fetchFeed = (shouldToastNew) => {
                if (isFetching || !feedUrl) {
                    return;
                }

                isFetching = true;
                fetch(feedUrl, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                })
                    .then(function (response) {
                        if (!response.ok) {
                            throw new Error('Notification feed request failed.');
                        }

                        return response.json();
                    })
                    .then(function (data) {
                        syncFeed(data, shouldToastNew);
                    })
                    .catch(function () {
                        // Keep notification polling silent if the app is temporarily unreachable.
                    })
                    .finally(function () {
                        isFetching = false;
                    });
            };

            notificationDropdownList.addEventListener('click', function (event) {
                const notificationLink = event.target.closest('.notification-dropdown__item');
                if (!notificationLink) {
                    return;
                }

                if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
                    return;
                }

                const href = notificationLink.getAttribute('href');
                if (!href) {
                    return;
                }

                event.preventDefault();
                markNotificationAsRead(notificationLink).finally(function () {
                    window.location.href = href;
                });
            });

            if (notificationBrowserAlertsButton) {
                notificationBrowserAlertsButton.addEventListener('click', function () {
                    if (readAlertsEnabled()) {
                        writeAlertsEnabled(false);
                        updateAlertsButton();
                        return;
                    }

                    ensureAudioContext();

                    if (supportsBrowserAlerts && Notification.permission === 'default') {
                        Notification.requestPermission().then(function (permission) {
                            if (permission === 'granted') {
                                writeAlertsEnabled(true);
                            } else if (permission === 'denied' && supportsAudioAlerts) {
                                writeAlertsEnabled(true);
                            }
                            updateAlertsButton();
                        }).catch(function () {
                            updateAlertsButton();
                        });

                        return;
                    }

                    if (!supportsBrowserAlerts || Notification.permission === 'granted' || (Notification.permission === 'denied' && supportsAudioAlerts)) {
                        writeAlertsEnabled(true);
                    }

                    updateAlertsButton();
                });
            }

            updateBellState(initialUnread, false);
            updateAlertsButton();
            pollTimer = window.setInterval(function () {
                fetchFeed(true);
            }, 15000);

            document.addEventListener('visibilitychange', function () {
                if (!document.hidden) {
                    fetchFeed(true);
                }
            });

            window.addEventListener('focus', function () {
                fetchFeed(true);
            });
        })();

        (function () {
            const logoutForms = Array.from(document.querySelectorAll('form[data-logout-form]') || []);
            const splash = document.getElementById('loginSplash');
            const splashRoleIcon = document.getElementById('loginSplashRoleIcon');
            const splashRoleLabel = document.getElementById('loginSplashRoleLabel');
            const splashDisplayMs = 900;
            const slowConnectionMs = 2200;
            let isSubmitting = false;
            let slowTimer = null;
            const roleIcons = {
                super_admin: 'fa-user-shield',
                payout_admin: 'fa-scale-balanced',
                account_owner: 'fa-building-user',
                marketing_manager: 'fa-chart-line',
                sales_agent: 'fa-handshake',
                finance: 'fa-file-invoice-dollar',
                customer: 'fa-user-circle'
            };
            const roleLabels = {
                super_admin: 'Super Admin',
                payout_admin: 'Platform Finance Admin',
                account_owner: 'Account Owner',
                marketing_manager: 'Marketing Manager',
                sales_agent: 'Sales Agent',
                finance: 'Finance',
                customer: 'Customer'
            };

            const normalizeRoleKey = (value) => {
                const key = String(value || '').trim().toLowerCase().replace(/[\s-]+/g, '_');
                return Object.prototype.hasOwnProperty.call(roleIcons, key) ? key : 'customer';
            };

            const authRole = normalizeRoleKey(document.body.getAttribute('data-auth-role') || 'customer');
            const authEmail = String(document.body.getAttribute('data-auth-email') || '').trim().toLowerCase();

            const setSplashRoleVisual = (roleKey) => {
                const normalized = normalizeRoleKey(roleKey);
                if (splashRoleIcon) {
                    const icon = roleIcons[normalized] || roleIcons.customer;
                    splashRoleIcon.className = 'fas ' + icon + ' login-splash__role-icon';
                }
                if (splashRoleLabel) {
                    splashRoleLabel.textContent = roleLabels[normalized] || roleLabels.customer;
                }
            };

            if (!logoutForms.length || !splash) {
                return;
            }

            setSplashRoleVisual(authRole);
            try {
                localStorage.setItem('splashRole', authRole);
                if (authEmail) {
                    const rawMap = localStorage.getItem('splashRoleByEmail');
                    const roleMap = rawMap ? JSON.parse(rawMap) : {};
                    roleMap[authEmail] = authRole;
                    localStorage.setItem('splashRoleByEmail', JSON.stringify(roleMap));
                }
            } catch (_e) {}

            const resetSubmitUi = () => {
                splash.classList.remove('is-visible');
                splash.classList.remove('is-slow');
                splash.setAttribute('aria-hidden', 'true');
                if (slowTimer) {
                    window.clearTimeout(slowTimer);
                    slowTimer = null;
                }
                isSubmitting = false;
            };

            logoutForms.forEach(function (logoutForm) {
                logoutForm.addEventListener('submit', function (event) {
                    if (isSubmitting) {
                        return;
                    }

                    event.preventDefault();
                    isSubmitting = true;
                    setSplashRoleVisual(authRole);
                    splash.classList.add('is-visible');
                    splash.setAttribute('aria-hidden', 'false');
                    splash.classList.remove('is-slow');
                    slowTimer = window.setTimeout(function () {
                        splash.classList.add('is-slow');
                    }, slowConnectionMs);

                    window.setTimeout(function () {
                        logoutForm.submit();
                    }, splashDisplayMs);
                });
            });

            window.addEventListener('pageshow', resetSubmitUi);
        })();
    </script>

    @if(session('success') || session('error'))
        <div id="statusToastContainer" class="status-toast-container">
            <div class="status-toast {{ session('success') ? 'success' : 'error' }}">
                <i class="status-icon fas {{ session('success') ? 'fa-check' : 'fa-times' }}"></i>
                <div>
                    <h4>{{ session('success') ? 'Success!' : 'Error!' }}</h4>
                    <p>{{ session('success') ?? session('error') }}</p>
                </div>
                <button type="button" class="status-toast-close" onclick="closeStatusToast()" aria-label="Close notification">
                    <i class="fas fa-times-circle"></i>
                </button>
            </div>
        </div>
        <script>
            function closeStatusToast() {
                const toastContainer = document.getElementById('statusToastContainer');
                if (toastContainer) {
                    toastContainer.style.display = 'none';
                }
            }

            setTimeout(closeStatusToast, 3000);
        </script>
    @endif
    @yield('scripts')
</body>
</html>
