<?php

namespace Mortezamasumi\FbProfile;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Mortezamasumi\FbProfile\Enums\AuthType;
use Mortezamasumi\FbProfile\Pages\FbProfile;
use Mortezamasumi\FbProfile\Pages\Register;
use Mortezamasumi\FbProfile\Pages\RequestPasswordReset;
use Mortezamasumi\FbProfile\Pages\ResetPassword;
use Exception;

class FbProfilePlugin implements Plugin
{
    public function getId(): string
    {
        return 'fb-profile';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->profile(FbProfile::class)
            ->emailChangeVerification();

        if ($panel->hasRegistration()) {
            $panel
                ->registration(Register::class);
        }

        if ($panel->hasPasswordReset()) {
            $panel
                ->passwordReset(RequestPasswordReset::class, ResetPassword::class);
        }
    }

    public function boot(Panel $panel): void
    {
        $values = collect(config('fb-profile'))
            ->only(['mobile_required', 'email_required', 'username_required']);

        $trueCount = count($values->filter());

        if ($trueCount > 1) {
            throw new Exception('Only one of link/code/mobile verification can be select');
        } elseif ($trueCount === 0) {
            throw new Exception('At least one of link/code/mob must be required');
        }

        if (config('fb-profile.email_required')) {
            if (config('fb-profile.email_link_verification')) {
                $type = AuthType::Link;
            } else {
                $type = AuthType::Code;
            }
        } else {
            if (config('fb-profile.email_link_verification')) {
                throw new Exception('Can not use link while auth type are mobile/username');
            }
            if (config('fb-profile.mobile_required')) {
                $type = AuthType::Mobile;
            } else {
                $type = AuthType::User;

                $panel
                    ->emailVerification(null)
                    ->passwordReset(null, null);
            }
        }

        config(['app.auth_type' => $type]);
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }
}
