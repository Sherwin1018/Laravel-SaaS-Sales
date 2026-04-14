<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirecting to PayMongo</title>
    <meta http-equiv="refresh" content="1;url={{ $checkoutUrl }}">
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            background: linear-gradient(135deg, #f8fafc 0%, #eef2ff 100%);
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            color: #1f2937;
        }
        .card {
            width: min(92vw, 460px);
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 26px 60px rgba(15, 23, 42, 0.16);
            padding: 32px 28px;
            text-align: center;
        }
        .spinner {
            width: 54px;
            height: 54px;
            border-radius: 999px;
            border: 5px solid #e5e7eb;
            border-top-color: #31124b;
            margin: 0 auto 18px;
            animation: spin 0.85s linear infinite;
        }
        h1 {
            margin: 0 0 12px;
            font-size: 28px;
            line-height: 1.15;
            color: #31124b;
        }
        p {
            margin: 0 0 18px;
            font-size: 16px;
            line-height: 1.6;
            color: #64748b;
        }
        a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 18px;
            border-radius: 999px;
            background: #31124b;
            color: #ffffff;
            text-decoration: none;
            font-weight: 700;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="spinner" aria-hidden="true"></div>
        <h1>Redirecting to PayMongo</h1>
        <p>Your checkout session is ready. If the payment page does not open automatically, use the button below.</p>
        <a href="{{ $checkoutUrl }}" id="paymongoRedirectLink">Continue to Payment</a>
    </div>

    <script>
        window.setTimeout(function(){
            window.location.assign(@json($checkoutUrl));
        }, 150);
    </script>
</body>
</html>
