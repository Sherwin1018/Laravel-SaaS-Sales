<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signing in...</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/new_login.css') }}">
</head>
<body>
    <div id="loginSplash" class="login-splash is-visible" aria-hidden="false">
        <div class="login-splash__panel">
            <div class="login-splash__icon-stack" aria-hidden="true">
                <i id="loginSplashRoleIcon" class="fas fa-user-circle login-splash__role-icon"></i>
                <span class="login-splash__slow-icon" title="Slow connection detected">
                    <i class="fas fa-wifi"></i>
                </span>
            </div>
            <p id="loginSplashRoleLabel" class="login-splash__role-label">Customer</p>
            <p class="login-splash__text">Signing you in...</p>
        </div>
    </div>

    <script>
        (function () {
            const redirectTo = @json($redirectTo);
            const fallback = @json(route('landing'));
            const splashRoleFromServer = @json($splashRole ?? 'customer');
            const splashEmailFromServer = @json($splashEmail ?? '');
            const delayMs = 900;
            const slowConnectionMs = 2200;
            const splash = document.getElementById('loginSplash');
            const icon = document.getElementById('loginSplashRoleIcon');
            const label = document.getElementById('loginSplashRoleLabel');
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
            const setSplashRoleVisual = (roleKey) => {
                const normalized = normalizeRoleKey(roleKey);
                if (icon) {
                    const iconName = roleIcons[normalized] || roleIcons.customer;
                    icon.className = 'fas ' + iconName + ' login-splash__role-icon';
                }
                if (label) {
                    label.textContent = roleLabels[normalized] || roleLabels.customer;
                }
            };
            try {
                const normalizedServerRole = normalizeRoleKey(splashRoleFromServer);
                setSplashRoleVisual(normalizedServerRole);
                localStorage.setItem('splashRole', normalizedServerRole);

                const normalizedEmail = String(splashEmailFromServer || '').trim().toLowerCase();
                if (normalizedEmail) {
                    const rawMap = localStorage.getItem('splashRoleByEmail');
                    const roleMap = rawMap ? JSON.parse(rawMap) : {};
                    roleMap[normalizedEmail] = normalizedServerRole;
                    localStorage.setItem('splashRoleByEmail', JSON.stringify(roleMap));
                }
            } catch (_e) {
                setSplashRoleVisual(splashRoleFromServer || 'customer');
            }
            window.setTimeout(function () {
                if (splash) splash.classList.add('is-slow');
            }, slowConnectionMs);

            window.setTimeout(function () {
                window.location.replace(redirectTo || fallback);
            }, delayMs);
        })();
    </script>
</body>
</html>
