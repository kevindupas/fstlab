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
use Illuminate\Support\Facades\Auth;

class ContactAdmin extends Page
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';
    protected static ?string $navigationLabel = 'Contacter l\'administrateur';
    protected static string $view = 'filament.pages.contact-admin';
    protected static ?int $navigationSort = 100;

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
                    ->label('Sujet')
                    ->options([
                        'unban' => 'Demande de débannissement',
                        'principal_banned' => 'Principal expérimentateur banni',
                        'question' => 'Question générale',
                        'other' => 'Autre',
                    ])
                    ->required(),

                MarkdownEditor::make('message')
                    ->label(__('filament.resources.my_experiment.form.description'))
                    ->placeholder(__('filament.resources.my_experiment.form.description_placeholder'))
                    ->required()
                    ->toolbarButtons([
                        'blockquote',
                        'bold',
                        'bulletList',
                        'codeBlock',
                        'heading',
                        'italic',
                        'link',
                        'orderedList',
                        'redo',
                        'strike',
                        'table',
                        'undo',
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
            ->title('Message envoyé')
            ->success()
            ->send();

        $this->form->fill();
    }
}
