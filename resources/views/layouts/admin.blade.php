<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Super Admin Dashboard')</title>
    <link rel="stylesheet" href="{{ asset('css/admin-dashboard.css') }}">
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @yield('styles')
</head>
<body>

    <!-- Sidebar -->
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
            @endif

            {{-- TENANT LINKS (Account Owner, Marketing, Sales, Finance) --}}

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
                <a href="#"><i class="fas fa-filter"></i> <span>Funnels</span></a>
                <a href="#"><i class="fas fa-clipboard-list"></i> <span>Automation</span></a>
            @endif

            {{-- Billing (Owner, Finance) --}}
            @if(auth()->user()->hasRole('account-owner') || auth()->user()->hasRole('finance'))
                <a href="{{ route('payments.index') }}" class="{{ request()->routeIs('payments.*') ? 'active' : '' }}">
                    <i class="fas fa-file-invoice-dollar"></i> <span>Billing</span>
                </a>
            @endif
            
            {{-- Analytics (Owner, Marketing, Finance) --}}
            @if(auth()->user()->hasRole('account-owner') || auth()->user()->hasRole('marketing-manager') || auth()->user()->hasRole('finance'))
                <a href="#"><i class="fas fa-chart-line"></i> <span>Reports</span></a>
            @endif
        </div>

        <div class="account-info-wrapper">
            <div class="account-info">
                <div class="account-details">
                    <strong>{{ auth()->user()->name }}</strong>
                    <small>{{ auth()->user()->email }}</small>
                </div>

                <div class="account-menu">
                    <button class="dots-btn" onclick="toggleAccountMenu(event)">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>

                    <div id="accountDropdown" class="account-dropdown">
                        <a href="#" class="dropdown-link">Manage Profile</a>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-btn">Logout</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Main Content -->
    <div class="main-content">
        @yield('content')
    </div>

    <script>

        function toggleAccountMenu(event) {
            event.stopPropagation(); // Prevent window click from firing
            const dropdown = document.getElementById("accountDropdown");

            if (dropdown.style.display === "block") {
                dropdown.style.display = "none";
            } else {
                dropdown.style.display = "block";
            }
        }

        // Close dropdown when clicking anywhere outside
        document.addEventListener("click", function(event) {
            const dropdown = document.getElementById("accountDropdown");
            const menu = document.querySelector(".account-menu");

            if (!menu.contains(event.target)) {
                dropdown.style.display = "none";
            }
        });

        // Sidebar Toggle
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('sidebarToggle');
        
        if (sidebar && toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                sidebar.classList.toggle('collapsed');
            });
        }
    </script>
    @yield('scripts')
</body>
</html>
