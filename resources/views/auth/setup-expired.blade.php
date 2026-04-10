<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Link Expired</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="{{ asset('css/extracted/auth-setup-expired-style1.css') }}">
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

