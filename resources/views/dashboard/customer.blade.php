@extends('layouts.admin')

@section('title', 'My Orders')

@php
    $statusStyles = [
        'paid' => 'background:#dcfce7;color:#166534;border:1px solid #86efac;',
        'processing' => 'background:#dbeafe;color:#1d4ed8;border:1px solid #93c5fd;',
        'shipped' => 'background:#ede9fe;color:#6d28d9;border:1px solid #c4b5fd;',
        'out_for_delivery' => 'background:#fef3c7;color:#b45309;border:1px solid #fcd34d;',
        'delivered' => 'background:#ecfccb;color:#3f6212;border:1px solid #bef264;',
    ];
    $statusLabel = static fn (?string $value): string => ucwords(str_replace('_', ' ', (string) ($value ?: 'paid')));
    $statusStyle = static fn (?string $value): string => $statusStyles[$value ?: 'paid'] ?? 'background:#f1f5f9;color:#0f172a;border:1px solid #cbd5e1;';
@endphp

@section('content')
    <style>
        .customer-orders-shell {
            border: 1px solid #dbe4f0;
            border-radius: 24px;
            background:
                radial-gradient(circle at top right, rgba(37, 99, 235, 0.08), transparent 28%),
                linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
            padding: 22px;
            box-shadow: 0 18px 44px rgba(15, 23, 42, 0.05);
        }
        .customer-orders-heading {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            width: 100%;
        }
        .customer-order-search {
            display: flex;
            flex-wrap: nowrap;
            gap: 10px;
            align-items: center;
            margin-left: auto;
        }
        .customer-order-search__group {
            display: grid;
            gap: 0;
            position: relative;
        }
        .customer-order-search__label {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }
        .customer-order-search__field {
            flex: 1 1 320px;
            width: 320px;
            min-height: 46px;
            padding: 0 16px 0 42px;
            border: 1px solid #cbd5e1;
            border-radius: 14px;
            background: #fff;
            color: #0f172a;
            font-size: 14px;
        }
        .customer-order-search__icon {
            position: absolute;
            left: 14px;
            bottom: 14px;
            color: #94a3b8;
            font-size: 14px;
            pointer-events: none;
        }
        .customer-order-search__field:focus {
            outline: none;
            border-color: #93c5fd;
            box-shadow: 0 0 0 3px rgba(147, 197, 253, 0.25);
        }
        .customer-order-search__clear {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 46px;
            padding: 0 16px;
            border-radius: 14px;
            border: 1px solid #cbd5e1;
            background: #fff;
            color: #31537f;
            font-size: 14px;
            font-weight: 800;
            text-decoration: none;
            cursor: pointer;
        }
        .customer-summary-panel {
            border: 1px solid #d9e3f1;
            border-radius: 20px;
            background: #fff;
            padding: 18px;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.9);
        }
        .customer-summary-panel--accent {
            background: linear-gradient(180deg, #ffffff 0%, #f3f8ff 100%);
        }
        .customer-summary-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #64748b;
            font-weight: 800;
        }
        .customer-summary-value {
            margin-top: 6px;
            font-size: clamp(24px, 2.2vw, 34px);
            line-height: 1;
            font-weight: 900;
            color: #0f172a;
            letter-spacing: -.03em;
        }
        .customer-summary-meta {
            margin-top: 6px;
            color: #64748b;
            font-size: 13px;
            line-height: 1.5;
        }
        .customer-order-grid {
            display: grid;
            gap: 16px;
        }
        .customer-order-card {
            border: 1px solid #dbe4f0;
            border-radius: 20px;
            padding: 18px;
            background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
            box-shadow: 0 14px 30px rgba(15, 23, 42, 0.04);
        }
        .customer-order-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #5b7292;
            font-weight: 800;
        }
        .customer-order-value {
            margin-top: 6px;
            font-size: 18px;
            line-height: 1.2;
            font-weight: 900;
            color: #0f172a;
        }
        .customer-status-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 7px 12px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 800;
        }
        .customer-status-pill::before {
            content: "";
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: currentColor;
            opacity: .75;
        }
        .customer-order-line {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: start;
            gap: 12px;
            min-width: 0;
        }
        .customer-order-line-main {
            min-width: 0;
        }
        .customer-order-line-track {
            display: flex;
            align-items: flex-start;
            align-content: flex-start;
            flex-wrap: wrap;
            gap: 10px;
            width: 100%;
        }
        .customer-order-line-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: max-content;
            justify-self: end;
            align-self: start;
        }
        .customer-order-line-item {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 12px;
            border-radius: 999px;
            border: 1px solid #dbe4f0;
            background: linear-gradient(180deg, #f8fbff 0%, #f1f5f9 100%);
            color: #334155;
            flex: 0 0 auto;
        }
        .customer-order-line-item--title {
            background: #fff;
            border-color: #cddced;
        }
        .customer-order-inline-label {
            flex: 0 0 auto;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #5b7292;
            font-weight: 800;
        }
        .customer-order-inline-value {
            display: block;
            white-space: nowrap;
            font-size: 14px;
            line-height: 1.3;
            font-weight: 700;
            color: #0f172a;
        }
        .customer-order-inline-value--title {
            font-size: 16px;
            font-weight: 900;
            letter-spacing: -.02em;
        }
        .customer-order-toggle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 12px;
            border-radius: 999px;
            border: 1px solid #cbd5e1;
            background: #fff;
            color: #31537f;
            font-size: 12px;
            font-weight: 800;
            cursor: pointer;
            transition: background .18s ease, border-color .18s ease, color .18s ease;
        }
        .customer-order-toggle:hover {
            background: #eff6ff;
            border-color: #93c5fd;
            color: #1d4ed8;
        }
        .customer-order-modal {
            position: fixed;
            inset: 0;
            z-index: 1200;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background: rgba(15, 23, 42, 0.55);
        }
        .customer-order-modal[hidden] {
            display: none !important;
        }
        .customer-order-modal-card {
            width: min(760px, 100%);
            max-height: calc(100vh - 48px);
            overflow: auto;
            border-radius: 24px;
            border: 1px solid #dbe4f0;
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
            box-shadow: 0 28px 80px rgba(15, 23, 42, 0.24);
            padding: 22px;
        }
        .customer-order-modal-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 18px;
        }
        .customer-order-modal-title {
            margin: 8px 0 0;
            font-size: 28px;
            line-height: 1.05;
            font-weight: 900;
            color: #0f172a;
            letter-spacing: -.03em;
        }
        .customer-order-modal-close {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 999px;
            border: 1px solid #cbd5e1;
            background: #fff;
            color: #334155;
            font-size: 16px;
            cursor: pointer;
        }
        .customer-order-modal-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }
        .customer-order-modal-card-block {
            border: 1px solid #dbe4f0;
            border-radius: 16px;
            padding: 14px 16px;
            background: #fff;
        }
        .customer-order-modal-value {
            margin-top: 8px;
            color: #334155;
            font-size: 14px;
            line-height: 1.6;
            word-break: break-word;
        }
        .customer-order-modal-value + .customer-order-modal-value {
            margin-top: 10px;
        }
        .customer-order-modal-card-block--full {
            grid-column: 1 / -1;
        }
        .customer-empty-state {
            padding: 22px;
            border: 1px dashed #c5d2e3;
            border-radius: 20px;
            background: linear-gradient(180deg, #fbfdff 0%, #f8fafc 100%);
            color: #64748b;
            text-align: center;
            line-height: 1.7;
        }
        @media (max-width: 900px) {
            .customer-order-modal-grid {
                grid-template-columns: 1fr;
            }
        }
        @media (max-width: 640px) {
            .customer-orders-heading {
                align-items: flex-start;
            }
            .customer-order-search {
                width: 100%;
                margin-left: 0;
            }
            .customer-order-search__field {
                width: 100%;
            }
            .customer-orders-shell {
                padding: 16px;
            }
            .customer-summary-panel,
            .customer-order-card {
                padding: 16px;
            }
            .customer-order-modal-grid {
                grid-template-columns: 1fr;
            }
            .customer-order-line {
                grid-template-columns: 1fr;
                align-items: flex-start;
            }
            .customer-order-line-actions {
                justify-self: start;
            }
            .customer-order-modal {
                padding: 12px;
            }
            .customer-order-modal-card {
                padding: 16px;
            }
            .customer-order-modal-title {
                font-size: 22px;
            }
        }
    </style>

    <div class="top-header">
        <div class="customer-orders-heading">
            <h1>My Orders</h1>
            <form method="GET" action="{{ route('customer.orders.index') }}" class="customer-order-search" aria-label="Search orders by funnel">
                <div class="customer-order-search__group">
                    <label for="customerOrderFunnelSearch" class="customer-order-search__label">Search Funnel</label>
                    <span class="customer-order-search__icon" aria-hidden="true">
                        <i class="fas fa-search"></i>
                    </span>
                    <input
                        id="customerOrderFunnelSearch"
                        type="text"
                        name="funnel"
                        value="{{ $funnelSearch ?? '' }}"
                        class="customer-order-search__field"
                        placeholder="Type funnel name..."
                        autocomplete="off"
                    >
                </div>
                @if(!empty($funnelSearch))
                    <a href="{{ route('customer.orders.index') }}" class="customer-order-search__clear">Clear</a>
                @endif
            </form>
        </div>
    </div>

    <div class="customer-orders-shell">
        <div class="customer-order-grid">
            @forelse($orders as $order)
                @php
                    $orderItems = !empty($order['order_items']) && is_array($order['order_items']) ? $order['order_items'] : [];
                    $productSummary = !empty($orderItems)
                        ? collect($orderItems)
                            ->map(function ($item) {
                                $name = trim((string) ($item['name'] ?? 'Product'));
                                $quantity = max(1, (int) ($item['quantity'] ?? 1));
                                $price = trim((string) ($item['price'] ?? ''));

                                return $name . ' x' . $quantity . ($price !== '' ? ' (' . $price . ')' : '');
                            })
                            ->implode(', ')
                        : (string) ($order['order_items_label'] ?? 'No product details recorded.');
                    $deliverySummary = trim((string) ($order['delivery_address'] ?? '')) ?: 'No delivery address recorded.';
                    $trackingSummary = trim((string) ($order['tracking_url'] ?? ''));
                @endphp
                <article class="customer-order-card" data-order-card>
                    <div class="customer-order-line">
                        <div class="customer-order-line-main" data-order-line-main>
                            <div class="customer-order-line-track" data-order-line-track>
                                <span class="customer-order-line-item customer-order-line-item--title" title="{{ $order['funnel_name'] ?? 'Order' }}">
                                    <span class="customer-order-inline-label">Funnel</span>
                                    <span class="customer-order-inline-value customer-order-inline-value--title">{{ $order['funnel_name'] ?? 'Order' }}</span>
                                </span>
                            </div>
                        </div>
                        <div class="customer-order-line-actions">
                            <button type="button" class="customer-order-toggle" data-order-toggle aria-expanded="false">
                                View
                            </button>
                        </div>
                    </div>
                    <div class="customer-order-modal" data-order-details hidden>
                        <div class="customer-order-modal-card" role="dialog" aria-modal="true" aria-label="Order details">
                            <div class="customer-order-modal-header">
                                <div>
                                    <div class="customer-order-label">Funnel</div>
                                    <div class="customer-order-modal-title">{{ $order['funnel_name'] ?? 'Order' }}</div>
                                </div>
                                <button type="button" class="customer-order-modal-close" data-order-close aria-label="Close order details">
                                    <i class="fas fa-times" aria-hidden="true"></i>
                                </button>
                            </div>
                            <div class="customer-order-modal-grid">
                                <div class="customer-order-modal-card-block">
                                    <div class="customer-order-label">Ordered on</div>
                                    <div class="customer-order-modal-value">{{ $order['ordered_at_label'] ?? '-' }}</div>
                                </div>
                                <div class="customer-order-modal-card-block">
                                    <div class="customer-order-label">Amount</div>
                                    <div class="customer-order-modal-value">PHP {{ number_format((float) ($order['checkout_amount'] ?? 0), 2) }}</div>
                                </div>
                                <div class="customer-order-modal-card-block">
                                    <div class="customer-order-label">Payment</div>
                                    <div class="customer-order-modal-value">{{ $statusLabel($order['order_status'] ?? 'paid') }}</div>
                                </div>
                                <div class="customer-order-modal-card-block">
                                    <div class="customer-order-label">Quantity</div>
                                    <div class="customer-order-modal-value">{{ max(1, (int) ($order['order_quantity'] ?? 1)) }}</div>
                                </div>
                                <div class="customer-order-modal-card-block">
                                    <div class="customer-order-label">Delivery status</div>
                                    <div class="customer-order-modal-value">
                                        <span class="customer-status-pill" style="{{ $statusStyle($order['delivery_status'] ?? 'paid') }}">
                                            {{ $statusLabel($order['delivery_status'] ?? 'paid') }}
                                        </span>
                                    </div>
                                </div>
                                @if(!empty($order['delivery_updated_label']))
                                    <div class="customer-order-modal-card-block">
                                        <div class="customer-order-label">Updated</div>
                                        <div class="customer-order-modal-value">{{ $order['delivery_updated_label'] }}</div>
                                    </div>
                                @endif
                                <div class="customer-order-modal-card-block customer-order-modal-card-block--full">
                                    <div class="customer-order-label">Products</div>
                                    <div class="customer-order-modal-value">{{ $productSummary }}</div>
                                </div>
                                <div class="customer-order-modal-card-block customer-order-modal-card-block--full">
                                    <div class="customer-order-label">Delivery details</div>
                                    <div class="customer-order-modal-value">{{ $deliverySummary }}</div>
                                    @if($trackingSummary !== '')
                                        <div class="customer-order-modal-value">
                                            <strong>Tracking:</strong> {{ $trackingSummary }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </article>
            @empty
                <div class="customer-empty-state">
                    No paid funnel orders are linked to this customer email yet.
                </div>
            @endforelse
        </div>

        <div style="margin-top: 16px;">
            {{ $orders->links('pagination::bootstrap-4') }}
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var orderCards = document.querySelectorAll('[data-order-card]');
            var searchForm = document.querySelector('.customer-order-search');
            var searchField = document.getElementById('customerOrderFunnelSearch');
            var searchTimer = null;

            function closeModal(card) {
                if (!card) {
                    return;
                }

                var details = card.querySelector('[data-order-details]');
                var toggle = card.querySelector('[data-order-toggle]');

                if (!details || !toggle) {
                    return;
                }

                details.hidden = true;
                toggle.textContent = 'View';
                toggle.setAttribute('aria-expanded', 'false');
                document.body.style.overflow = '';
            }

            function openModal(card) {
                if (!card) {
                    return;
                }

                var details = card.querySelector('[data-order-details]');
                var toggle = card.querySelector('[data-order-toggle]');

                if (!details || !toggle) {
                    return;
                }

                details.hidden = false;
                toggle.textContent = 'View';
                toggle.setAttribute('aria-expanded', 'true');
                document.body.style.overflow = 'hidden';
            }

            orderCards.forEach(function (card) {
                var toggle = card.querySelector('[data-order-toggle]');
                var details = card.querySelector('[data-order-details]');

                if (!toggle || !details) {
                    return;
                }

                toggle.textContent = 'View';
                toggle.setAttribute('aria-expanded', 'false');
                details.hidden = true;

                toggle.addEventListener('click', function () {
                    openModal(card);
                });

                details.addEventListener('click', function (event) {
                    if (event.target === details || event.target.closest('[data-order-close]')) {
                        closeModal(card);
                    }
                });
            });

            document.addEventListener('keydown', function (event) {
                if (event.key !== 'Escape') {
                    return;
                }

                orderCards.forEach(function (card) {
                    var details = card.querySelector('[data-order-details]');

                    if (!details || details.hidden) {
                        return;
                    }

                    closeModal(card);
                });
            });

            if (searchForm && searchField) {
                searchField.addEventListener('input', function () {
                    window.clearTimeout(searchTimer);
                    searchTimer = window.setTimeout(function () {
                        searchForm.requestSubmit();
                    }, 250);
                });
            }
        });
    </script>
@endsection
