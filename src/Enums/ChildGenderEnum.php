<?php

namespace Mortezamasumi\FbProfile\Enums;

use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ChildGenderEnum: string implements HasLabel, HasColor
{
    case Undefined = 'undefined';
    case Girl = 'girl';
    case Boy = 'boy';

    public function getLabel(): ?string
    {
        return __('fb-profile::fb-profile.gender.'.$this->value);
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Girl => Color::Rose,
            self::Boy => Color::Sky,
            self::Undefined => Color::Stone,
        };
    }
}
