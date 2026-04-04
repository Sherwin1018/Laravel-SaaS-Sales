<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Password</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: Inter, Arial, sans-serif; background: #f4f6fb; margin: 0; }
        .wrap { max-width: 520px; margin: 40px auto; background: #fff; border-radius: 12px; padding: 28px; box-shadow: 0 10px 30px rgba(15, 23, 42, 0.09); }
        h1 { margin: 0 0 8px; font-size: 28px; color: #0f172a; }
        p { color: #475569; margin: 0 0 18px; }
        label { display: block; font-weight: 600; color: #334155; margin: 14px 0 6px; }
        input { width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #cbd5e1; box-sizing: border-box; }
        .password-wrap { position: relative; }
        .password-wrap input { padding-right: 42px; }
        .toggle-eye {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            border: 0;
            background: transparent;
            color: #475569;
            font-size: 18px;
            width: 24px;
            height: 24px;
            padding: 0;
            margin: 0;
            cursor: pointer;
        }
        .toggle-eye:focus-visible { outline: 2px solid #240e35; outline-offset: 2px; border-radius: 4px; }
        button { width: 100%; margin-top: 20px; border: 0; border-radius: 8px; padding: 12px; background: #240e35; color: #fff; font-weight: 700; cursor: pointer; }
        .msg { border-radius: 8px; padding: 10px 12px; margin-bottom: 12px; font-size: 14px; }
        .msg.error { background: #fee2e2; color: #991b1b; }
        .hint { margin-top: 10px; font-size: 12px; color: #475569; }
    </style>
</head>
<body>
    <div class="wrap">
        <h1>Set Your Password</h1>
        <p>Account: <strong>{{ $email ?? 'N/A' }}</strong></p>

        @if(session('error'))
            <div class="msg error">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="msg error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('setup.complete', ['token' => $token]) }}">
            @csrf
            <label for="password">New Password</label>
            <div class="password-wrap">
                <input type="password" id="password" name="password" required autocomplete="new-password">
                <button type="button" class="toggle-eye" data-target="password" aria-label="Show password" title="Show password">👁</button>
            </div>

            <label for="password_confirmation">Confirm Password</label>
            <div class="password-wrap">
                <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password">
                <button type="button" class="toggle-eye" data-target="password_confirmation" aria-label="Show confirmation password" title="Show confirmation password">👁</button>
            </div>

            <p class="hint">Use 12 to 64 characters with uppercase, lowercase, number, and special character.</p>
            <button type="submit">Set Password and Activate</button>
        </form>
    </div>
    <script>
        document.querySelectorAll('.toggle-eye').forEach(function (button) {
            button.addEventListener('click', function () {
                var input = document.getElementById(button.dataset.target);
                if (!input) return;
                var show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                button.textContent = show ? '🙈' : '👁';
                button.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
                button.setAttribute('title', show ? 'Hide password' : 'Show password');
            });
        });
    </script>
</body>
</html>
