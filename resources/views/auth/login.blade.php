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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
            <img src="{{ asset('images/nehemiahlogo.png') }}" alt="Nehemiah Solutions" class="login-splash__logo">
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
            const googleLoginLink = document.getElementById('googleLoginLink');
            const splash = document.getElementById('loginSplash');
            const defaultButtonLabel = submitButton ? submitButton.textContent : 'Login';
            const splashDisplayMs = 900;
            const googleSplashDisplayMs = 700;
            let isSubmitting = false;
            let isGoogleRedirecting = false;

            if (!loginForm || !submitButton || !splash) {
                return;
            }

            const resetSubmitUi = () => {
                splash.classList.remove('is-visible');
                splash.setAttribute('aria-hidden', 'true');
                submitButton.disabled = false;
                submitButton.textContent = defaultButtonLabel;
                isSubmitting = false;
            };

            loginForm.addEventListener('submit', function (event) {
                if (isSubmitting) {
                    return;
                }

                event.preventDefault();
                isSubmitting = true;
                splash.classList.add('is-visible');
                splash.setAttribute('aria-hidden', 'false');
                submitButton.disabled = true;
                submitButton.textContent = 'Signing In...';

                window.setTimeout(function () {
                    loginForm.submit();
                }, splashDisplayMs);

                window.setTimeout(function () {
                    if (document.body.contains(splash)) {
                        resetSubmitUi();
                    }
                }, 12000);
            });

            if (googleLoginLink) {
                googleLoginLink.addEventListener('click', function (event) {
                    if (isGoogleRedirecting || isSubmitting) {
                        event.preventDefault();
                        return;
                    }

                    event.preventDefault();
                    isGoogleRedirecting = true;
                    splash.classList.add('is-visible');
                    splash.setAttribute('aria-hidden', 'false');

                    window.setTimeout(function () {
                        window.location.href = googleLoginLink.href;
                    }, googleSplashDisplayMs);
                });
            }

            window.addEventListener('pageshow', resetSubmitUi);
        })();
    </script>

</body>
</html>
