@extends('layouts.admin')

@section('title', 'Customer Dashboard')

@section('content')
    <style>
        .customer-home-shell {
            display: grid;
            gap: 18px;
        }
        .customer-home-summary-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
        }
        .customer-home-stat {
            border: 1px solid #dbe4f0;
            border-radius: 20px;
            background: #fff;
            padding: 18px;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
        }
        .customer-home-stat__label {
            font-size: 11px;
            color: #64748b;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
        }
        .customer-home-stat__value {
            margin-top: 8px;
            font-size: clamp(24px, 2.1vw, 32px);
            line-height: 1.05;
            font-weight: 900;
            color: #0f172a;
            letter-spacing: -.03em;
        }
        .customer-home-stat__meta {
            margin-top: 8px;
            color: #64748b;
            font-size: 13px;
            line-height: 1.5;
        }
        .customer-home-cards {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }
        .customer-home-card {
            border: 1px solid #dbe4f0;
            border-radius: 22px;
            background: #fff;
            padding: 20px;
            box-shadow: 0 14px 30px rgba(15, 23, 42, 0.04);
        }
        .customer-home-card__eyebrow {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #5b7292;
            font-weight: 800;
        }
        .customer-home-card__title {
            margin: 10px 0 8px;
            font-size: 22px;
            line-height: 1.15;
            font-weight: 900;
            color: #0f172a;
            letter-spacing: -.03em;
        }
        .customer-home-card__copy {
            margin: 0;
            color: #475569;
            font-size: 14px;
            line-height: 1.7;
        }
        .customer-home-card__actions {
            margin-top: 16px;
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: center;
        }
        .customer-home-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 11px 16px;
            border-radius: 999px;
            background: #0f172a;
            color: #fff;
            text-decoration: none;
            font-size: 14px;
            font-weight: 800;
        }
        .customer-home-inline-note {
            color: #64748b;
            font-size: 13px;
            line-height: 1.5;
        }
        @media (max-width: 900px) {
            .customer-home-summary-grid,
            .customer-home-cards {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        @media (max-width: 640px) {
            .customer-home-summary-grid {
                grid-template-columns: 1fr;
            }
            .customer-home-card {
                padding: 16px;
            }
        }
    </style>

    <div class="top-header">
        <h1>Welcome, {{ auth()->user()->name }}</h1>
    </div>

    <div class="customer-home-shell">
        <div class="customer-home-summary-grid">
            <section class="customer-home-stat">
                <div class="customer-home-stat__label">Paid orders</div>
                <div class="customer-home-stat__value">{{ $orderSummary['total_orders'] ?? 0 }}</div>
                <div class="customer-home-stat__meta">Completed funnel orders linked to your email.</div>
            </section>
            <section class="customer-home-stat">
                <div class="customer-home-stat__label">Total spent</div>
                <div class="customer-home-stat__value">PHP {{ number_format((float) ($orderSummary['total_spent'] ?? 0), 2) }}</div>
                <div class="customer-home-stat__meta">
                    @if(!empty($orderSummary['last_ordered_at']))
                        Last confirmed order: {{ $orderSummary['last_ordered_at'] }}
                    @else
                        Your completed purchases will appear here once you place an order.
                    @endif
                </div>
            </section>
            <section class="customer-home-stat">
                <div class="customer-home-stat__label">Active shipments</div>
                <div class="customer-home-stat__value">{{ $orderSummary['active_shipments'] ?? 0 }}</div>
                <div class="customer-home-stat__meta">Orders still processing, shipped, or out for delivery.</div>
            </section>
            <section class="customer-home-stat">
                <div class="customer-home-stat__label">Portal role</div>
                <div class="customer-home-stat__value">Customer</div>
                <div class="customer-home-stat__meta">Access is based on purchases linked to this email, not a system subscription.</div>
            </section>
        </div>

        <div class="customer-home-cards">
            <section class="customer-home-card">
                <div class="customer-home-card__eyebrow">Orders</div>
                <h3 class="customer-home-card__title">Open your order history</h3>
                <div class="customer-home-card__actions">
                    <a href="{{ route('customer.orders.index') }}" class="customer-home-button">
                        <i class="fas fa-bag-shopping" aria-hidden="true"></i>
                        <span>Go to Orders</span>
                    </a>
                    <span class="customer-home-inline-note">This is where the detailed order cards now live.</span>
                </div>
            </section>

            <section class="customer-home-card">
                <div class="customer-home-card__eyebrow">Shipping</div>
                <h3 class="customer-home-card__title">{{ $orderSummary['active_shipments'] ?? 0 }} active shipment{{ ($orderSummary['active_shipments'] ?? 0) == 1 ? '' : 's' }}</h3>
                <div class="customer-home-card__actions">
                    <a href="{{ route('customer.orders.index') }}" class="customer-home-button">
                        <i class="fas fa-truck" aria-hidden="true"></i>
                        <span>Check delivery updates</span>
                    </a>
                </div>
            </section>
        </div>
    </div>
@endsection
