<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm your email</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'Inter', sans-serif; min-height: 100vh; display: flex; align-items: center; justify-content: center; background: #F3EEF7; padding: 24px; }
        .card { background: #fff; border-radius: 16px; padding: 48px; max-width: 420px; text-align: center; box-shadow: 0 10px 40px rgba(36,14,53,.12); }
        .icon { font-size: 56px; color: #240E35; margin-bottom: 20px; }
        h1 { margin: 0 0 12px; font-size: 24px; color: #0f172a; }
        p { margin: 0 0 10px; color: #64748b; line-height: 1.5; }
        .helper { font-size: 14px; color: #475569; margin-top: 6px; }
        .actions { margin-top: 24px; display: grid; gap: 10px; }
        .btn {
            appearance: none;
            border: 1px solid #240E35;
            background: #240E35;
            color: #fff;
            padding: 10px 14px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn-secondary {
            background: #fff;
            color: #240E35;
        }
        .status {
            margin-top: 14px;
            font-size: 13px;
            color: #6b7280;
            min-height: 18px;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon"><i class="fas fa-envelope-circle-check"></i></div>
        <h1>Check your email</h1>
        <p>We sent a confirmation link to your email address. Click the link to verify and complete your sign-up.</p>
        <p class="helper">If you verify in another tab or browser, you can continue from here.</p>
        @if($hasPendingVerification)
            <div class="status" id="verificationStatus">Waiting for verification...</div>
        @endif
    </div>
    @if($hasPendingVerification)
        <script>
            (function () {
                const statusEl = document.getElementById('verificationStatus');
                const statusUrl = "{{ route('funnels.lead.confirm-email.status', ['funnelSlug' => $funnel->slug]) }}";

                let polling = false;

                async function checkStatus() {
                    if (polling) return;
                    polling = true;

                    try {
                        const res = await fetch(statusUrl, {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' },
                            credentials: 'same-origin'
                        });
                        const data = await res.json();

                        if (data && data.verified && data.redirect_to) {
                            statusEl.textContent = 'Verified. Redirecting...';
                            window.location.href = data.redirect_to;
                            return;
                        }

                        statusEl.textContent = (data && data.message) ? data.message : 'Still waiting for verification.';
                    } catch (e) {
                        statusEl.textContent = 'Unable to check status right now. Please try again.';
                    } finally {
                        polling = false;
                    }
                }

                setInterval(function () {
                    checkStatus();
                }, 5000);
            })();
        </script>
    @endif
</body>
</html>
