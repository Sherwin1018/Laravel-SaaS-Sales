<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function show()
    {
        $user = auth()->user()->load('roles', 'tenant');

        return view('profile.show', compact('user'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => ['nullable', 'regex:/^09\d{9}$/'],
            'secondary_phone' => ['nullable', 'regex:/^09\d{9}$/'],
            'remove_secondary_phone' => 'nullable|boolean',
        ], [
            'phone.regex' => 'Phone number must be a valid Philippine mobile number (09XXXXXXXXX).',
            'secondary_phone.regex' => 'Secondary phone must be a valid Philippine mobile number (09XXXXXXXXX).',
        ]);

        $secondaryPhone = $validated['secondary_phone'] ?? null;
        if (!empty($validated['remove_secondary_phone'])) {
            $secondaryPhone = null;
        }

        $user->update([
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?? null,
            'secondary_phone' => $secondaryPhone,
        ]);

        return redirect()->route('profile.show')->with('success', 'Edited Successfully');
    }

    public function updatePassword(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'old_password' => 'required|string',
            'new_password' => [
                'required',
                'string',
                'min:12',
                'max:14',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[^A-Za-z0-9]/',
                'confirmed',
            ],
        ], [
            'new_password.regex' => 'Password must contain uppercase, lowercase, number, and a special character.',
        ]);

        if (!Hash::check($validated['old_password'], $user->password)) {
            return redirect()->route('profile.show')->with('error', 'Edited Failed. Old password is incorrect.');
        }

        $user->update([
            'password' => Hash::make($validated['new_password']),
        ]);

        return redirect()->route('profile.show')->with('success', 'Edited Successfully');
    }

    public function updateAvatar(Request $request)
    {
        $request->validate([
            'profile_photo' => 'required|image|max:2048',
        ]);

        $user = auth()->user();

        if ($user->profile_photo_path) {
            Storage::disk('public')->delete($user->profile_photo_path);
        }

        $path = $request->file('profile_photo')->store('profile-photos', 'public');
        $user->update(['profile_photo_path' => $path]);

        return redirect()->route('profile.show')->with('success', 'Edited Successfully');
    }

    public function deleteAvatar()
    {
        $user = auth()->user();

        if ($user->profile_photo_path) {
            Storage::disk('public')->delete($user->profile_photo_path);
        }

        $user->update(['profile_photo_path' => null]);

        return redirect()->route('profile.show')->with('success', 'Deleted Successfully');
    }

    public function updateCompanyLogo(Request $request)
    {
        $user = auth()->user()->load('tenant');

        if (!$user->hasRole('account-owner') || !$user->tenant) {
            return redirect()->route('profile.show')->with('error', 'Edited Failed');
        }

        $request->validate([
            'company_logo' => 'required|image|max:2048',
        ]);

        if ($user->tenant->logo_path) {
            Storage::disk('public')->delete($user->tenant->logo_path);
        }

        $path = $request->file('company_logo')->store('company-logos', 'public');
        $user->tenant->update(['logo_path' => $path]);

        return redirect()->route('profile.show')->with('success', 'Edited Successfully');
    }

    public function deleteCompanyLogo()
    {
        $user = auth()->user()->load('tenant');

        if (!$user->hasRole('account-owner') || !$user->tenant) {
            return redirect()->route('profile.show')->with('error', 'Deleted Failed');
        }

        if ($user->tenant->logo_path) {
            Storage::disk('public')->delete($user->tenant->logo_path);
        }

        $user->tenant->update(['logo_path' => null]);

        return redirect()->route('profile.show')->with('success', 'Deleted Successfully');
    }
}
