<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Payment::with('lead')
            ->where('tenant_id', $user->tenant_id)
            ->latest('payment_date');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $payments = $query->paginate(12);
        $leadOptions = Lead::where('tenant_id', $user->tenant_id)->orderBy('name')->get(['id', 'name']);

        return view('payments.index', compact('payments', 'leadOptions'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'lead_id' => 'nullable|integer|exists:leads,id',
            'amount' => 'required|numeric|min:0.01',
            'status' => 'required|in:pending,paid,failed',
            'payment_date' => 'required|date',
        ]);

        if (!empty($validated['lead_id'])) {
            $belongsToTenant = Lead::where('id', $validated['lead_id'])
                ->where('tenant_id', $user->tenant_id)
                ->exists();

            if (!$belongsToTenant) {
                abort(422, 'Selected lead is invalid.');
            }
        }

        Payment::create([
            'tenant_id' => $user->tenant_id,
            'lead_id' => $validated['lead_id'] ?? null,
            'amount' => $validated['amount'],
            'status' => $validated['status'],
            'payment_date' => $validated['payment_date'],
        ]);

        return redirect()->route('payments.index')->with('success', 'Payment recorded successfully.');
    }
}
