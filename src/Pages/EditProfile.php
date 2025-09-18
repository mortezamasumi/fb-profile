<?php

namespace Mortezamasumi\FbProfile\Pages;

use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Facades\Filament;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Model;
use Mortezamasumi\FbProfile\Schemas\ProfileForm;

class EditProfile extends BaseEditProfile
{
    protected Width|string|null $maxContentWidth = '3xl';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components(ProfileForm::components(true))
            ->columns(config('fb-profile.profile_form_columns'));
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
