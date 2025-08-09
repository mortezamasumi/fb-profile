<?php

namespace Mortezamasumi\FbProfile\Component;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Mortezamasumi\FbProfile\Enums\GenderEnum;

class Profile
{
    public static function components(): array
    {
        return [
            FileUpload::make('avatar')
                ->hiddenLabel()
                ->avatar()
                ->disk('public')
                ->directory('avatars')
                ->visibility('public')
                ->maxSize(config('fb-profile.max_avatar_size', 200))
                ->columnSpanFull()
                ->alignCenter(),
            TextInput::make('first_name')
                ->label(__('fb-profile::fb-profile.form.first_name'))
                ->required()
                ->maxLength(255),
            TextInput::make('last_name')
                ->label(__('fb-profile::fb-profile.form.last_name'))
                ->required()
                ->maxLength(255),
            TextInput::make('nid')
                ->label(fn () => (__(config('fb-profile.use_passport_number_on_nid')
                    ? 'fb-profile::fb-profile.form.nid_pass'
                    : 'fb-profile::fb-profile.form.nid')))
                ->required(config('fb-profile.nid_required'))
                // ->regex(fn () => (__(config('fb-profile.use_passport_number_on_nid')
                //     ? '/^(?:\d{10}|[A-Za-z].*\d{5,})$/'
                //     : '/^\d{10}$/')))
                ->maxLength(255)
                ->rule('iran_nid')
                ->toEN(),
            TextInput::make('profile.father_name')
                ->label(__('fb-profile::fb-profile.form.profile.father_name'))
                ->maxLength(255),
            Select::make('gender')
                ->label(__('fb-profile::fb-profile.form.gender'))
                ->required(config('fb-profile.gender_required'))
                ->options(GenderEnum::class),
            DatePicker::make('birth_date')
                ->label(__('fb-profile::fb-profile.form.birth_date'))
                ->maxDate(now()->endOfDay())
                ->required(config('fb-profile.birth_date_required'))
                ->jDate(),
            TextInput::make('mobile')
                ->label(__('fb-profile::fb-profile.form.mobile'))
                ->required(config('fb-profile.mobile_required'))
                ->tel()
                ->telRegex('/^((\+|00)[1-9][0-9 \-\(\)\.]{11,18}|09\d{9})$/')
                ->maxLength(30)
                ->toEN(),
            TextInput::make('email')
                ->label(__('filament-panels::auth/pages/register.form.email.label'))
                ->required(config('fb-profile.email_required'))
                ->rules(['email'])
                ->extraAttributes(['dir' => 'ltr'])
                ->maxLength(255)
                ->toEN(),
            TextInput::make('username')
                ->label(__('fb-profile::fb-profile.form.username'))
                ->required(config('fb-profile.username_required'))
                ->maxLength(255),
        ];
    }
}
