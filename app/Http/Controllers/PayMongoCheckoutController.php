<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class PayMongoCheckoutController extends Controller
{
    public function return(string $payment, Request $request): RedirectResponse
    {
        // Handle PayMongo return callback
        // For now, redirect to dashboard
        return redirect()->to(route('dashboard.owner'));
    }
}
