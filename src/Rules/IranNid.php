<?php

namespace Mortezamasumi\FbProfile\Rules;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\App;
use Mortezamasumi\FbPersian\Facades\FbPersian;
use Closure;

class IranNid implements ValidationRule
{
    public function __construct(
        protected bool $condition = true,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $this->condition || config('fb-profile.use_passport_number_on_nid') || ! App::isProduction()) {
            return;
        }

        $value = FbPersian::arfaTOen($value);

        if (
            preg_match('/^\d{10}$/', $value) != 1 ||
            $value === '0000000000' ||
            $value === '1111111111' ||
            $value === '2222222222' ||
            $value === '3333333333' ||
            $value === '4444444444' ||
            $value === '5555555555' ||
            $value === '6666666666' ||
            $value === '7777777777' ||
            $value === '8888888888' ||
            $value === '9999999999'
        ) {
            $fail('failed');
        }

        $s = 0;

        for ($i = 0; $i < 9; $i++) {
            $s += (10 - $i) * (int) substr($value, $i, 1);
        }

        $s %= 11;

        if (
            ! (($s < 2 && $s === (int) substr($value, 9, 1)) ||
                ($s >= 2 && $s === (11 - (int) substr($value, 9, 1))))
        ) {
            $fail('failed');
        }
    }
}
