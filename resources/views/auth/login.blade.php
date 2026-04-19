<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">

    <!-- Custom CSS -->
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/new_login.css') }}">
</head>
<body>
    @if(session('success') || session('error'))
        <div id="statusToastContainer" style="position:fixed;top:18px;right:18px;z-index:9999;">
            <div style="display:flex;gap:12px;align-items:center;background:#fff;border-radius:14px;width:min(90vw,380px);padding:12px 38px 12px 12px;border:1px solid var(--theme-border, #E6E1EF);box-shadow:0 18px 38px rgba(15,23,42,.2);position:relative;">
                <i class="fas {{ session('success') ? 'fa-check' : 'fa-times' }}" style="font-size:24px;color:{{ session('success') ? '#65A30D' : '#B91C1C' }};"></i>
                <div>
                    <h4 style="margin:0 0 6px 0;font-size:15px;font-weight:800;color:{{ session('success') ? '#65A30D' : '#B91C1C' }};">
                        {{ session('success') ? 'Success!' : 'Error!' }}
                    </h4>
                    <p style="margin:0;color:#334155;font-size:14px;font-weight:500;line-height:1.2;">
                        {{ session('success') ?? session('error') }}
                    </p>
                </div>
                <button type="button" onclick="closeStatusToast()" aria-label="Close notification"
                    style="position:absolute;top:8px;right:8px;border:none;background:transparent;color:#334155;font-size:16px;cursor:pointer;line-height:1;">
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

    <div class="login-container">
        <!-- Left Info Panel -->
        <div class="info-panel">
            <img src="{{ asset('images/logo3.png') }}" alt="Funnel System Logo" class="info-logo">
            <h1>Grow Your Sales Efficiently</h1>
            <p class="info-subtitle">Manage leads, automate marketing campaigns, and track performance in one platform.</p>

            <ul class="features">
                <li>
                    <span class="feature-title">Lead Management:</span>
                    <span class="feature-text">Capture and organize all your leads seamlessly</span>
                </li>
                <li>
                    <span class="feature-title">Marketing Automation:</span>
                    <span class="feature-text">Automate email sequences and customer follow-ups</span>
                </li>
                <li>
                    <span class="feature-title">Analytics Dashboard:</span>
                    <span class="feature-text">Monitor campaign performance and conversion rates</span>
                </li>
            </ul>
        </div>

        <!-- Right Login Panel -->
        <div class="login-card">
            <a href="{{ route('landing') }}" class="back-link">
                <i class="fas fa-arrow-left"></i>
                <span>Back to landing page</span>
            </a>

            <img src="{{ asset('images/logo2.png') }}" alt="Funnel System Logo" class="login-logo">

            <h1>Login to Funnel System</h1>
            <p class="subtitle">Access your sales and marketing dashboard</p>

            <form id="loginForm" method="POST" action="{{ route('login.post') }}">
                @csrf
                <label for="email">Email Address</label>
                <input type="email" name="email" placeholder="Email" required>

                <label for="password">Password</label>
                <div class="password-container">
                    <input type="password" name="password" id="password" placeholder="Password" required>
                    <i class="fas fa-eye toggle-password" onclick="togglePassword()"></i>
                </div>

                <button id="loginSubmitButton" type="submit">Login</button>
            </form>
            <a id="googleLoginLink" href="{{ route('auth.google.redirect') }}" style="display:flex;align-items:center;justify-content:center;gap:10px;margin-top:12px;padding:12px;border-radius:10px;border:1px solid #E6E1EF;background:#fff;color:#111827;text-decoration:none;font-weight:600;">
                <i class="fab fa-google" style="color:#ea4335;"></i>
                Continue with Google
            </a>

            <p class="register-link">
                Don't have an account? <a href="{{ route('register') }}">Register here</a>
            </p>
        </div>
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
            <p class="login-splash__text">Signing you in...</p>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const icon = document.querySelector('.toggle-password');
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

        (function () {
            const loginForm = document.getElementById('loginForm');
            const submitButton = document.getElementById('loginSubmitButton');
            const splash = document.getElementById('loginSplash');
            const splashRoleIcon = document.getElementById('loginSplashRoleIcon');
            const splashRoleLabel = document.getElementById('loginSplashRoleLabel');
            const defaultButtonLabel = submitButton ? submitButton.textContent : 'Login';
            const splashDisplayMs = 900;
            const slowConnectionMs = 2200;
            let isSubmitting = false;
            let slowTimer = null;
            let redirectTimer = null;

            if (!loginForm || !submitButton || !splash) {
                return;
            }

            const roleIcons = {
                super_admin: 'fa-user-shield',
                account_owner: 'fa-building-user',
                marketing_manager: 'fa-chart-line',
                sales_agent: 'fa-handshake',
                finance: 'fa-file-invoice-dollar',
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
                if (splashRoleIcon) {
                    const icon = roleIcons[normalized] || roleIcons.customer;
                    splashRoleIcon.className = 'fas ' + icon + ' login-splash__role-icon';
                }
                if (splashRoleLabel) {
                    splashRoleLabel.textContent = roleLabels[normalized] || roleLabels.customer;
                }
            };

            const closeStatusToast = () => {
                const toastContainer = document.getElementById('statusToastContainer');
                if (toastContainer) {
                    toastContainer.style.display = 'none';
                }
            };

            const showStatusToast = (message, type) => {
                const tone = type === 'success' ? 'success' : 'error';
                const existing = document.getElementById('statusToastContainer');
                if (existing) {
                    existing.remove();
                }

                const container = document.createElement('div');
                container.id = 'statusToastContainer';
                container.style.position = 'fixed';
                container.style.top = '18px';
                container.style.right = '18px';
                container.style.zIndex = '9999';
                container.innerHTML =
                    '<div style="display:flex;gap:12px;align-items:center;background:#fff;border-radius:14px;width:min(90vw,380px);padding:12px 38px 12px 12px;border:1px solid var(--theme-border, #E6E1EF);box-shadow:0 18px 38px rgba(15,23,42,.2);position:relative;">'
                    + '<i class="fas ' + (tone === 'success' ? 'fa-check' : 'fa-times') + '" style="font-size:24px;color:' + (tone === 'success' ? '#65A30D' : '#B91C1C') + ';"></i>'
                    + '<div>'
                    + '<h4 style="margin:0 0 6px 0;font-size:15px;font-weight:800;color:' + (tone === 'success' ? '#65A30D' : '#B91C1C') + ';">' + (tone === 'success' ? 'Success!' : 'Error!') + '</h4>'
                    + '<p style="margin:0;color:#334155;font-size:14px;font-weight:500;line-height:1.2;"></p>'
                    + '</div>'
                    + '<button type="button" aria-label="Close notification" style="position:absolute;top:8px;right:8px;border:none;background:transparent;color:#334155;font-size:16px;cursor:pointer;line-height:1;">'
                    + '<i class="fas fa-times-circle"></i>'
                    + '</button>'
                    + '</div>';
                const messageNode = container.querySelector('p');
                if (messageNode) {
                    messageNode.textContent = String(message || '');
                }
                const closeButton = container.querySelector('button');
                if (closeButton) {
                    closeButton.addEventListener('click', closeStatusToast);
                }
                document.body.appendChild(container);
                window.setTimeout(closeStatusToast, 3000);
            };

            const extractErrorMessage = (payload) => {
                if (payload && payload.errors && typeof payload.errors === 'object') {
                    const firstKey = Object.keys(payload.errors)[0];
                    if (firstKey && Array.isArray(payload.errors[firstKey]) && payload.errors[firstKey][0]) {
                        return String(payload.errors[firstKey][0]);
                    }
                }
                if (payload && payload.message) {
                    return String(payload.message);
                }
                return 'Login Failed. Please try again.';
            };

            loginForm.addEventListener('submit', function (event) {
                event.preventDefault();

                if (isSubmitting) {
                    return;
                }

                isSubmitting = true;
                submitButton.disabled = true;
                submitButton.textContent = 'Signing In...';

                const formData = new FormData(loginForm);
                fetch(loginForm.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData,
                    credentials: 'same-origin'
                })
                    .then(async function (response) {
                        let payload = {};
                        try {
                            payload = await response.json();
                        } catch (_e) {}

                        if (!response.ok || !payload.ok) {
                            throw payload;
                        }

                        const roleKey = normalizeRoleKey(payload.splash_role || 'customer');
                        const email = String(payload.splash_email || '').trim().toLowerCase();
                        setSplashRoleVisual(roleKey);
                        splash.classList.add('is-visible');
                        splash.classList.remove('is-slow');
                        splash.setAttribute('aria-hidden', 'false');

                        try {
                            localStorage.setItem('splashRole', roleKey);
                            if (email) {
                                const rawMap = localStorage.getItem('splashRoleByEmail');
                                const roleMap = rawMap ? JSON.parse(rawMap) : {};
                                roleMap[email] = roleKey;
                                localStorage.setItem('splashRoleByEmail', JSON.stringify(roleMap));
                            }
                        } catch (_e) {}

                        slowTimer = window.setTimeout(function () {
                            splash.classList.add('is-slow');
                        }, slowConnectionMs);

                        redirectTimer = window.setTimeout(function () {
                            window.location.replace(String(payload.redirect_to || '{{ route('landing') }}'));
                        }, splashDisplayMs);
                    })
                    .catch(function (payload) {
                        isSubmitting = false;
                        submitButton.disabled = false;
                        submitButton.textContent = defaultButtonLabel;
                        showStatusToast(extractErrorMessage(payload), 'error');
                    });
            });

            window.addEventListener('pageshow', function () {
                isSubmitting = false;
                submitButton.disabled = false;
                submitButton.textContent = defaultButtonLabel;
                splash.classList.remove('is-visible');
                splash.classList.remove('is-slow');
                splash.setAttribute('aria-hidden', 'true');
                if (slowTimer) {
                    window.clearTimeout(slowTimer);
                    slowTimer = null;
                }
                if (redirectTimer) {
                    window.clearTimeout(redirectTimer);
                    redirectTimer = null;
                }
            });
        })();
    </script>

</body>
</html>
