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
        }
        body.builder-full-width .main-content {
            margin-left: 0;
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
        }
    </style>
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @yield('styles')
    @stack('styles')
</head>
<body class="@if(request()->routeIs('funnels.edit')) builder-full-width @endif">
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
    @unless(request()->routeIs('funnels.edit'))
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
            @endif

            {{-- Funnels & Automation (Owner, Marketing) --}}
            @if(auth()->user()->hasRole('account-owner') || auth()->user()->hasRole('marketing-manager'))
                <a href="{{ route('funnels.index') }}" class="{{ request()->routeIs('funnels.*') ? 'active' : '' }}"><i class="fas fa-filter"></i> <span>Funnels</span></a>
                <a href="{{ route('automation.overview') }}" class="{{ request()->routeIs('automation.*') ? 'active' : '' }}"><i class="fas fa-clipboard-list"></i> <span>Automation</span></a>
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

                        <form method="POST" action="{{ route('logout') }}">
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
    </script>

    @if(session('success') || session('error') || session('warning'))
        @php
            $toastType = session('success') ? 'success' : (session('warning') ? 'warning' : 'error');
            $toastTitle = session('success') ? 'Success!' : (session('warning') ? 'Notice' : 'Error!');
            $toastIcon = session('success') ? 'fa-check' : (session('warning') ? 'fa-exclamation-triangle' : 'fa-times');
            $toastBody = session('success') ?? session('warning') ?? session('error');
        @endphp
        <div id="statusToastContainer" class="status-toast-container">
            <div class="status-toast {{ $toastType }}">
                <i class="status-icon fas {{ $toastIcon }}"></i>
                <div>
                    <h4>{{ $toastTitle }}</h4>
                    <p>{{ $toastBody }}</p>
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

            setTimeout(closeStatusToast, {{ session('warning') ? 8000 : 3000 }});
        </script>
    @endif
    @yield('scripts')
    @stack('scripts')
</body>
</html>
