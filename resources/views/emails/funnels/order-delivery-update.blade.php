@php
    $statusLabel = ucwords(str_replace('_', ' ', (string) $deliveryStatus));
    $name = trim((string) $customerName) !== '' ? trim((string) $customerName) : 'Customer';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Delivery Update</title>
</head>
<body style="margin:0;padding:0;background:#f7f7fb;font-family:Arial,sans-serif;color:#0f172a;">
    <div style="max-width:640px;margin:0 auto;padding:24px;">
        <div style="background:#ffffff;border:1px solid #e6e1ef;border-radius:18px;padding:28px;">
            <div style="font-size:12px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#64748b;">{{ $funnelName }}</div>
            <h1 style="margin:12px 0 10px;font-size:28px;line-height:1.2;color:#240E35;">Your order is {{ strtolower($statusLabel) }}</h1>
            <p style="margin:0 0 16px;font-size:15px;line-height:1.7;">Hi {{ $name }}, we’re sending you an update about your order.</p>

            <div style="margin:0 0 18px;padding:16px 18px;border-radius:14px;background:#f8fafc;border:1px solid #e2e8f0;">
                <div style="font-size:13px;color:#64748b;text-transform:uppercase;letter-spacing:.05em;font-weight:700;">Delivery Status</div>
                <div style="margin-top:8px;font-size:22px;font-weight:800;color:#240E35;">{{ $statusLabel }}</div>
                <div style="margin-top:8px;font-size:14px;color:#64748b;">Courier: {{ $courierName }}</div>
            </div>

            @if(!empty($orderItems))
                <div style="margin:0 0 18px;">
                    <div style="font-size:16px;font-weight:800;color:#240E35;margin-bottom:10px;">Order Summary</div>
                    <div style="border:1px solid #e6e1ef;border-radius:14px;overflow:hidden;">
                        @foreach($orderItems as $item)
                            <div style="padding:12px 16px;border-bottom:1px solid #eef2f7;">
                                <div style="font-weight:700;color:#0f172a;">{{ $item['name'] ?? 'Product' }} x{{ max(1, (int) ($item['quantity'] ?? 1)) }}</div>
                                <div style="margin-top:4px;font-size:13px;color:#64748b;">
                                    {{ trim(implode(' • ', array_filter([
                                        $item['badge'] ?? null,
                                        $item['price'] ?? null,
                                    ]))) }}
                                </div>
                            </div>
                        @endforeach
                        <div style="padding:12px 16px;background:#fcfbfe;font-size:13px;color:#475569;">
                            Total quantity: {{ $orderQuantity > 0 ? $orderQuantity : collect($orderItems)->sum(fn ($item) => max(1, (int) ($item['quantity'] ?? 1))) }}
                        </div>
                    </div>
                </div>
            @endif

            @if($customMessage)
                <div style="margin:0 0 18px;padding:16px 18px;border-radius:14px;background:#fff7ed;border:1px solid #fed7aa;">
                    <div style="font-size:16px;font-weight:800;color:#9a3412;margin-bottom:8px;">Message From Our Team</div>
                    <div style="font-size:14px;line-height:1.7;color:#7c2d12;">{{ $customMessage }}</div>
                </div>
            @endif

            @if($trackingUrl)
                <div style="margin:0 0 18px;">
                    <a href="{{ $trackingUrl }}" style="display:inline-block;padding:12px 18px;border-radius:999px;background:#240E35;color:#ffffff;text-decoration:none;font-weight:800;">Track Delivery</a>
                </div>
                <p style="margin:0 0 10px;font-size:13px;line-height:1.6;color:#64748b;">If the button doesn’t work, use this link:</p>
                <p style="margin:0;font-size:13px;line-height:1.6;word-break:break-all;color:#1d4ed8;">{{ $trackingUrl }}</p>
            @endif
        </div>
    </div>
</body>
</html>
