<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailVerificationController extends Controller
{
    /**
     * Display the email verification notice.
     */
    public function notice(Request $request): View|RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended('/');
        }

        return view('auth.verify-email');
    }

    /**
     * Verify the user's email address (called when user clicks the link in the email).
     */
    public function verify(Request $request): RedirectResponse
    {
        if (!$request->hasValidSignature()) {
            return redirect()->route('verification.notice')->with('error', 'This verification link is invalid or expired.');
        }

        // Find the user from the URL parameter, not the currently logged-in user
        $user = \App\Models\User::find($request->route('id'));
        
        if (!$user) {
            return redirect()->route('verification.notice')->with('error', 'User not found.');
        }

        if (!hash_equals((string) $request->route('hash'), sha1($user->getEmailForVerification()))) {
            return redirect()->route('verification.notice')->with('error', 'This verification link is invalid.');
        }

        // If user is not logged in, log them in first
        if (!auth()->check() || auth()->user()->id !== $user->id) {
            auth()->login($user);
        }

        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        return redirect()->intended('/')->with('success', 'Email verified successfully.');
    }

    /**
     * Resend the email verification notification.
     */
    public function resend(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended('/');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('success', 'Verification link sent! Please check your email.');
    }
}
