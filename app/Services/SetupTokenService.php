<?php

namespace App\Services;

use App\Models\SetupToken;
use App\Models\SignupIntent;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SetupTokenService
{
    /**
     * @param  array<string, mixed>|null  $meta
     * @return array{token: string, setupToken: SetupToken}
     */
    public function createForUser(User $user, string $purpose, ?array $meta = null, int $expiresHours = 24): array
    {
        $token = Str::random(64);
        $tokenHash = hash('sha256', $token);

        $setupToken = SetupToken::create([
            'user_id' => $user->id,
            'purpose' => $purpose,
            'token_hash' => $tokenHash,
            'expires_at' => now()->addHours($expiresHours),
            'meta' => $meta,
        ]);

        app(OnboardingAuditService::class)->record(
            'setup_token_created',
            'success',
            'Setup token created.',
            $user,
            SignupIntent::query()->where('email', $user->email)->latest('id')->first(),
            [
                'purpose' => $purpose,
                'expires_at' => optional($setupToken->expires_at)?->toIso8601String(),
            ],
        );

        return [
            'token' => $token,
            'setupToken' => $setupToken,
        ];
    }

    public function resolveByPlainToken(string $plainToken): ?SetupToken
    {
        if ($plainToken === '') {
            return null;
        }

        $tokenHash = hash('sha256', $plainToken);

        return SetupToken::query()
            ->where('token_hash', $tokenHash)
            ->with('user.roles')
            ->first();
    }

    public function isUsable(?SetupToken $setupToken): bool
    {
        return $setupToken instanceof SetupToken && $setupToken->isUsable();
    }

    public function consume(SetupToken $setupToken): SetupToken
    {
        return DB::transaction(function () use ($setupToken) {
            $locked = SetupToken::query()->lockForUpdate()->findOrFail($setupToken->id);
            if ($locked->used_at === null) {
                $locked->update(['used_at' => now()]);

                $user = $locked->user()->first();
                app(OnboardingAuditService::class)->record(
                    'setup_token_used',
                    'success',
                    'Setup token consumed.',
                    $user,
                    $user ? SignupIntent::query()->where('email', $user->email)->latest('id')->first() : null,
                    [
                        'purpose' => $locked->purpose,
                        'token_id' => $locked->id,
                    ],
                );
            }

            return $locked->fresh();
        });
    }
}
