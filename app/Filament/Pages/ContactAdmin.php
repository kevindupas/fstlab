<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use App\Notifications\AdminContactMessage;
use App\Models\User;
use Filament\Forms\Components\MarkdownEditor;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class ContactAdmin extends Page
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';
    protected static string $view = 'filament.pages.contact-admin';
    protected static ?int $navigationSort = 100;

    public static function getNavigationLabel(): string
    {
        return __('pages.admin_contact.title');
    }
    public function getTitle(): string | Htmlable
    {
        return new HtmlString(__('pages.admin_contact.title'));
    }
    public static function shouldRegisterNavigation(): bool
    {
        /** @var \App\Models\User */
        $user = Auth::user();
        return !$user?->hasRole('supervisor') ?? false;
    }

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('subject')
                    ->label(__('pages.admin_contact.form.subject'))
                    ->options(function ($get) {
                        $userId = Auth::id();
                        $selectedUser = $userId ? User::find($userId) : null;

                        $options = [
                            'unban' => __('pages.admin_contact.form.options.unban'),
                            'principal_banned' => __('pages.admin_contact.form.options.principal_banned'),
                            'question' => __('pages.admin_contact.form.options.question'),
                            'other' => __('pages.admin_contact.form.options.other'),
                        ];

                        // Ajouter l'option seulement si l'utilisateur est un compte secondaire
                        if ($selectedUser && $selectedUser->hasRole('secondary_experimenter')) {
                            $options['secondary_option'] = __('pages.admin_contact.form.options.secondary_option');
                        }

                        return $options;
                    })
                    ->native(false)
                    ->reactive()
                    ->required(),

                MarkdownEditor::make('message')
                    ->label(__('pages.admin_contact.form.message.label'))
                    ->placeholder(__('pages.admin_contact.form.message.placeholder'))
                    ->required()
                    ->toolbarButtons([
                        'bold',
                        'bulletList',
                        'orderedList',
                        'italic',
                        'link',
                        'undo',
                        'redo',
                    ])
                    ->columnSpan('full'),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $supervisor = User::role('supervisor')->first();

        if ($supervisor) {
            $supervisor->notify(new AdminContactMessage(
                Auth::user(),
                $this->form->getState()['subject'],
                $this->form->getState()['message']
            ));
        }

        Notification::make()
            ->title(__('pages.admin_contact.form.success'))
            ->success()
            ->send();

        $this->form->fill();
    }
}
