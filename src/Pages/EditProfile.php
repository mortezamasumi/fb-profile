<?php

namespace Mortezamasumi\FbProfile\Pages;

use Filament\Auth\Pages\EditProfile as PagesEditProfile;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Model;
use Mortezamasumi\FbProfile\Enums\GenderEnum;

class EditProfile extends PagesEditProfile
{
    protected Width|string|null $maxContentWidth = '3xl';

    public static function formComponents(): array
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

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components(static::formComponents())
            ->columns(3);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        if (Filament::hasEmailChangeVerification() && array_key_exists('email', $data)) {
            $this->sendEmailChangeVerification($record, $data['email']);

            unset($data['email']);
        }

        $record->update($data);

        return $record;
    }

    protected function getRedirectUrl(): ?string
    {
        return Filament::getCurrentPanel()->getLoginUrl();
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return __('fb-profile::fb-profile.notification.title');
    }
}
