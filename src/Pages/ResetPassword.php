<?php

namespace Mortezamasumi\FbProfile\Pages;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Actions\Action;
use Filament\Auth\Http\Responses\Contracts\PasswordResetResponse;
use Filament\Auth\Pages\PasswordReset\ResetPassword as BaseResetPassword;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Models\Contracts\FilamentUser;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\PasswordResetLinkSent;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Livewire\Attributes\Locked;
use Mortezamasumi\FbProfile\Enums\AuthType;
use Mortezamasumi\FbProfile\Facades\FbProfile;
use Mortezamasumi\FbProfile\Notifications\PasswordResetEmailCodeNotification;
use Mortezamasumi\FbProfile\Notifications\PasswordResetMobileNotification;
use Closure;
use Exception;

class ResetPassword extends BaseResetPassword
{
    #[Locked]
    public ?string $mobile = null;

    public ?string $otp = null;

    public function mount(?string $email = null, ?string $token = null): void
    {
        if (Filament::auth()->check()) {
            redirect()->intended(Filament::getUrl());
        }

        $this->token = $token ?? request()->query('token');

        $this->form->fill([
            'email' => $email ?? request()->query('email'),
            'mobile' => request()->query('mobile'),
        ]);
    }

    public function resetPassword(): ?PasswordResetResponse
    {
        try {
            $this->rateLimit(2);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        $data = $this->form->getState();

        $data['mobile'] = $this->mobile;
        $data['email'] = $this->email;
        $data['token'] = $this->token;

        $hasPanelAccess = true;

        $status = Password::broker(Filament::getAuthPasswordBroker())->reset(
            $this->getCredentialsFromFormData($data),
            function (CanResetPassword|Model|Authenticatable $user) use ($data, &$hasPanelAccess): void {
                if (
                    ($user instanceof FilamentUser) &&
                    (! $user->canAccessPanel(Filament::getCurrentOrDefaultPanel()))
                ) {
                    $hasPanelAccess = false;

                    return;
                }

                $user->forceFill([
                    'password' => Hash::make($data['password']),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($hasPanelAccess === false) {
            $status = Password::INVALID_USER;
        }

        if ($status === Password::PASSWORD_RESET) {
            Notification::make()
                ->title(__($status))
                ->success()
                ->send();

            return app(PasswordResetResponse::class);
        }

        Notification::make()
            ->title(__($status))
            ->danger()
            ->send();

        return null;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function getCredentialsFromFormData(array $data): array
    {
        switch (config('app.auth_type')) {
            case AuthType::Mobile:
                unset($data['email']);
                break;
            default:
                unset($data['mobile']);
                break;
        }

        unset($data['otp']);

        return $data;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('otp')
                    ->hiddenLabel()
                    ->rules([
                        fn (): Closure => function (string $attribute, $value, Closure $fail) {
                            $code = Cache::get('otp-'.match (config('app.auth_type')) {
                                AuthType::Mobile => $this->mobile,
                                AuthType::Code => $this->email,
                            });

                            if (! $code) {
                                $fail(__('fb-profile::fb-profile.reset-password.otp-expired'));
                            }

                            if ($value !== $code) {
                                $fail(__('fb-profile::fb-profile.reset-password.otp-validation'));
                            }
                        }
                    ])
                    ->hintAction(
                        Action::make('resend-code')
                            ->label(__('fb-profile::fb-profile.reset-password.resend-code'))
                            ->action(fn ($state) => $this->resend())
                    )
                    ->hidden(config('app.auth_type') === AuthType::Link),
                $this
                    ->getEmailFormComponent()
                    ->visible(config('app.auth_type') === AuthType::Link),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
            ]);
    }

    // protected function getCodeFormComponent(): Component
    // {
    //     return OTPInput::make('code')
    //         ->hiddenLabel()
    //         ->required()
    //         ->rules([
    //             fn (): Closure => function (string $attribute, $value, Closure $fail) {
    //                 $code = Cache::get('email-code-verify-otp-'.$this->email);

    //                 if ($value !== $code) {
    //                     $fail(Lang::get('email-code-verify::email-code-verify.reset-password.form.code.error'));
    //                 }
    //             }
    //         ])
    //         ->dehydrated(false);
    // }

    public function resend(): void
    {
        try {
            $this->rateLimit(2);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return;
        }

        $data['mobile'] = $this->mobile;
        $data['email'] = $this->email;
        $data['token'] = $this->token;

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

        redirect($notification->url);
    }

    protected function getFailureNotification(string $status): ?Notification
    {
        return Notification::make()
            ->title(__($status))
            ->danger();
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
