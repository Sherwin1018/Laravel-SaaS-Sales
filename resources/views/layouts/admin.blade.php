<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Super Admin Dashboard')</title>
    <link rel="stylesheet" href="{{ asset('css/admin-dashboard.css') }}">
    @php
        $authUser = auth()->user();
        $tenant = $authUser?->tenant;
        $themePrimary = $tenant->theme_primary_color ?? '#240E35';
        $themeAccent = $tenant->theme_accent_color ?? '#6B4A7A';
        $themeSidebarBg = $tenant->theme_sidebar_bg ?? '#240E35';
        $themeSidebarText = $tenant->theme_sidebar_text ?? '#F8F4FB';
        $themeSidebarIcon = '#E7D8F0';
        $themeBodyBg = '#F7F7FB';
        $themeBodyText = '#111827';
        $themePrimaryDark = '#2E1244';
        $themeSurface = '#FFFFFF';
        $themeSurfaceSoft = '#F3EEF7';
        $themeSurfaceSofter = '#F7F7FB';
        $themeBorder = '#E6E1EF';
        $themeAccentStrong = '#9E7BB5';
        $themeMuted = '#6B7280';
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
        .main-content button[type="submit"]:not(.modal-close-btn):not(.status-toast-close):not(.toggle-btn):not(.dots-btn):not(.toggle-eye):not(.landing-video-close):not([style*="background:none"]):not([style*="background: none"]):not([style*="padding:0"]):not([style*="padding: 0"]),
        .main-content button[type="button"]:not(.modal-close-btn):not(.status-toast-close):not(.toggle-btn):not(.dots-btn):not(.toggle-eye):not(.landing-video-close):not([style*="background:none"]):not([style*="background: none"]):not([style*="padding:0"]):not([style*="padding: 0"]),
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
<body class="@if(request()->routeIs('funnels.edit') || request()->routeIs('admin.funnel-templates.edit')) builder-full-width @endif" data-auth-role="{{ match (true) { auth()->user()->hasRole('super-admin') => 'super_admin', auth()->user()->hasRole('account-owner') => 'account_owner', auth()->user()->hasRole('marketing-manager') => 'marketing_manager', auth()->user()->hasRole('sales-agent') => 'sales_agent', auth()->user()->hasRole('finance') => 'finance', default => 'customer' } }}" data-auth-email="{{ strtolower((string) auth()->user()->email) }}">
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
                <a href="{{ route('admin.funnel-templates.index') }}" class="{{ request()->routeIs('admin.funnel-templates.*') ? 'active' : '' }}">
                    <i class="fas fa-layer-group"></i> <span>Templates</span>
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
            @if(auth()->user()->hasRole('account-owner') || auth()->user()->hasRole('marketing-manager') || auth()->user()->hasRole('finance'))
                <a href="#"><i class="fas fa-chart-line"></i> <span>Reports</span></a>
            @endif
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
                        <a href="{{ route('profile.show') }}" class="dropdown-link">Manage Profile</a>

                        <form method="POST" action="{{ route('logout') }}" data-logout-form>
                            @csrf
                            <button type="submit" class="dropdown-btn">Logout</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
    @endunless

    <!-- Main Content -->
    <div class="main-content">
        @yield('content')
    </div>

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
        
        if (sidebar && toggleBtn) {
            const mobileSidebarMedia = window.matchMedia('(max-width: 768px)');
            const syncSidebarResponsiveState = () => {
                if (mobileSidebarMedia.matches) {
                    if (!sidebar.dataset.mobileInit) {
                        sidebar.classList.add('collapsed');
                        sidebar.dataset.mobileInit = '1';
                    }
                } else {
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
                sidebar.classList.toggle('collapsed');
                if (mobileSidebarMedia.matches) {
                    sidebar.dataset.mobileInit = '1';
                }
            });
        }

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
                account_owner: 'fa-building-user',
                marketing_manager: 'fa-chart-line',
                sales_agent: 'fa-handshake',
                finance: 'fa-file-peso-sign',
                customer: 'fa-user-circle'
            };
            const roleLabels = {
                super_admin: 'Super Admin',
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
