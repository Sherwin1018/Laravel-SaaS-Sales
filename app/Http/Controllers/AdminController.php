<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Lead;

class AdminController extends Controller
{
    public function index()
    {
        $tenantCount = Tenant::count();
        $userCount = User::count();
        $leadCount = Lead::withoutGlobalScope('tenant')->count();

        // Hardcoded for now as per original view, or can be calculated if data exists
        $mrr = 25600; 

        return view('admin.dashboard', compact('tenantCount', 'userCount', 'leadCount', 'mrr'));
    }
}
