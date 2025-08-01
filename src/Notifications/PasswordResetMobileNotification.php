<?php

namespace Mortezamasumi\FbProfile\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class PasswordResetMobileNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $url;

    public function __construct(
        protected string $code,
    ) {}

    public function via($notifiable)
    {
        return ['sms'];
    }

    public function toSms(object $notifiable): string
    {
        return __('fb-profile::fb-profile.reset-password.text-message', [
            'app' => __(config('app.name')),
            'code' => $this->code,
        ]);
    }
}
