<?php

namespace Mortezamasumi\FbProfile\Enums;

use Filament\Support\Contracts\HasLabel;

enum GenderEnum: string implements HasLabel
{
    case Undefined = 'undefined';
    case Female = 'female';
    case Male = 'male';

    public function getLabel(): ?string
    {
        return __('fb-profile::fb-profile.gender.'.$this->value);
    }

    public function getTitle(): string
    {
        return match ($this) {
            self::Female => __('fb-profile::fb-profile.gender.ms'),
            self::Male => __('fb-profile::fb-profile.gender.mr'),
            default => '',
        };
    }
}
