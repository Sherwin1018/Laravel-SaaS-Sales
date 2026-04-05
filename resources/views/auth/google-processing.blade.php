<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signing in...</title>
    <link rel="stylesheet" href="{{ asset('css/new_login.css') }}">
</head>
<body>
    <div id="loginSplash" class="login-splash is-visible" aria-hidden="false">
        <div class="login-splash__panel">
            <img src="{{ asset('images/nehemiahlogo.png') }}" alt="Nehemiah Solutions" class="login-splash__logo">
            <p class="login-splash__text">Signing you in...</p>
        </div>
    </div>

    <script>
        (function () {
            const redirectTo = @json($redirectTo);
            const fallback = @json(route('landing'));
            const delayMs = 900;

            window.setTimeout(function () {
                window.location.replace(redirectTo || fallback);
            }, delayMs);
        })();
    </script>
</body>
</html>
