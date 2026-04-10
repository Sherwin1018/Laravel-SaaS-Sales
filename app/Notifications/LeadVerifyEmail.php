<?php

namespace App\Notifications;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class LeadVerifyEmail extends Notification
{
    use Queueable;

    public function __construct(
        public Lead $lead,
        public int $funnelId = 0,
        public ?string $funnelName = null,
        public int $optInStepId = 0
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $verifyUrl = $this->verificationUrl();

        return (new MailMessage)
            ->subject('Confirm your email address')
            ->greeting('Hello!')
            ->line('Please click the button below to confirm your email address and complete your sign-up.')
            ->action('Confirm Email Address', $verifyUrl)
            ->line('If you did not sign up for this, you can safely ignore this email.');
    }

    protected function verificationUrl(): string
    {
        return URL::temporarySignedRoute(
            'funnels.lead.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $this->lead->getKey(),
                'hash' => sha1($this->lead->getEmailForVerification()),
                'funnel_id' => $this->funnelId,
                'opt_in_step_id' => $this->optInStepId,
            ]
        );
    }
}
