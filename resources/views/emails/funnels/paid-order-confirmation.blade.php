<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful</title>
</head>
@php
    $statusStyles = [
        'paid' => 'background:#dcfce7;color:#166534;border:1px solid #86efac;',
        'processing' => 'background:#dbeafe;color:#1d4ed8;border:1px solid #93c5fd;',
        'shipped' => 'background:#ede9fe;color:#6d28d9;border:1px solid #c4b5fd;',
        'out_for_delivery' => 'background:#fef3c7;color:#b45309;border:1px solid #fcd34d;',
        'delivered' => 'background:#ecfccb;color:#3f6212;border:1px solid #bef264;',
    ];
    $statusPill = static fn (?string $key): string => $statusStyles[(string) $key] ?? 'background:#f1f5f9;color:#0f172a;border:1px solid #cbd5e1;';
@endphp
<body style="margin:0;padding:0;background:#f8fafc;color:#0f172a;font-family:Arial,sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f8fafc;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:680px;background:#ffffff;border:1px solid #e2e8f0;border-radius:20px;overflow:hidden;">
                    <tr>
                        <td style="padding:28px 28px 18px;background:linear-gradient(135deg,#2e1065 0%,#4c1d95 100%);color:#ffffff;">
                            <div style="font-size:12px;letter-spacing:.14em;text-transform:uppercase;opacity:.85;font-weight:700;">Payment Successful</div>
                            <h1 style="margin:10px 0 0;font-size:32px;line-height:1.15;">Your order has been received</h1>
                            <p style="margin:14px 0 0;font-size:15px;line-height:1.7;opacity:.92;">
                                Hello {{ $customerName }}, your payment for <strong>{{ $funnelName }}</strong> is confirmed.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px 28px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;">
                                <tr>
                                    <td style="padding:0 0 12px;">
                                        <span style="display:inline-block;padding:8px 14px;border-radius:999px;font-weight:700;{{ $statusPill($paymentStatus ?? 'paid') }}">{{ $paymentStatusLabel }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:0 0 18px;">
                                        <span style="display:inline-block;padding:8px 14px;border-radius:999px;font-weight:700;{{ $statusPill($deliveryStatus ?? 'processing') }}">{{ $deliveryStatusLabel }}</span>
                                    </td>
                                </tr>
                            </table>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;background:#f8fafc;border:1px solid #e2e8f0;border-radius:16px;">
                                <tr>
                                    <td style="padding:18px 20px;">
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;">
                                            <tr>
                                                <td style="padding:0 0 10px;font-size:13px;color:#64748b;">Order date and time</td>
                                                <td align="right" style="padding:0 0 10px;font-size:15px;font-weight:700;color:#0f172a;">{{ $orderedAtLabel }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:0 0 10px;font-size:13px;color:#64748b;">Amount paid</td>
                                                <td align="right" style="padding:0 0 10px;font-size:15px;font-weight:700;color:#0f172a;">PHP {{ number_format((float) $amount, 2) }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:0 0 10px;font-size:13px;color:#64748b;">Current delivery status</td>
                                                <td align="right" style="padding:0 0 10px;font-size:15px;font-weight:700;color:#0f172a;">{{ $deliveryStatusLabel }}</td>
                                            </tr>
                                            @if(!empty($paymentMethod))
                                                <tr>
                                                    <td style="padding:0 0 10px;font-size:13px;color:#64748b;">Payment method</td>
                                                    <td align="right" style="padding:0 0 10px;font-size:15px;font-weight:700;color:#0f172a;">{{ $paymentMethod }}</td>
                                                </tr>
                                            @endif
                                            @if(!empty($reference))
                                                <tr>
                                                    <td style="padding:0;font-size:13px;color:#64748b;">Reference</td>
                                                    <td align="right" style="padding:0;font-size:15px;font-weight:700;color:#0f172a;">{{ $reference }}</td>
                                                </tr>
                                            @endif
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            @if(!empty($orderItems))
                                <div style="margin-top:24px;">
                                    <h2 style="margin:0 0 10px;font-size:18px;color:#0f172a;">Ordered products</h2>
                                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;border:1px solid #e2e8f0;border-radius:16px;overflow:hidden;">
                                        @foreach($orderItems as $item)
                                            <tr>
                                                <td style="padding:14px 16px;border-top:{{ $loop->first ? '0' : '1px solid #e2e8f0' }};">
                                                    <div style="font-size:15px;font-weight:700;color:#0f172a;">{{ $item['name'] ?? 'Product' }}</div>
                                                    <div style="margin-top:4px;font-size:13px;color:#64748b;">
                                                        Qty: {{ max(1, (int) ($item['quantity'] ?? 1)) }}
                                                        @if(!empty($item['price']))
                                                            | {{ $item['price'] }}
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </table>
                                </div>
                            @endif

                            @if(!empty($estimatedArrivalLabel))
                                <div style="margin-top:24px;">
                                    <h2 style="margin:0 0 10px;font-size:18px;color:#0f172a;">Estimated arrival</h2>
                                    <div style="padding:16px 18px;border-radius:16px;background:#fff7ed;border:1px solid #fdba74;">
                                        <div style="font-size:13px;color:#9a3412;font-weight:700;text-transform:uppercase;letter-spacing:.08em;">{{ $courierName }} estimated window</div>
                                        <div style="margin-top:6px;font-size:18px;font-weight:700;color:#7c2d12;">{{ $estimatedArrivalLabel }}</div>
                                        @if(!empty($estimatedArrivalRangeLabel) || !empty($estimatedArrivalRegionLabel))
                                            <div style="margin-top:8px;font-size:13px;color:#9a3412;">
                                                {{ trim(($estimatedArrivalRegionLabel ?? '') . (!empty($estimatedArrivalRangeLabel) ? ' | ' . $estimatedArrivalRangeLabel : '')) }}
                                            </div>
                                        @endif
                                        @if(!empty($estimatedArrivalNote))
                                            <div style="margin-top:8px;font-size:13px;line-height:1.6;color:#9a3412;">{{ $estimatedArrivalNote }}</div>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            @if(!empty($shippingAddress))
                                <div style="margin-top:24px;">
                                    <h2 style="margin:0 0 10px;font-size:18px;color:#0f172a;">Shipping address</h2>
                                    <div style="padding:16px 18px;border-radius:16px;background:#f8fafc;border:1px solid #e2e8f0;font-size:14px;line-height:1.7;color:#334155;">
                                        {{ $shippingAddress }}
                                    </div>
                                </div>
                            @endif

                            <div style="margin-top:24px;padding:20px;border-radius:18px;background:#eff6ff;border:1px solid #bfdbfe;">
                                <h2 style="margin:0 0 10px;font-size:18px;color:#0f172a;">Customer portal access</h2>
                                <p style="margin:0 0 14px;font-size:14px;line-height:1.7;color:#334155;">
                                    Your account is tagged as <strong>{{ $portalRole }}</strong> so you can log in and view your ordered products, payment confirmation, and delivery status updates.
                                </p>
                                @if($setupRequired && !empty($setupUrl))
                                    <p style="margin:0 0 14px;font-size:14px;line-height:1.7;color:#334155;">
                                        Set your password first, then use the portal anytime.
                                        @if(!empty($setupExpiresLabel))
                                            This setup link expires on <strong>{{ $setupExpiresLabel }}</strong>.
                                        @endif
                                    </p>
                                    <div style="margin:0 0 12px;">
                                        <a href="{{ $setupUrl }}" style="display:inline-block;padding:12px 18px;border-radius:12px;background:#2563eb;color:#ffffff;text-decoration:none;font-weight:700;">Set up password</a>
                                    </div>
                                @endif
                                <div>
                                    <a href="{{ $loginUrl }}" style="display:inline-block;padding:12px 18px;border-radius:12px;background:#0f172a;color:#ffffff;text-decoration:none;font-weight:700;">Open customer portal</a>
                                </div>
                            </div>

                            @if(!empty($statusTimeline) && is_array($statusTimeline))
                                <div style="margin-top:24px;">
                                    <h2 style="margin:0 0 10px;font-size:18px;color:#0f172a;">Delivery status guide</h2>
                                    <div>
                                        @foreach($statusTimeline as $statusStep)
                                            <span style="display:inline-block;margin:0 8px 8px 0;padding:8px 12px;border-radius:999px;font-size:12px;font-weight:700;{{ $statusPill($statusStep['key'] ?? null) }}">
                                                {{ $statusStep['label'] ?? 'Status' }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <p style="margin:24px 0 0;font-size:14px;line-height:1.7;color:#475569;">
                                We will keep your order updated as it moves through processing, shipping, out for delivery, and delivered.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
