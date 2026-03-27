<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
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

            // Mail misconfiguration must not block team creation
            try {
                $user->sendEmailVerificationNotification();
            } catch (Throwable $e) {
                report($e);

                return redirect()->route('users.index')->with(
                    'warning',
                    'User was added, but the verification email could not be sent. Fix MAIL_* in .env, then run php artisan config:clear. After mail works, they can log in and use “Resend verification email”.'
                );
            }

            return redirect()->route('users.index')->with('success', 'User added. They must verify their email before accessing the platform.');
        } catch (Throwable $e) {
            report($e);

            $message = config('app.debug')
                ? 'Could not add user: '.$e->getMessage()
                : 'Could not add user. Please try again or contact support.';

            return redirect()->back()->withInput()->with('error', $message);
        }
    }

    /**
     * Resend verification email to user.
     */
    public function resendVerification(User $user)
    {
        // Policy: can only resend to users from own tenant
        if ($user->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        // Check if user is already verified
        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'success' => false,
                'message' => 'User has already verified their email address.'
            ]);
        }

        try {
            $user->sendEmailVerificationNotification();
            
            return response()->json([
                'success' => true,
                'message' => 'Verification email sent successfully to ' . $user->email . '.'
            ]);
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
