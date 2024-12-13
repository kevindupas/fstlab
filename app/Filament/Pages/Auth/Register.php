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
                            ->label('Nom complet')
                            ->required()
                            ->maxLength(255)
                            ->rules(['required', 'string', 'max:255']),

                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique('users')
                            ->rules(['required', 'email', 'max:255', 'unique:users']),

                        TextInput::make('university')
                            ->label('Université')
                            ->required()
                            ->maxLength(255)
                            ->rules(['required', 'string', 'max:255']),

                        Textarea::make('registration_reason')
                            ->label('Pourquoi souhaitez-vous vous inscrire ?')
                            ->required()
                            ->minLength(50)
                            ->rules(['required', 'string', 'min:50']),

                        TextInput::make('orcid')
                            ->label('Numéro ORCID')
                            ->maxLength(255),
                        // ->rules(['nullable', 'string', 'max:255']),

                        TextInput::make('password')
                            ->label('Mot de passe')
                            ->password()
                            ->required()
                            ->rules(['required', 'string', Password::defaults()])
                            ->same('password_confirmation'),

                        TextInput::make('password_confirmation')
                            ->label('Confirmation du mot de passe')
                            ->password()
                            ->required()
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
            'registration_reason' => $this->registration_reason,
            'password' => $this->password,
            'password_confirmation' => $this->password_confirmation,
        ];
    }
}
