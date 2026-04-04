<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Link Expired</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: Inter, Arial, sans-serif; background: #f8fafc; margin: 0; }
        .wrap { max-width: 520px; margin: 40px auto; background: #fff; border-radius: 12px; padding: 28px; box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08); }
        h1 { margin: 0 0 8px; font-size: 28px; color: #0f172a; }
        p { color: #475569; margin: 0 0 18px; }
        label { display: block; font-weight: 600; color: #334155; margin: 14px 0 6px; }
        input { width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #cbd5e1; box-sizing: border-box; }
        button { width: 100%; margin-top: 20px; border: 0; border-radius: 8px; padding: 12px; background: #240e35; color: #fff; font-weight: 700; cursor: pointer; }
        .msg { border-radius: 8px; padding: 10px 12px; margin-bottom: 12px; font-size: 14px; }
        .msg.success { background: #dcfce7; color: #166534; }
        .msg.error { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
    <div class="wrap">
        <h1>Setup Link Expired</h1>
        <p>Your setup link is invalid or expired. Request a new activation email below.</p>

        @if(session('success'))
            <div class="msg success">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="msg error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('setup.resend') }}">
            @csrf
            <label for="email">Email Address</label>
            <input
                type="email"
                id="email"
                name="email"
                required
                value="{{ old('email', $email ?? '') }}"
                placeholder="you@example.com"
            >

            <button type="submit">Request New Activation Email</button>
        </form>
    </div>
</body>
</html>
