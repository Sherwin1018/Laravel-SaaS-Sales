<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Support\TenantPlanEnforcer;
use Illuminate\Support\Facades\Hash;
use Throwable;

class UserController extends Controller
{
    /**
     * Display a list of ALL users for Super Admin.
     */
    public function adminIndex(Request $request)
    {
        $query = User::with(['tenant', 'roles']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('status', 'like', "%{$search}%")
                  ->orWhereHas('roles', function ($roleQuery) use ($search) {
                      $roleQuery->where('name', 'like', "%{$search}%")
                          ->orWhere('slug', 'like', "%{$search}%");
                  })
                  ->orWhereHas('tenant', function($tq) use ($search) {
                      $tq->where('company_name', 'like', "%{$search}%");
                  });
            });
        }

        $users = $query->latest()->paginate(10);

        if ($request->ajax()) {
            return view('admin.users._rows', compact('users'))->render();
        }

        return view('admin.users.index', compact('users'));
    }

    /**
     * Display a list of users for the current tenant.
     */
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $query = User::where('tenant_id', $tenantId)->with('roles');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('roles', function ($roleQuery) use ($search) {
                      $roleQuery->where('name', 'like', "%{$search}%")
                          ->orWhere('slug', 'like', "%{$search}%");
                  });
            });
        }

        $users = $query->latest()->paginate(10);
        $planUsage = app(TenantPlanEnforcer::class)->usageSummary(auth()->user()->tenant);

        if ($request->ajax()) {
            return view('users._rows', compact('users'))->render();
        }

        return view('users.index', compact('users', 'planUsage'));
    }

    /**
     * Show form to create a new user.
     */
    public function create()
    {
        try {
            app(TenantPlanEnforcer::class)->ensureCanCreateUser(auth()->user()->tenant);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            return redirect()->route('users.index')->with('error', $e->getMessage());
        }

        // Get roles assignable by account owner
        $roles = Role::whereIn('slug', ['marketing-manager', 'sales-agent', 'finance', 'customer'])->get();

        return view('users.create', compact('roles'));
    }

    /**
     * Store a new user.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => [
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
            'role' => 'required|exists:roles,slug',
        ], [
            'password.regex' => 'Password must contain uppercase, lowercase, number, and a special character.',
        ]); 
        try {
            $tenantId = auth()->user()->tenant_id;
            app(TenantPlanEnforcer::class)->ensureCanCreateUser(auth()->user()->tenant);

            $role = Role::where('slug', $request->role)->first();
            if (!$role) {
                return redirect()->back()->withInput()->with('error', 'Invalid role. Please refresh the page and try again.');
            }

            // Create User
            $user = User::create([
                'tenant_id' => $tenantId,
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role, // Store slug directly (e.g. 'marketing-manager')
                'status' => 'active',
            ]);

            $user->roles()->attach($role);

            return redirect()->route('users.index')->with('success', 'Added Successfully');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            report($e);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to send verification email. Please check your mail configuration and try again.'
            ]);
        }
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user)
    {
        // Policy: can only delete users from own tenant, and not self
        if ($user->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'Deleted Failed. You cannot delete yourself.');
        }

        try {
            $user->delete();
            return redirect()->back()->with('success', 'Deleted Successfully');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Deleted Failed');
        }
    }

    public function toggleOwnerStatus(Request $request, User $user)
    {
        if (!$user->hasRole('account-owner')) {
            return redirect()->back()->with('error', 'Edited Failed. Only Account Owner accounts can be updated.');
        }

        if ($user->status === 'active') {
            $validated = $request->validate([
                'suspension_reason' => 'required|string|max:255',
            ]);

            $user->update([
                'status' => 'inactive',
                'suspension_reason' => $validated['suspension_reason'],
            ]);
        } else {
            $user->update([
                'status' => 'active',
                'suspension_reason' => null,
            ]);
        }

        return redirect()->back()->with('success', 'Edited Successfully');
    }
}
