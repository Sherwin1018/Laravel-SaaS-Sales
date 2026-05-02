<?php

use App\Services\PlatformAdminProvisioningService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('platform-admin:setup-link {email? : Platform admin email. Omit to issue links for configured platform admins.} {--force-reset : Rotate the current password and require setup completion before the next login.}', function (PlatformAdminProvisioningService $provisioning) {
    $forceReset = (bool) $this->option('force-reset');
    $email = $this->argument('email');
    $provisioning->provisionConfiguredAccounts();

    if (is_string($email) && trim($email) !== '') {
        $user = $provisioning->findUserByEmail($email);

        if (! $user) {
            $this->error('No user found for that email.');

            return 1;
        }

        if (! $user->hasAnyRole(['super-admin', 'payout-admin'])) {
            $this->error('That user is not a configured platform admin.');

            return 1;
        }

        $link = $provisioning->issueSetupLink($user, $forceReset);

        $this->line($user->email . ' => ' . $link);

        return 0;
    }

    foreach ($provisioning->provisionConfiguredAccounts() as $user) {
        $link = $provisioning->issueSetupLink($user, $forceReset);
        $this->line($user->email . ' => ' . $link);
    }

    return 0;
})->purpose('Issue a one-time setup link for the platform super admin and payout admin accounts.');
