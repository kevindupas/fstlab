<?php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Auth\Register as BaseRegister;
use Illuminate\Validation\Rules\Password;
use Filament\Actions\Action;
use Filament\Http\Responses\Auth\RegistrationResponse;
use Illuminate\Database\Eloquent\Model;

class Register extends BaseRegister
{
    public $name = '';
    public $email = '';
    public $university = '';
    public $orcid = '';
    public $registration_reason = '';
    public $password = '';
    public $password_confirmation = '';
    public $terms_accepted = false;

    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        TextInput::make('name')
                            ->label(__('pages.auth.register.name'))
                            ->required()
                            ->maxLength(50)
                            ->rules(['required', 'string', 'max:50']),

                        TextInput::make('email')
                            ->label(__('pages.auth.register.email.label'))
                            ->email()
                            ->required()
                            ->unique('users')
                            ->validationMessages([
                                'unique' => __('pages.auth.register.email.unique'),
                            ])
                            ->rules(['required', 'email', 'max:255', 'unique:users']),

                        TextInput::make('university')
                            ->label(__('pages.auth.register.university'))
                            ->required()
                            ->maxLength(255)
                            ->rules(['required', 'string', 'max:255']),

                        Textarea::make('registration_reason')
                            ->label(__('pages.auth.register.registration_reason.label'))
                            ->required()
                            ->minLength(50)
                            ->helperText(fn($state) => strlen($state) . __('pages.auth.register.registration_reason.helpMessage'))
                            ->rules(['required', 'string', 'min:50']),

                        TextInput::make('orcid')
                            ->label(__('pages.auth.register.orcid'))
                            ->prefix('https://orcid.org/')
                            ->maxLength(25)
                            ->placeholder('0000-0000-0000-0000')
                            ->columnSpan(1),

                        TextInput::make('password')
                            ->label(__('pages.auth.register.password.label'))
                            ->password()
                            ->required()
                            ->validationMessages([
                                'attributes' => __('pages.auth.register.password.helpMessage'),
                            ])
                            ->rules([
                                'required',
                                'string',
                                Password::min(3)
                                    ->mixedCase()
                                    ->numbers()
                                    ->symbols()
                            ]),

                        TextInput::make('password_confirmation')
                            ->label(__('pages.auth.register.confirm_password.label'))
                            ->password()
                            ->required()
                            ->validationMessages([
                                'same' => __('pages.auth.register.confirm_password.helpMessage'),
                            ])
                            ->same('password')
                            ->rules(['required', 'string'])
                            ->dehydrated(false),

                        Checkbox::make('terms_accepted')
                            ->label(fn() => view('filament.components.terms-checkbox'))
                            ->required()
                            ->rules(['required', 'accepted'])
                            ->live() // Important pour mettre à jour l'état du bouton en temps réel
                            ->columnSpanFull()
                            ->validationMessages([
                                'accepted' => __('pages.auth.register.terms.required'),
                            ]),
                    ])
            ),
        ];
    }

    protected function mutateFormDataBeforeRegister(array $data): array
    {
        return [
            ...$data,
            'status' => 'unverified',
        ];
    }
    protected function getFormActions(): array
    {
        return [
            $this->getRegisterFormAction()
                ->label(__('filament-panels::pages/auth/register.form.actions.register.label'))
                ->disabled(fn() => ! $this->terms_accepted)
                ->action('register'),
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
            'terms_accepted' => $this->terms_accepted,
            'locale' => substr(request()->server('HTTP_ACCEPT_LANGUAGE'), 0, 2),
        ];
    }
}
