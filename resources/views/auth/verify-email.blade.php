<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify your email</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/new_login.css') }}">
    <style>
        .verify-card { max-width: 480px; margin: 0 auto; }
        .verify-card h1 { font-size: 24px; margin-bottom: 12px; }
        .verify-card .subtitle { color: var(--text-light, #6B7280); margin-bottom: 24px; }
        .verify-card .icon-wrap { font-size: 48px; color: var(--primary-color, #240E35); margin-bottom: 20px; }
    </style>
</head>
<body>
    @if(session('success') || session('error'))
        <div id="statusToastContainer" style="position:fixed;top:18px;right:18px;z-index:9999;">
            <div style="display:flex;gap:12px;align-items:center;background:#fff;border-radius:14px;width:min(90vw,380px);padding:12px 38px 12px 12px;border:1px solid #E6E1EF;box-shadow:0 18px 38px rgba(15,23,42,.2);position:relative;">
                <i class="fas {{ session('success') ? 'fa-check' : 'fa-times' }}" style="font-size:24px;color:{{ session('success') ? '#65A30D' : '#B91C1C' }};"></i>
                <div>
                    <h4 style="margin:0 0 6px 0;font-size:15px;font-weight:800;color:{{ session('success') ? '#65A30D' : '#B91C1C' }};">{{ session('success') ? 'Success!' : 'Error!' }}</h4>
                    <p style="margin:0;color:#334155;font-size:14px;">{{ session('success') ?? session('error') }}</p>
                </div>
                <button type="button" onclick="document.getElementById('statusToastContainer').style.display='none'" aria-label="Close" style="position:absolute;top:8px;right:8px;border:none;background:transparent;cursor:pointer;"><i class="fas fa-times-circle"></i></button>
            </div>
        </div>
        <script>setTimeout(function(){ document.getElementById('statusToastContainer').style.display='none'; }, 4000);</script>
    @endif

    <div class="login-container">
        <div class="info-panel">
            <img src="{{ asset('images/logo3.png') }}" alt="Logo" class="info-logo">
            <h1>Almost there</h1>
            <p class="info-subtitle">Verify your email address to access the full platform.</p>
        </div>
        <div class="login-card verify-card">
            <div class="icon-wrap"><i class="fas fa-envelope-circle-check"></i></div>
            <h1>Verify your email</h1>
            <p class="subtitle">We've sent a verification link to <strong>{{ auth()->user()->email }}</strong>. Click the link in that email to verify your account.</p>
            @error('email')
                <p style="color:#B91C1C;font-size:14px;margin-bottom:16px;">{{ $message }}</p>
            @enderror
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit">Resend verification email</button>
            </form>
            <p style="margin-top:20px;font-size:14px;color:var(--text-light,#6B7280);">
                <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" style="color:var(--primary-color,#240E35);">Log out</a>
            </p>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
        </div>
    </div>
</body>
</html>
