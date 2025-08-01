<?php

namespace Mortezamasumi\FbProfile;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Mortezamasumi\FbProfile\Enums\AuthType;

class FbProfile
{
    public function encodeEmail(string $email): string
    {
        [$username, $domain] = explode('@', $email);
        $maskedUsername = str_repeat('*', 2).substr($username, -2, 2);
        $domainParts = explode('.', $domain);
        $maskedDomain = str_repeat('*', 2).substr($domainParts[0], -2, 2);
        $codedEmail = $maskedUsername.'@'.$maskedDomain.'.'.$domainParts[1];

        return $codedEmail;
    }

    public function generateRandomCode(): string
    {
        $digits = config('fb-profile.otp_digits');
        $min = pow(10, $digits - 1);
        $max = pow(10, $digits) - 1;

        // return '1234';
        return str_pad(random_int($min, $max), $digits, '0', STR_PAD_LEFT);
    }

    public function createCode(Model $user): string
    {
        $code = $this->generateRandomCode();

        $identifire = match (config('app.auth_type')) {
            AuthType::Mobile => $user->mobile,
            AuthType::Code,
            AuthType::Link => $user->email,
            default => $user->id,
        };

        Cache::forget('otp-'.$identifire);
        Cache::add(
            'otp-'.$identifire,
            $code,
            (int) config('auth.passwords.'.config('auth.defaults.passwords').'.expire') * 2
        );

        return $code;
    }
}
