<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verified</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'Inter', sans-serif; min-height: 100vh; display: flex; align-items: center; justify-content: center; background: #F3EEF7; padding: 24px; }
        .card { background: #fff; border-radius: 16px; padding: 48px; max-width: 420px; text-align: center; box-shadow: 0 10px 40px rgba(36,14,53,.12); }
        .icon { font-size: 56px; color: #16a34a; margin-bottom: 20px; }
        h1 { margin: 0 0 12px; font-size: 24px; color: #0f172a; }
        p { margin: 0; color: #64748b; line-height: 1.5; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon"><i class="fas fa-check-circle"></i></div>
        <h1>Email verified</h1>
        <p>{{ session('success', 'Thank you! Your email has been confirmed. You can close this window.') }}</p>
    </div>
</body>
</html>
