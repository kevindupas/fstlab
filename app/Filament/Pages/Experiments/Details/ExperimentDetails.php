<?php

namespace App\Filament\Pages\Experiments\Details;

use App\Filament\Pages\Experiments\Lists\ExperimentsList;
use App\Models\Experiment;
use App\Models\User;
use App\Notifications\UserBanned;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class ExperimentDetails extends Page
{
    protected static string $view = 'filament.pages.experiments.details.experiment-details';
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $slug = 'experiment-details/{record}';

    public function getTitle(): string | Htmlable
    {
        return new HtmlString(__('filament.pages.experiment_details.title'));
    }

    public Experiment $record;

    public function mount(Experiment $record): void
    {
        // Empêcher l'accès aux expérimentations du supervisor
        if (
            $record->created_by === Auth::user()->id ||
            (Auth::user()->hasRole('supervisor') && User::find($record->created_by)->hasRole('supervisor'))
        ) {
            $this->redirect(ExperimentsList::getUrl());
            return;
        }

        $this->record = $record;
    }

    public function experimentInfolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->record($this->record)
            ->schema([
                Section::make(__('filament.pages.experiment_details.information_section.title'))
                    ->description(__('filament.pages.experiment_details.information_section.description'))
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        TextEntry::make('name')
                            ->label(__('filament.pages.experiment_details.information_section.name'))
                            ->size(TextEntry\TextEntrySize::Large)
                            ->weight('bold')
                            ->columnSpanFull(),

                        TextEntry::make('creator.name')
                            ->label(__('filament.pages.experiment_details.information_section.created_by'))
                            ->icon('heroicon-m-user')
                            ->iconColor('primary'),

                        TextEntry::make('created_at')
                            ->label(__('filament.pages.experiment_details.information_section.created_at'))
                            ->dateTime()
                            ->icon('heroicon-m-calendar'),

                        TextEntry::make('type')
                            ->label('Type')
                            ->badge()
                            ->formatStateUsing(fn(string $state): string => match ($state) {
                                'sound' => __('filament.pages.experiment_details.information_section.type.options.sound'),
                                'image' => __('filament.pages.experiment_details.information_section.type.options.image'),
                                'image_sound' => __('filament.pages.experiment_details.information_section.type.options.image_sound'),
                                default => $state
                            })
                            ->color(fn(string $state): string => match ($state) {
                                'sound' => 'success',
                                'image' => 'info',
                                'image_sound' => 'warning',
                            }),

                        TextEntry::make('status')
                            ->label('Statut')
                            ->badge()
                            ->formatStateUsing(fn(string $state): string => match ($state) {
                                'start' => __('filament.pages.experiment_details.information_section.status.options.start'),
                                'pause' => __('filament.pages.experiment_details.information_section.status.options.pause'),
                                'stop' => __('filament.pages.experiment_details.information_section.status.options.stop'),
                                'test' => __('filament.pages.experiment_details.information_section.status.options.test'),
                                'none' => __('filament.pages.experiment_details.information_section.status.options.none'),
                                default => $state
                            })
                            ->color(fn(string $state): string => match ($state) {
                                'none' => 'gray',
                                'start' => 'success',
                                'pause' => 'warning',
                                'stop' => 'danger',
                                'test' => 'info',
                            }),

                        TextEntry::make('link')
                            ->label(__('filament.pages.experiment_details.information_section.link'))
                            ->url(fn($record) => url("/experiment/{$record->link}"))
                            ->openUrlInNewTab()
                            ->visible(fn($record) => $record->link !== null)
                            ->icon('heroicon-m-link'),

                        TextEntry::make('doi')
                            ->label(__('filament.pages.experiment_details.information_section.doi'))
                            ->visible(fn($record) => filled($record->doi))
                            ->icon('heroicon-m-document-text'),
                    ])
                    ->columns(3),

                Section::make(__('filament.pages.experiment_details.description_section.title'))
                    ->description(__('filament.pages.experiment_details.description_section.description'))
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        TextEntry::make('description')
                            ->label(__('filament.pages.experiment_details.description_section.description'))
                            ->markdown()
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                Section::make(__('filament.pages.experiment_details.instruction_section.title'))
                    ->description(__('filament.pages.experiment_details.instruction_section.description'))
                    ->icon('heroicon-o-clipboard-document-list')
                    ->schema([
                        TextEntry::make('instruction')
                            ->label(__('filament.pages.experiment_details.instruction_section.description'))
                            ->markdown()
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                Section::make(__('filament.pages.experiment_details.settings_section.title'))
                    ->description(__('filament.pages.experiment_details.settings_section.description'))
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->schema([
                        TextEntry::make('button_size')
                            ->label(__('filament.pages.experiment_details.settings_section.button_size'))
                            ->suffix('px'),
                        TextEntry::make('button_color')
                            ->label(__('filament.pages.experiment_details.settings_section.button_color'))
                            ->formatStateUsing(fn($state) => new HtmlString("
                                <div class='flex items-center gap-2'>
                                    <div class='w-6 h-6 rounded border' style='background-color: {$state}'></div>
                                    <span>{$state}</span>
                                </div>
                            ")),
                    ])
                    ->columns(2),

                $this->getMediaSection(),
            ]);
    }

    protected function getMediaSection(): Section
    {
        return Section::make(__('filament.pages.experiment_details.medias_section.title'))
            ->description(__('filament.pages.experiment_details.medias_section.description'))
            ->icon('heroicon-o-photo')
            ->collapsible()
            ->schema([
                TextEntry::make('media')
                    ->label(__('filament.pages.experiment_details.medias_section.medias'))
                    ->visible(fn($record) => !empty($record->media))
                    ->formatStateUsing(function ($state) {
                        $mediaFiles = is_string($state) ? explode(',', $state) : $state;
                        $mediaFiles = collect($mediaFiles)->map(fn($path) => trim($path));

                        // Séparer les fichiers par type
                        $images = $mediaFiles->filter(fn($path) => str_contains($path, '.jpg') || str_contains($path, '.png'));
                        $audio = $mediaFiles->filter(fn($path) => str_contains($path, '.wav') || str_contains($path, '.mp3'));

                        $imagesTranslatable = __('filament.pages.experiment_details.medias_section.images');
                        $audioTranslatable = __('filament.pages.experiment_details.medias_section.sounds');

                        $html = '';

                        // Section Images si présentes
                        if ($images->isNotEmpty()) {
                            $imagesHtml = $images->map(function ($path) {
                                return "
                                <div class='bg-gray-50 dark:bg-gray-700 rounded-lg shadow-sm overflow-hidden'>
                                    <img src='/storage/{$path}' alt='Media' class='w-full h-48 object-cover' />
                                    <div class='p-3'>
                                        <div class='text-xs text-gray-500'>" . basename($path) . "</div>
                                    </div>
                                </div>
                            ";
                            })->join('');

                            $html .= "
                            <div class='mb-6'>
                                <h3 class='text-lg font-medium mb-3 flex items-center'>
                                    <svg class='w-5 h-5 mr-2' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                                        <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z'></path>
                                    </svg>
                                    $imagesTranslatable
                                </h3>
                                <div class='grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4'>
                                    {$imagesHtml}
                                </div>
                            </div>
                        ";
                        }

                        // Section Audio si présent
                        if ($audio->isNotEmpty()) {
                            $audioHtml = $audio->map(function ($path) {
                                return "
                                <div class='bg-gray-50 dark:bg-gray-700 rounded-lg shadow-sm p-4'>
                                    <div class='flex items-center space-x-3 mb-2'>
                                        <svg class='w-8 h-8 text-primary-500' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                                            <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3'></path>
                                        </svg>
                                        <div class='flex-1 flex-col justify-center items-center space-y-4'>
                                            <audio controls class='w-full'>
                                                <source src='/storage/{$path}' type='audio/mpeg'>
                                                Votre navigateur ne supporte pas l'élément audio.
                                            </audio>
                                            <div class='text-sm font-medium mb-1'>" . basename($path) . "</div>
                                        </div>
                                    </div>
                                </div>
                            ";
                            })->join('');

                            $html .= "
                            <div class='mb-6'>
                                <h3 class='text-lg font-medium mb-3 flex items-center'>
                                    <svg class='w-5 h-5 mr-2' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                                        <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M15.536 8.464a5 5 0 010 7.072M12 9.64a3 3 0 010 4.72m-3.536-1.464a5 5 0 010-7.072M12 6.36a3 3 0 010-4.72'></path>
                                    </svg>
                                    $audioTranslatable
                                </h3>
                                <div class='grid grid-cols-1 md:grid-cols-3 gap-4'>
                                    {$audioHtml}
                                </div>
                            </div>
                        ";
                        }

                        return new HtmlString($html);
                    }),

                // Documents section
                TextEntry::make('documents')
                    ->label(__('filament.pages.experiment_details.documents_section.title'))
                    ->columnSpanFull()
                    ->visible(fn($record) => !empty($record->documents))
                    ->formatStateUsing(function ($state) {
                        $documents = is_string($state) ? explode(',', $state) : $state;
                        $documents = collect($documents)->map(fn($path) => trim($path));

                        $documentsHtml = $documents->map(function ($path) {
                            $isPDF = str_contains($path, '.pdf');
                            $icon = $isPDF ?
                                '<svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>' :
                                '<svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>';

                            return "
                            <div class='bg-gray-50 dark:bg-gray-700 rounded-lg shadow-sm p-4'>
                                <div class='flex items-center space-x-3'>
                                    {$icon}
                                    <div class='flex-1'>
                                        <a href='/storage/{$path}' target='_blank'
                                           class='text-primary-600 hover:text-primary-500 font-medium'>
                                            " . basename($path) . "
                                        </a>
                                        <div class='text-xs text-gray-500 mt-1'>
                                            " . strtoupper(pathinfo($path, PATHINFO_EXTENSION)) . "
                                        </div>
                                    </div>
                                </div>
                            </div>
                        ";
                        })->join('');

                        return new HtmlString("
                        <div class='grid grid-cols-1 md:grid-cols-3 gap-4'>
                            {$documentsHtml}
                        </div>
                    ");
                    }),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('banUser')
                ->label(__('filament.pages.experiment_details.ban_action.label'))
                ->color('danger')
                ->icon('heroicon-o-user')
                ->hidden(
                    fn() =>
                    User::find($this->record->created_by)->hasRole('supervisor')
                )
                ->requiresConfirmation()
                ->form([
                    \Filament\Forms\Components\Textarea::make('banned_reason')
                        ->label(__('filament.pages.experiment_details.ban_action.reason'))
                        ->required()
                        ->minLength(10)
                        ->helperText(__('filament.pages.experiment_details.ban_action.helper')),
                ])
                ->modalHeading(__('filament.pages.experiment_details.ban_action.modalHeading'))
                ->modalDescription(__('filament.pages.experiment_details.ban_action.modalDescription'))
                ->visible(fn() => auth()->user()->hasRole('supervisor'))
                ->action(function (array $data) {
                    // Récupérer l'utilisateur qui a créé cette expérimentation
                    $user = User::find($this->record->created_by);

                    // Mettre à jour son statut et la raison
                    $user->status = 'banned';
                    $user->banned_reason = $data['banned_reason'];
                    $user->save();

                    // Envoyer la notification
                    $user->notify(new UserBanned($data['banned_reason']));

                    Notification::make()
                        ->title(__('filament.pages.experiment_details.notification.banned'))
                        ->success()
                        ->send();

                    // Rediriger vers la liste des expérimentations
                    $this->redirect(ExperimentsList::getUrl());
                }),
            \Filament\Actions\Action::make('contactUs')
                ->label(__('filament.pages.experiment_details.action.contact'))
                ->color('info')
                ->icon('heroicon-o-envelope')
                ->url("/admin/contact-user?user={$this->record->created_by}&experiment={$this->record->id}")
                ->hidden(fn() => User::find($this->record->created_by)->hasRole('supervisor'))
        ];
    }
}
