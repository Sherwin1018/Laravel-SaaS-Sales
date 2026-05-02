<?php

namespace App\Http\Controllers;

use App\Models\TenantPayoutAccount;
use App\Services\FinanceAuditService;
use App\Services\N8nEmailOrchestrator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function show()
    {
        $user = auth()->user()->load('roles', 'tenant.defaultPayoutAccount');

        return view('profile.show', compact('user'));
    }

    public function showPayoutSetup()
    {
        $user = auth()->user()->load('tenant.defaultPayoutAccount');

        if (! $user->hasRole('account-owner') || ! $user->tenant) {
            return redirect()->route('profile.show')->with('error', 'Edited Failed');
        }

        return view('payouts.setup', [
            'user' => $user,
            'payoutAccount' => $user->tenant->defaultPayoutAccount,
        ]);
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
                'max:64',
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

    public function updateTheme(Request $request)
    {
        $user = auth()->user()->load('tenant');

        if (!$user->hasRole('account-owner') || !$user->tenant) {
            return redirect()->route('profile.show')->with('error', 'Edited Failed');
        }

        $validated = $request->validate([
            'theme_primary_color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'theme_accent_color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'theme_sidebar_bg' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'theme_sidebar_text' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        $user->tenant->update($validated);

        return redirect()->route('profile.show')->with('success', 'Edited Successfully');
    }

    public function updatePayoutAccount(Request $request)
    {
        $user = auth()->user()->load('tenant');

        if (! $user->hasRole('account-owner') || ! $user->tenant) {
            return redirect()->route('profile.show')->with('error', 'Edited Failed');
        }

        $validated = $request->validate([
            'destination_type' => 'required|in:gcash,bank_transfer',
            'account_name' => 'required|string|max:160',
            'destination_value' => 'nullable|string|max:160',
            'provider_destination_reference' => 'nullable|string|max:160',
            'notes' => 'nullable|string|max:500',
            'gcash_qr' => 'nullable|file|mimes:jpg,jpeg,png|max:4096',
        ]);

        $result = DB::transaction(function () use ($request, $user, $validated) {
            $existingAccounts = TenantPayoutAccount::query()
                ->where('tenant_id', $user->tenant->id)
                ->orderByDesc('is_default')
                ->orderByDesc('updated_at')
                ->orderByDesc('id')
                ->lockForUpdate()
                ->get();

            $existing = $existingAccounts->first();
            $deduplicatedCount = max(0, $existingAccounts->count() - 1);

            if ($deduplicatedCount > 0) {
                TenantPayoutAccount::query()
                    ->whereIn('id', $existingAccounts->skip(1)->pluck('id'))
                    ->delete();
            }

            $destinationValue = trim((string) ($validated['destination_value'] ?? ''));
            $providerReference = trim((string) ($validated['provider_destination_reference'] ?? ''));
            $resolvedDestinationValue = $destinationValue !== '' ? $destinationValue : (string) ($existing?->destination_value ?? '');
            $resolvedProviderReference = $providerReference !== '' ? $providerReference : (string) ($existing?->provider_destination_reference ?? '');
            $maskedDestination = $this->maskPayoutDestination($validated['destination_type'], $resolvedDestinationValue, $resolvedProviderReference);
            $meta = is_array($existing?->meta) ? $existing->meta : [];
            $meta['notes'] = trim((string) ($validated['notes'] ?? ''));

            if ($validated['destination_type'] === 'gcash') {
                unset($meta['card']);
                $gcashMeta = is_array($meta['gcash'] ?? null) ? $meta['gcash'] : [];
                $gcashMeta = array_merge($gcashMeta, [
                    'account_name' => trim((string) $validated['account_name']),
                    'masked_destination' => $maskedDestination,
                    'reference' => $resolvedProviderReference !== '' ? $resolvedProviderReference : null,
                ]);

                if ($request->hasFile('gcash_qr')) {
                    $existingQr = trim((string) ($gcashMeta['qr_path'] ?? ''));
                    if ($existingQr !== '') {
                        Storage::disk('public')->delete($existingQr);
                    }

                    $gcashMeta['qr_path'] = $request->file('gcash_qr')->store('payout-qr', 'public');
                }

                $meta['gcash'] = $gcashMeta;
            } else {
                $existingQr = trim((string) data_get($meta, 'gcash.qr_path', ''));
                if ($existingQr !== '') {
                    Storage::disk('public')->delete($existingQr);
                }

                unset($meta['gcash']);
                $meta['card'] = [
                    'account_name' => trim((string) $validated['account_name']),
                    'masked_destination' => $maskedDestination,
                    'reference' => $resolvedProviderReference !== '' ? $resolvedProviderReference : null,
                ];
            }

            $attributes = [
                'tenant_id' => $user->tenant->id,
                'destination_type' => $validated['destination_type'],
                'account_name' => trim((string) $validated['account_name']),
                'destination_value' => $resolvedDestinationValue !== '' ? $resolvedDestinationValue : null,
                'masked_destination' => $maskedDestination,
                'provider_destination_reference' => $resolvedProviderReference !== '' ? $resolvedProviderReference : null,
                'meta' => $meta,
                'is_default' => true,
            ];

            $statusReset = [
                'is_verified' => false,
                'verified_at' => null,
                'verified_by' => null,
                'verification_status' => TenantPayoutAccount::STATUS_PENDING_PLATFORM_REVIEW,
                'reviewed_at' => null,
                'reviewed_by' => null,
                'review_notes' => null,
            ];

            $dispatchReviewEvent = false;
            $verificationReset = false;

            if ($existing) {
                $wasVerified = (bool) $existing->is_verified;
                $hasChanged = $existing->destination_type !== $attributes['destination_type']
                    || (string) $existing->destination_value !== (string) $attributes['destination_value']
                    || (string) $existing->provider_destination_reference !== (string) $attributes['provider_destination_reference']
                    || $existing->account_name !== $attributes['account_name'];

                if ($hasChanged) {
                    $attributes = array_merge($attributes, $statusReset);
                    $dispatchReviewEvent = true;
                    $verificationReset = $wasVerified;
                }

                $existing->update($attributes);
                $payoutAccount = $existing->fresh();
            } else {
                $payoutAccount = TenantPayoutAccount::create(array_merge($attributes, $statusReset));
                $dispatchReviewEvent = true;
            }

            return [
                'payoutAccount' => $payoutAccount,
                'attributes' => $attributes,
                'dispatchReviewEvent' => $dispatchReviewEvent,
                'verificationReset' => $verificationReset,
                'deduplicatedCount' => $deduplicatedCount,
            ];
        });

        /** @var TenantPayoutAccount $payoutAccount */
        $payoutAccount = $result['payoutAccount'];
        $attributes = $result['attributes'];
        $dispatchReviewEvent = $result['dispatchReviewEvent'];

        app(FinanceAuditService::class)->record(
            'payout_account_updated',
            'Tenant payout destination was updated.',
            $user,
            $user->tenant,
            null,
            null,
            [
                'destination_type' => $attributes['destination_type'],
                'masked_destination' => $attributes['masked_destination'],
                'verification_reset' => $result['verificationReset'],
                'payout_account_id' => $payoutAccount->id,
                'verification_status' => $payoutAccount->reviewStatus(),
                'deduplicated_count' => $result['deduplicatedCount'],
            ]
        );

        if ($dispatchReviewEvent) {
            $this->dispatchPayoutAutomationEvent('payout_account_pending_review', [
                'tenant_id' => $user->tenant->id,
                'tenant_name' => $user->tenant->company_name,
                'account_owner_id' => $user->id,
                'account_owner_name' => $user->name,
                'account_owner_email' => $user->email,
                'payout_account_id' => $payoutAccount->id,
                'destination_type' => $payoutAccount->destination_type,
                'masked_destination' => $payoutAccount->masked_destination,
                'verification_status' => $payoutAccount->reviewStatus(),
            ]);
        }

        $redirectRoute = $request->boolean('from_payout_setup')
            ? route('dashboard.owner')
            : route('profile.show');

        return redirect($redirectRoute)->with('success', 'Edited Successfully');
    }

    private function maskPayoutDestination(string $destinationType, string $destinationValue, string $providerReference): ?string
    {
        if ($destinationType === 'provider_managed') {
            if ($providerReference === '') {
                return null;
            }

            return 'Provider Ref: ' . mb_substr($providerReference, 0, 10) . (mb_strlen($providerReference) > 10 ? '...' : '');
        }

        if ($destinationValue === '') {
            return null;
        }

        $visibleChars = 4;
        $suffix = mb_substr($destinationValue, -$visibleChars);

        return str_repeat('*', max(0, mb_strlen($destinationValue) - $visibleChars)) . $suffix;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function dispatchPayoutAutomationEvent(string $eventName, array $payload): void
    {
        try {
            app(N8nEmailOrchestrator::class)->dispatch($eventName, $payload);
        } catch (\Throwable) {
            // Best-effort dispatch only.
        }
    }
}
