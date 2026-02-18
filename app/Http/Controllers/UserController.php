<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

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
                  ->orWhereHas('tenant', function($tq) use ($search) {
                      $tq->where('company_name', 'like', "%{$search}%");
                  });
            });
        }

        $users = $query->latest()->paginate(15);

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
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(10);

        if ($request->ajax()) {
            return view('users._rows', compact('users'))->render();
        }

        return view('users.index', compact('users'));
    }

    /**
     * Show form to create a new user.
     */
    public function create()
    {
        // Get roles assignable by account owner: 'marketing-manager', 'sales-agent', 'finance'
        $roles = Role::whereIn('slug', ['marketing-manager', 'sales-agent', 'finance'])->get();

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
            'password' => 'required|string|min:8',
            'role' => 'required|exists:roles,slug',
        ]);

        $tenantId = auth()->user()->tenant_id;

        // Create User
        $user = User::create([
            'tenant_id' => $tenantId,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role, // Store slug directly (e.g. 'marketing-manager')
        ]);

        // Attach Role
        $role = Role::where('slug', $request->role)->first();
        $user->roles()->attach($role);

        return redirect()->route('users.index')->with('success', 'User added successfully.');
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
            return redirect()->back()->with('error', 'You cannot delete yourself.');
        }

        $user->delete();

        return redirect()->back()->with('success', 'User deleted successfully.');
    }
}
