<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class TenantController extends Controller
{
    public function index(Request $request)
    {
        $query = Tenant::latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('company_name', 'like', "%{$search}%")
                  ->orWhere('subscription_plan', 'like', "%{$search}%")
                  ->orWhere('status', 'like', "%{$search}%");
            });
        }

        $tenants = $query->paginate(10); 

        if ($request->ajax()) {
            return view('admin.tenants._rows', compact('tenants'))->render();
        }

        return view('admin.tenants.index', compact('tenants'));
    }

    public function create()
    {
        return view('admin.tenants.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'subscription_plan' => 'required|string|max:255',
            'status' => 'required|in:active,inactive,trial',
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|max:255|unique:users,email',
            'admin_password' => [
                'required',
                'string',
                'min:12',
                'max:64',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[^A-Za-z0-9]/',
                'confirmed',
            ],
        ], [
            'admin_password.regex' => 'Password must contain uppercase, lowercase, number, and a special character.',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                // 1. Create Tenant
                $tenant = Tenant::create([
                    'company_name' => $validated['company_name'],
                    'subscription_plan' => $validated['subscription_plan'],
                    'status' => $validated['status'],
                ]);

                // 2. Create Admin User (pre-verified: Super Admin creates trusted Account Owner)
                $user = User::create([
                    'tenant_id' => $tenant->id,
                    'name' => $validated['admin_name'],
                    'email' => $validated['admin_email'],
                    'password' => Hash::make($validated['admin_password']),
                    'role' => 'account-owner', // consistent with role slug
                    'status' => 'active',
                ]);
                $user->forceFill(['email_verified_at' => now()])->save();

                // 3. Attach Role (if using pivot table)
                // Ensure Role model has 'account-owner' slug from seeder
                $role = Role::where('slug', 'account-owner')->first();
                if ($role) {
                    $user->roles()->attach($role);
                }
            });

            return redirect()->route('admin.tenants.index')
                ->with('success', 'Added Successfully');
        } catch (\Throwable $e) {
            report($e);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Added Failed: '.$e->getMessage());
        }
    }

    public function edit(Tenant $tenant)
    {
        return view('admin.tenants.edit', compact('tenant'));
    }

    public function update(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'subscription_plan' => 'required|string|max:255',
            'status' => 'required|in:active,inactive,trial',
        ]);

        try {
            $tenant->update($validated);

            return redirect()->route('admin.tenants.index')
                ->with('success', 'Edited Successfully');
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', 'Edited Failed');
        }
    }

    public function destroy(Tenant $tenant)
    {
        try {
            $tenant->delete();

            return redirect()->route('admin.tenants.index')
                ->with('success', 'Deleted Successfully');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Deleted Failed');
        }
    }
}
