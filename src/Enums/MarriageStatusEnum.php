<?php

namespace Mortezamasumi\FbProfile\Enums;

use Filament\Support\Contracts\HasLabel;

enum MarriageStatusEnum: string implements HasLabel
{
    case SINGLE = 'single';
    case MARRIED = 'married';
    case UNKNOWN = 'unknown';

    public function getLabel(): ?string
    {
        return __('fb-profile::fb-profile.marriage.'.$this->value);
    }
}
