<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Show the login form
    public function showLoginForm()
    {
        if (Auth::check()) {
            return $this->redirectByRole(auth()->user());
        }

        return view('auth.login');
    }

    // Handle login
    public function login(Request $request)
    {
        // Validate input
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, true)) {
            $request->session()->regenerate();

            $user = auth()->user();
            if ($user->status !== 'active') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                $reason = $user->suspension_reason ?: 'No reason provided.';
                $message = "Login Failed. Your account has been temporarily suspended. Please contact support or your system administrator for assistance. Reason: {$reason} Support: nehemiah.solutions.corp@gmail.com";

                return redirect()->route('login')->with('error', $message);
            }

            $user->last_login_at = now();
            $user->save();

            return $this->redirectByRole($user);
        }

        return back()->with('error', 'Login Failed. Invalid email or password.');
    }

    // Logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login')->with('success', 'Logout Successfully');
    }

    private function redirectByRole($user)
    {
        if ($user->hasRole('super-admin')) {
            return redirect()->intended('/admin/dashboard')->with('success', 'Login Successfully');
        }

        if ($user->hasRole('account-owner')) {
            return redirect()->intended(route('dashboard.owner'))->with('success', 'Login Successfully');
        }

        if ($user->hasRole('marketing-manager')) {
            return redirect()->intended(route('dashboard.marketing'))->with('success', 'Login Successfully');
        }

        if ($user->hasRole('sales-agent')) {
            return redirect()->intended(route('dashboard.sales'))->with('success', 'Login Successfully');
        }

        if ($user->hasRole('finance')) {
            return redirect()->intended(route('dashboard.finance'))->with('success', 'Login Successfully');
        }

        if ($user->hasRole('customer')) {
            return redirect()->intended(route('dashboard.customer'))->with('success', 'Login Successfully');
        }

        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('login')->with('error', 'Login Failed. Your role does not have access.');
    }
}
