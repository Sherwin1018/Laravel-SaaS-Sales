<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manual Payment</title>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            background: linear-gradient(135deg, #f8fafc 0%, #eef2ff 100%);
            font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
            color: #0f172a;
        }
        .wrap { max-width: 920px; margin: 0 auto; padding: 22px 14px 34px; }
        .card {
            background: #ffffff;
            border: 1px solid #E6E1EF;
            border-radius: 18px;
            box-shadow: 0 22px 55px rgba(15,23,42,.12);
            padding: 16px;
        }
        .grid { display: grid; gap: 12px; }
        .row { display:flex; justify-content:space-between; gap:12px; flex-wrap:wrap; }
        label { font-weight: 800; color:#0f172a; }
        input, textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #E6E1EF;
            border-radius: 12px;
            box-sizing: border-box;
            font-weight: 650;
        }
        .btn {
            display:inline-flex;
            align-items:center;
            justify-content:center;
            padding: 12px 16px;
            border-radius: 12px;
            text-decoration:none;
            font-weight: 900;
            border: none;
            cursor: pointer;
        }
        .btn-primary { background:#166534; color:#fff; }
        .btn-dark { background:#0f172a; color:#fff; }
        .hint { margin: 0; color:#64748b; font-weight: 650; line-height: 1.6; }
        .pill {
            display:inline-flex;
            align-items:center;
            gap:8px;
            padding: 8px 12px;
            border-radius: 999px;
            border: 1px solid #E6E1EF;
            background:#F8FAFC;
            font-weight: 900;
            color:#0f172a;
        }
        .ok { background:#ECFDF5; border-color:#A7F3D0; color:#065F46; }
        .bad { background:#FEF2F2; border-color:#FECACA; color:#991B1B; }
        .muted { color:#64748b; font-weight: 750; font-size: 12px; letter-spacing:.08em; text-transform: uppercase; }
        .qr-block { text-align: center; }
        .qr-row { margin-top: 8px; display: grid; gap: 10px; justify-items: center; }
        .qr-row img { margin: 0 auto; }
        .qr-row p { margin: 0; text-align: center; max-width: 560px; }
        .modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.55);
            display: none;
            align-items: center;
            justify-content: center;
            padding: 18px;
            z-index: 999999;
        }
        .modal-backdrop.is-open { display: flex; }
        .modal-panel {
            width: min(520px, 100%);
            background: #ffffff;
            border-radius: 18px;
            border: 1px solid #E6E1EF;
            box-shadow: 0 28px 70px rgba(15,23,42,.28);
            padding: 18px 18px 16px;
            text-align: center;
        }
        .modal-title { margin: 0; font-size: 20px; font-weight: 950; color: #0F172A; }
        .modal-copy { margin: 8px 0 0; color:#475569; font-weight: 700; line-height: 1.6; }
        .modal-actions { display:flex; gap:10px; justify-content:center; flex-wrap:wrap; margin-top: 14px; }
    </style>
</head>
<body>
    <div class="wrap">
        <h1 style="margin: 0 0 6px; font-size: 26px; font-weight: 900; color: #0F172A;">Manual payment</h1>
        <p style="margin: 0 0 16px; color: #64748b; font-weight: 600; line-height: 1.6;">
            Use this option if you can’t pay via GCash/Card checkout. Your payment will be <strong>verified</strong> before access is confirmed.
        </p>

        @if(session('success'))
            <div class="modal-backdrop is-open" id="manualSuccessModal" aria-hidden="false">
                <div class="modal-panel" role="dialog" aria-modal="true" aria-label="Manual payment submitted">
                    <div class="muted">Manual payment</div>
                    <h2 class="modal-title">Submitted for verification</h2>
                    <p class="modal-copy">
                        {{ session('success') }}
                        <br>Redirecting you to the Thank You page in <strong id="manualRedirectCountdown">4</strong>s…
                    </p>
                    <div class="modal-actions">
                        <a class="btn btn-primary" href="{{ $thankYouUrl ?? '#' }}">Continue now</a>
                        <a class="btn btn-dark" href="{{ route('funnels.portal.step', ['funnelSlug' => $funnel->slug, 'stepSlug' => $step->slug]) }}">Back to checkout</a>
                    </div>
                </div>
            </div>
            <script>
                (function () {
                    var url = @json($thankYouUrl ?? '');
                    if (!url) return;
                    var count = 4;
                    var node = document.getElementById('manualRedirectCountdown');
                    var tick = function () {
                        count = Math.max(0, count - 1);
                        if (node) node.textContent = String(count);
                        if (count <= 0) {
                            window.location.assign(url);
                        }
                    };
                    window.setTimeout(function () { tick(); }, 1000);
                    window.setInterval(function () { tick(); }, 1000);
                })();
            </script>
        @endif

        @if($errors->any())
            <div class="card bad" style="margin: 12px 0;">
                Please fix the highlighted fields and try again.
            </div>
        @endif

        @php
            $destinationType = $payoutAccount?->destination_type ?? 'gcash';
            $destinationValue = (string) ($payoutAccount?->destination_value ?? '');
            $accountName = (string) ($payoutAccount?->account_name ?? '');
            $masked = (string) ($payoutAccount?->masked_destination ?? '');
            $qrPath = (string) data_get($payoutAccount, 'meta.gcash.qr_path', '');
        @endphp

        <div class="grid">
            <div class="card">
                <div class="row" style="align-items:flex-start;">
                    <div>
                        <div class="muted">Step</div>
                        <div style="color:#0f172a; font-weight:900; margin-top:6px;">{{ $step->title ?? 'Checkout' }}</div>
                    </div>
                    <div style="text-align:right;">
                        <div class="muted">Amount to send</div>
                        <div style="font-size: 22px; font-weight: 900; color:#0F172A; margin-top:6px;">₱ {{ number_format((float) $payment->amount, 2) }}</div>
                    </div>
                </div>
                <div style="margin-top: 12px; padding-top: 12px; border-top: 1px dashed #E6E1EF;">
                    <div class="muted">Send to</div>
                    <div style="margin-top:8px;">
                        <span class="pill">
                        {{ $destinationType === 'gcash' ? 'GCash' : 'Card / Bank' }}
                        @if($masked !== '')
                            · {{ $masked }}
                        @endif
                        </span>
                    </div>
                    @if($accountName !== '')
                        <div style="margin-top:8px; color:#64748b; font-weight:800;">Account name: {{ $accountName }}</div>
                    @endif
                    @if($destinationValue !== '')
                        <div style="margin-top:10px; padding: 10px 12px; border-radius: 12px; background:#F8FAFC; border:1px solid #E2E8F0;">
                            <div class="muted">Destination</div>
                            <div style="font-size: 16px; font-weight: 900; color:#0F172A; margin-top:4px; word-break:break-word;">
                                {{ $destinationValue }}
                            </div>
                        </div>
                    @endif
                    @if($destinationType === 'gcash' && $qrPath !== '')
                        <div style="margin-top:12px;">
                            <div class="muted">Scan QR (optional)</div>
                            <div class="qr-block">
                            <div class="qr-row">
                                <img src="{{ asset('storage/' . $qrPath) }}" alt="GCash QR"
                                    style="width:180px;height:180px;object-fit:contain;border-radius:14px;border:1px solid #E6E1EF;background:#fff;padding:10px;">
                                <p class="hint" style="max-width:520px;margin-top:0;">
                                    You can scan this QR in your GCash app, then upload the receipt and reference number below.
                                </p>
                            </div>
                            </div>
                        </div>
                    @endif
                    <p class="hint" style="margin-top:10px;">
                        After sending, upload your receipt and reference number below. Verification may take some time.
                    </p>
                </div>
            </div>

            <div class="card">
                <h3 style="margin:0 0 10px; font-weight: 900; color:#0F172A;">Upload payment proof</h3>
                <form method="POST" action="{{ $receiptActionUrl ?? route('funnels.portal.manual.receipt', ['funnelSlug' => $funnel->slug, 'stepSlug' => $step->slug, 'payment' => $payment->id]) }}" enctype="multipart/form-data">
                    @csrf

                    <div class="grid">
                        <div class="grid" style="gap:6px;">
                            <label>Reference number</label>
                            <input name="reference_number" value="{{ old('reference_number') }}" required
                                autocomplete="off">
                            @error('reference_number')<div style="color:#B91C1C;font-weight:700;font-size:12px;">{{ $message }}</div>@enderror
                        </div>

                        <div class="grid" style="gap:6px;">
                            <label>Receipt file (JPG/PNG/PDF)</label>
                            <input type="file" name="receipt_file" accept=".jpg,.jpeg,.png,.pdf"
                                style="background:#fff;">
                            @error('receipt_file')<div style="color:#B91C1C;font-weight:700;font-size:12px;">{{ $message }}</div>@enderror
                        </div>

                        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px;">
                            <div class="grid" style="gap:6px;">
                                <label>Your name (optional)</label>
                                <input name="customer_name" value="{{ old('customer_name') }}">
                            </div>
                            <div class="grid" style="gap:6px;">
                                <label>Email (optional)</label>
                                <input type="email" name="customer_email" value="{{ old('customer_email') }}">
                            </div>
                        </div>

                        <div class="grid" style="gap:6px;">
                            <label>Notes (optional)</label>
                            <textarea name="notes" rows="3"
                                style="resize: vertical;">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top: 12px;">
                        <button type="submit" class="btn btn-primary">
                            Submit receipt
                        </button>
                        <a href="{{ route('funnels.portal.step', ['funnelSlug' => $funnel->slug, 'stepSlug' => $step->slug]) }}"
                            class="btn btn-dark">
                            Back to checkout
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>

