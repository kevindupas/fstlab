<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Experiments\Details\ExperimentDetails;
use App\Models\Experiment;
use App\Models\User;
use App\Notifications\UserMessage;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class ContactPrincipal extends Page
{
    use InteractsWithForms;

    protected static bool $shouldRegisterNavigation = true;
    protected static ?string $slug = 'contact-principal';
    protected static string $view = 'filament.pages.contact-user';
    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    public ?array $data = [];
    public ?Experiment $experiment = null;

    public static function shouldRegisterNavigation(): bool
    {
        /** @var \App\Models\User */
        $user = Auth::user();
        return $user?->hasRole('secondary_experimenter') ?? false;
    }

    public function mount(): void
    {
        $experimentId = request()->query('experiment');
        $this->experiment = $experimentId ? Experiment::find($experimentId) : null;

        $this->form->fill([
            'experiment_id' => $this->experiment?->id,
        ]);
    }

    public function form(Form $form): Form
    {
        /** @var \App\Models\User */
        $user = Auth::user();

        return $form
            ->schema([
                Select::make('experiment_id')
                    ->label(__('filament.pages.user_contact.form.experiment'))
                    ->options(
                        // Récupérer uniquement les expériences attribuées au compte secondaire
                        Experiment::whereHas('users', function ($query) use ($user) {
                            $query->where('users.id', $user->id);
                        })->pluck('name', 'id')
                    )
                    ->searchable()
                    ->reactive()
                    ->disabled(fn() => $this->experiment !== null),

                MarkdownEditor::make('message')
                    ->label(__('filament.pages.user_contact.form.message.label'))
                    ->placeholder(__('filament.pages.user_contact.form.message.placeholder'))
                    ->required()
                    ->columnSpanFull()
                    ->toolbarButtons([
                        'bold',
                        'bulletList',
                        'orderedList',
                        'italic',
                        'link',
                        'undo',
                        'redo',
                    ]),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $data = $this->form->getState();

        /** @var \App\Models\User */
        $sender = Auth::user();
        $principal = User::find($sender->created_by);

        if (!$principal) {
            Notification::make()
                ->title('Erreur')
                ->danger()
                ->body('Expérimentateur principal non trouvé')
                ->send();
            return;
        }

        $experiment = isset($data['experiment_id']) ? Experiment::find($data['experiment_id']) : null;

        // Envoyer la notification au principal
        $principal->notify(new UserMessage(
            message: $data['message'],
            experiment: $experiment,
            sender: $sender
        ));

        Notification::make()
            ->title(__('filament.pages.admin_contact.form.success'))
            ->success()
            ->send();

        if ($this->experiment) {
            $this->redirect(ExperimentDetails::getUrl(['record' => $this->experiment]));
        } else {
            $this->form->fill();
        }
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.pages.user_contact.title_secondary_experimenter');
    }

    public function getTitle(): string | Htmlable
    {
        return new HtmlString(__('filament.pages.user_contact.title_secondary_experimenter'));
    }
}
