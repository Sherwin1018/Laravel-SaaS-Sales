<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Tenant;
use App\Models\User;

class AdminController extends Controller
{
    public function index()
    {
        $tenantCount = Tenant::count();
        $activeTenantCount = Tenant::where('status', 'active')->count();
        $userCount = User::count();
        $leadCount = Lead::withoutGlobalScope('tenant')->count();

        $mrr = 25600;

        return view('admin.dashboard', compact(
            'tenantCount',
            'activeTenantCount',
            'userCount',
            'leadCount',
            'mrr'
        ));
    }
}
