<?php

namespace Mortezamasumi\FbProfile\Pages;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Actions\Action;
use Filament\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Filament\Auth\Pages\PasswordReset\RequestPasswordReset as BaseRequestPasswordReset;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Models\Contracts\FilamentUser;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Illuminate\Auth\Events\PasswordResetLinkSent;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Support\Facades\Password;
use Mortezamasumi\FbProfile\Enums\AuthType;
use Mortezamasumi\FbProfile\Facades\FbProfile;
use Mortezamasumi\FbProfile\Notifications\PasswordResetEmailCodeNotification;
use Mortezamasumi\FbProfile\Notifications\PasswordResetMobileNotification;
use Exception;

class RequestPasswordReset extends BaseRequestPasswordReset
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('mobile')
                    ->label(__('fb-profile::fb-profile.form.mobile'))
                    ->required()
                    ->tel()
                    ->telRegex('/^((\+|00)[1-9][0-9 \-\(\)\.]{11,18}|09\d{9})$/')
                    ->maxLength(30)
                    ->toEN()
                    ->visible(config('app.auth_type') === AuthType::Mobile),
                TextInput::make('email')
                    ->label(__('filament-panels::auth/pages/register.form.email.label'))
                    ->required()
                    ->rules(['email'])
                    ->extraAttributes(['dir' => 'ltr'])
                    ->maxLength(255)
                    ->toEN()
                    ->hidden(config('app.auth_type') === AuthType::Mobile),
            ]);
    }

    protected function getRequestFormAction(): Action
    {
        return Action::make('request')
            ->label(__(
                config('app.auth_type') === AuthType::Mobile
                    ? 'fb-profile::fb-profile.reset-password.request.action.mobile'
                    : 'fb-profile::fb-profile.reset-password.request.action.email'
            ))
            ->submit('request');
    }

    public function request(): void
    {
        // try {
        //     $this->rateLimit(2);
        // } catch (TooManyRequestsException $exception) {
        //     $this->getRateLimitedNotification($exception)?->send();

        //     return;
        // }

        $data = $this->form->getState();

        $status = Password::broker(Filament::getAuthPasswordBroker())->sendResetLink(
            $this->getCredentialsFromFormData($data),
            function (CanResetPassword $user, string $token) use (&$notification): void {
                if (
                    ($user instanceof FilamentUser) &&
                    (! $user->canAccessPanel(Filament::getCurrentOrDefaultPanel()))
                ) {
                    return;
                }

                if (! method_exists($user, 'notify')) {
                    $userClass = $user::class;

                    throw new Exception("Model [{$userClass}] does not have a [notify()] method.");
                }

                $notification = app(
                    match (config('app.auth_type')) {
                        AuthType::Link => ResetPasswordNotification::class,
                        AuthType::Code => PasswordResetEmailCodeNotification::class,
                        AuthType::Mobile => PasswordResetMobileNotification::class,
                    },
                    [
                        'token' => $token,
                        'code' => FbProfile::createCode($user)
                    ]
                );

                $notification->url = Filament::getResetPasswordUrl(
                    $token,
                    $user,
                    ['mobile' => $user->mobile]
                );

                /** @var Notifiable $user */
                $user->notify($notification);

                if (class_exists(PasswordResetLinkSent::class)) {
                    event(new PasswordResetLinkSent($user));
                }
            },
        );

        if ($status !== Password::RESET_LINK_SENT) {
            $this->getFailureNotification($status)?->send();

            return;
        }

        $this->getSentNotification($status)?->send();

        if (config('app.auth_type') === AuthType::Link) {
            $this->form->fill();
        } else {
            redirect($notification->url);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function getCredentialsFromFormData(array $data): array
    {
        return match (config('app.auth_type')) {
            AuthType::Link,
            AuthType::Code => ['email' => $data['email']],
            AuthType::Mobile => ['mobile' => $data['mobile']],
        };
    }

    protected function getSentNotification(string $status): ?Notification
    {
        switch (config('app.auth_type')) {
            case AuthType::Mobile:
                $title = 'fb-profile::fb-profile.reset-password.request.notification.mobile.title';
                $body = 'fb-profile::fb-profile.reset-password.request.notification.mobile.body';
                break;
            case AuthType::Code:
                $title = 'fb-profile::fb-profile.reset-password.request.notification.code.title';
                $body = 'fb-profile::fb-profile.reset-password.request.notification.code.body';
                break;
            case AuthType::Link:
                $title = $status;
                $body = 'filament-panels::auth/pages/password-reset/request-password-reset.notifications.sent.body';
                break;
        }

        return Notification::make()
            ->title(__($title))
            ->body(($status === Password::RESET_LINK_SENT) ? __($body) : null)
            ->success();
    }
}
