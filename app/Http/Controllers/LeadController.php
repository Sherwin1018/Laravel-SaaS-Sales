<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lead;

class LeadController extends Controller
{
    /**
     * Display all leads for Super Admin (across all tenants).
     */
    public function adminIndex(Request $request)
    {
        $query = Lead::withoutGlobalScope('tenant')->with('tenant');

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('status', 'like', "%{$search}%")
                  ->orWhereHas('tenant', function($tenantQuery) use ($search) {
                      $tenantQuery->where('company_name', 'like', "%{$search}%");
                  });
            });
        }

        $leads = $query->latest()->paginate(15);

        // Append search parameter to pagination links
        if ($request->has('search')) {
            $leads->appends(['search' => $request->search]);
        }

        // If AJAX request, return JSON for live search
        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.leads.partials.table', compact('leads'))->render(),
                'pagination' => $leads->links('pagination::bootstrap-4')->toHtml()
            ]);
        }

        return view('admin.leads.index', compact('leads'));
    }

    /**
     * Display all leads for the logged-in user's tenant
     */
    public function index()
    {
        $user = auth()->user();

        // Always filter by tenant
        $query = Lead::where('tenant_id', $user->tenant_id);

        // Example: if Sales Agent, limit to first 5 leads (optional)
        if ($user->hasRole('sales-agent')) {
            $query->take(5);
        }

        $leads = $query->get();

        return view('leads.index', compact('leads'));
    }

    /**
     * Show the form for creating a new lead.
     */
    public function create()
    {
        return view('leads.create');
    }

    /**
     * Store a new lead
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:150',
            'email' => 'required|email|max:150',
            'phone' => 'nullable|string|max:50',
            'status' => 'required|string',
        ]);

        Lead::create([
            'tenant_id' => auth()->user()->tenant_id, // 🔥 always include tenant_id
            'name'      => $request->name,
            'email'     => $request->email,
            'phone'     => $request->phone,
            'status'    => $request->status,
            'score'     => 0,
        ]);

        return redirect()->route('leads.index')->with('success', 'Lead created successfully.');
    }

    /**
     * Show the form for editing the specified lead.
     */
    public function edit(Lead $lead)
    {
        // Policy check (ensure tenant ownership)
        if ($lead->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        return view('leads.edit', compact('lead'));
    }

    /**
     * Update the specified lead.
     */
    public function update(Request $request, Lead $lead)
    {
        if ($lead->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $validated = $request->validate([
            'name'  => 'required|string|max:150',
            'email' => 'required|email|max:150',
            'phone' => 'nullable|string|max:50',
            'status' => 'required|string',
            'score'  => 'nullable|integer',
        ]);

        $lead->update($validated);

        return redirect()->route('leads.index')->with('success', 'Lead updated successfully.');
    }

    /**
     * Delete a lead
     */
    public function destroy(Lead $lead)
    {
        // 🔥 Protect: only allow deletion if lead belongs to user's tenant
        if ($lead->tenant_id !== auth()->user()->tenant_id) {
            abort(403, 'Unauthorized action.');
        }

        $lead->delete();

        return redirect()->back()->with('success', 'Lead deleted successfully.');
    }

    /**
     * Store a new activity (note) for a lead
     */
    public function storeActivity(Request $request, Lead $lead)
    {
        if ($lead->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $request->validate([
            'notes' => 'required|string',
            'activity_type' => 'required|string',
        ]);

        $lead->activities()->create([
            'activity_type' => $request->activity_type,
            'notes' => $request->notes,
        ]);

        return redirect()->back()->with('success', 'Activity added.');
    }
}
