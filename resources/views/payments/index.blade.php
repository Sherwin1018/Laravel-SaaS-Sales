@extends('layouts.admin')

@section('title', 'Payment Tracking')

@section('content')
    <div class="top-header">
        <h1>Payment Tracking</h1>
    </div>

    @if(session('success'))
        <div style="background-color: #d1fae5; color: #065f46; padding: 10px; border-radius: 6px; margin-bottom: 20px;">
            {{ session('success') }}
        </div>
    @endif

    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 20px;">
        <div class="card">
            <h3>Record Payment</h3>
            <form action="{{ route('payments.store') }}" method="POST">
                @csrf

                <div style="margin-bottom: 12px;">
                    <label for="lead_id" style="display: block; margin-bottom: 6px;">Lead</label>
                    <select name="lead_id" id="lead_id" style="width: 100%; padding: 10px; border: 1px solid #DBEAFE; border-radius: 6px;">
                        <option value="">No lead linked</option>
                        @foreach($leadOptions as $lead)
                            <option value="{{ $lead->id }}">{{ $lead->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="margin-bottom: 12px;">
                    <label for="amount" style="display: block; margin-bottom: 6px;">Amount</label>
                    <input type="number" step="0.01" min="0.01" name="amount" id="amount" required
                        style="width: 100%; padding: 10px; border: 1px solid #DBEAFE; border-radius: 6px;">
                </div>

                <div style="margin-bottom: 12px;">
                    <label for="status" style="display: block; margin-bottom: 6px;">Status</label>
                    <select name="status" id="status" required style="width: 100%; padding: 10px; border: 1px solid #DBEAFE; border-radius: 6px;">
                        <option value="pending">Pending</option>
                        <option value="paid">Paid</option>
                        <option value="failed">Failed</option>
                    </select>
                </div>

                <div style="margin-bottom: 12px;">
                    <label for="payment_date" style="display: block; margin-bottom: 6px;">Date</label>
                    <input type="date" name="payment_date" id="payment_date" required
                        value="{{ now()->toDateString() }}"
                        style="width: 100%; padding: 10px; border: 1px solid #DBEAFE; border-radius: 6px;">
                </div>

                <button type="submit"
                    style="padding: 10px 18px; background-color: #2563EB; color: white; border: none; border-radius: 6px; cursor: pointer;">
                    Save Payment
                </button>
            </form>
        </div>

        <div class="card">
            <h3>Recent Payments</h3>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Lead</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                        <tr>
                            <td>{{ $payment->payment_date->format('Y-m-d') }}</td>
                            <td>{{ $payment->lead->name ?? 'N/A' }}</td>
                            <td>${{ number_format($payment->amount, 2) }}</td>
                            <td>{{ ucfirst($payment->status) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">No payment records yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div style="margin-top: 14px;">
                {{ $payments->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
@endsection
