<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;


class Profile extends BaseEditProfile
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
                Select::make('locale')
                    ->label(__('pages.auth.profile.language'))
                    ->helperText(__('pages.auth.profile.helper_text'))
                    ->options([
                        'fr' => 'Français',
                        'en' => 'English',
                    ])
                    ->required()
                    ->native(false),
            ]);
    }

    public function submit(): void
    {
        $data = $this->form->getState();

        $this->user->update($data);

        if (isset($data['locale']) && $data['locale'] !== app()->getLocale()) {
            app()->setLocale($data['locale']);
        }

        $this->notify('success', __('pages.auth.profile.notifications.saved'));
    }
}
