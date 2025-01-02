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
        return __('filament.pages.admin_contact.title');
    }
    public function getTitle(): string | Htmlable
    {
        return new HtmlString(__('filament.pages.admin_contact.title'));
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
                    ->label(__('filament.pages.admin_contact.form.subject'))
                    ->options([
                        'unban' => __('filament.pages.admin_contact.form.options.unban'),
                        'principal_banned' => __('filament.pages.admin_contact.form.options.principal_banned'),
                        'question' => __('filament.pages.admin_contact.form.options.question'),
                        'other' => __('filament.pages.admin_contact.form.options.other'),
                    ])
                    ->native(false)
                    ->required(),

                MarkdownEditor::make('message')
                    ->label(__('filament.pages.admin_contact.form.message.label'))
                    ->placeholder(__('filament.pages.admin_contact.form.message.placeholder'))
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
            ->title(__('filament.pages.admin_contact.form.success'))
            ->success()
            ->send();

        $this->form->fill();
    }
}
