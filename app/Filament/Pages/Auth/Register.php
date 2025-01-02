<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Auth\Register as BaseRegister;
use Illuminate\Validation\Rules\Password;

class Register extends BaseRegister
{
    public $name = '';
    public $email = '';
    public $university = '';
    public $orcid = '';
    public $registration_reason = '';
    public $password = '';
    public $password_confirmation = '';

    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        TextInput::make('name')
                            ->label(__('filament.pages.auth.register.name'))
                            ->required()
                            ->maxLength(50)
                            ->rules(['required', 'string', 'max:50']),

                        TextInput::make('email')
                            ->label(__('filament.pages.auth.register.email.label'))
                            ->email()
                            ->required()
                            ->unique('users')
                            ->validationMessages([
                                'unique' => __('filament.pages.auth.register.email.unique'),
                            ])
                            ->rules(['required', 'email', 'max:255', 'unique:users']),

                        TextInput::make('university')
                            ->label(__('filament.pages.auth.register.university'))
                            ->required()
                            ->maxLength(255)
                            ->rules(['required', 'string', 'max:255']),

                        Textarea::make('registration_reason')
                            ->label(__('filament.pages.auth.register.registration_reason.label'))
                            ->required()
                            ->minLength(50)
                            ->helperText(__('filament.pages.auth.register.registration_reason.helpMessage'))
                            ->rules(['required', 'string', 'min:50']),

                        TextInput::make('orcid')
                            ->label(__('filament.pages.auth.register.orcid'))
                            ->maxLength(25)
                            ->rules(['nullable', 'string', 'max:25']),

                        TextInput::make('password')
                            ->label(__('filament.pages.auth.register.password.label'))
                            ->password()
                            ->required()
                            ->validationMessages([
                                'attributes' => __('filament.pages.auth.register.password.helpMessage'),
                            ])
                            ->rules(['required', 'string', Password::defaults()]),

                        TextInput::make('password_confirmation')
                            ->label(__('filament.pages.auth.register.confirm_password.label'))
                            ->password()
                            ->required()
                            ->validationMessages([
                                'same' => __('filament.pages.auth.register.confirm_password.helpMessage'),
                            ])
                            ->same('password')
                            ->rules(['required', 'string'])
                            ->dehydrated(false),
                    ])
            ),
        ];
    }

    protected function getFormData(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'university' => $this->university,
            'orcid' => $this->orcid,
            'registration_reason' => $this->registration_reason,
            'password' => $this->password,
            'password_confirmation' => $this->password_confirmation,
            'locale' => substr(request()->server('HTTP_ACCEPT_LANGUAGE'), 0, 2),
        ];
    }
}
