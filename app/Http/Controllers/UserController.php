<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Services\SetupTokenService;
use App\Services\SignupOnboardingService;
use App\Support\TenantPlanEnforcer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function adminIndex(Request $request)
    {
        $query = User::with(['tenant', 'roles']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhereHas('roles', function ($roleQuery) use ($search) {
                        $roleQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('slug', 'like', "%{$search}%");
                    })
                    ->orWhereHas('tenant', function ($tenantQuery) use ($search) {
                        $tenantQuery->where('company_name', 'like', "%{$search}%");
                    });
            });
        }

        $users = $query->latest()->paginate(10);

        if ($request->ajax()) {
            return view('admin.users._rows', compact('users'))->render();
        }

        return view('admin.users.index', compact('users'));
    }

    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $query = User::where('tenant_id', $tenantId)->with('roles');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
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

    public function create()
    {
        try {
            app(TenantPlanEnforcer::class)->ensureCanCreateUser(auth()->user()->tenant);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            return redirect()->route('users.index')->with('error', $e->getMessage());
        }

        $roles = Role::whereIn('slug', ['marketing-manager', 'sales-agent', 'finance', 'customer'])->get();

        return view('users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'phone' => 'nullable|string|max:32',
            'role' => 'required|exists:roles,slug',
        ]);

        try {
            app(TenantPlanEnforcer::class)->ensureCanCreateUser(auth()->user()->tenant);

            $role = Role::where('slug', $request->role)->first();
            if (! $role) {
                return redirect()->back()->withInput()->with('error', 'Invalid role. Please refresh the page and try again.');
            }

            $user = User::create([
                'tenant_id' => auth()->user()->tenant_id,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Str::random(40),
                'role' => $request->role,
                'status' => 'inactive',
                'activation_state' => 'invited',
                'invited_by' => auth()->id(),
                'invited_at' => now(),
                'is_customer_portal_user' => $request->role === 'customer',
            ]);

            $user->roles()->syncWithoutDetaching([$role->id]);

            $eventName = $request->role === 'customer'
                ? 'customer_portal_invited'
                : 'team_member_invited';

            app(SignupOnboardingService::class)->queueSetupEmail(
                $user,
                $eventName,
                app(SetupTokenService::class),
            );

            return redirect()->route('users.index')->with('success', 'Invitation sent successfully.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            report($e);

            return redirect()->back()->withInput()->with('error', 'Invitation could not be sent.');
        }
    }

    public function resendVerification(User $user)
    {
        if ($user->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        if ($user->activation_state === 'active') {
            return redirect()->back()->with('success', 'User is already active.');
        }

        $eventName = $user->hasRole('customer')
            ? 'customer_portal_invited'
            : 'team_member_invited';

        $sent = app(SignupOnboardingService::class)->queueSetupEmail(
            $user,
            $eventName,
            app(SetupTokenService::class),
        );

        return redirect()->back()->with(
            $sent ? 'success' : 'error',
            $sent ? 'Invitation resent successfully.' : 'Invitation could not be resent.'
        );
    }

    public function destroy(User $user)
    {
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
        if (! $user->hasRole('account-owner')) {
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
