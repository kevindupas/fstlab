<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\Experiments\Details\ExperimentDetails;
use App\Filament\Pages\Experiments\Sessions\ExperimentSessions;
use App\Filament\Pages\Experiments\Statistics\ExperimentStatistics;
use App\Models\ExperimentAccessRequest;
use App\Models\ExperimentLink;
use App\Models\User;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class ExperimentAccessRequestsWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public function getTableHeading(): string
    {
        return __('filament.widgets.access_requests.heading');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ExperimentAccessRequest::query()
                    ->where('user_id', Auth::id())
                    ->whereIn('status', ['pending', 'approved'])
                    ->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('experiment.name')
                    ->label(__('filament.widgets.access_requests.column.name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('experiment.creator.name')
                    ->label(__('filament.widgets.access_requests.column.created_by'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'results' => __('filament.widgets.access_requests.column.results'),
                        'pass' => __('filament.widgets.access_requests.column.pass'),
                        default => $state
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('filament.widgets.access_requests.column.status'))
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pending' => __('filament.widgets.access_requests.column.pending'),
                        'approved' => __('filament.widgets.access_requests.column.approved'),
                        default => $state
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('filament.widgets.access_requests.column.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                // Actions uniquement visibles pour les demandes approuvées
                Tables\Actions\ActionGroup::make([

                    Tables\Actions\Action::make('manageExperiment')
                        ->label(__('filament.resources.borrowed_experiment.table.actions.sessions'))
                        ->icon('heroicon-o-play')
                        ->color('success')
                        ->visible(fn($record) => $record->type === 'access')
                        ->modalWidth('xl')
                        ->form([
                            TextInput::make('link')
                                ->label(__('filament.resources.my_experiment.actions.session_link'))
                                ->disabled(true)
                                // ->visible(fn($get) => $get('experimentStatus') !== 'stop')
                                ->reactive()
                                ->default(function ($record) {
                                    $experimentLink = \App\Models\ExperimentLink::where('experiment_id', $record->experiment_id)
                                        ->where('user_id', Auth::id())
                                        ->first();

                                    return $experimentLink && $experimentLink->link
                                        ? url("/experiment/{$experimentLink->link}")
                                        : 'No active session';
                                }),

                            ToggleButtons::make('experimentStatus')
                                ->options([
                                    'start' => __('filament.resources.my_experiment.actions.status.start'),
                                    'pause' => __('filament.resources.my_experiment.actions.status.pause'),
                                    'stop' => __('filament.resources.my_experiment.actions.status.stop'),
                                    'test' => __('filament.resources.my_experiment.actions.status.test'),
                                ])
                                ->colors([
                                    'start' => 'success',
                                    'pause' => 'warning',
                                    'stop' => 'danger',
                                    'test' => 'info',
                                ])
                                ->icons([
                                    'start' => 'heroicon-o-play',
                                    'pause' => 'heroicon-o-pause',
                                    'stop' => 'heroicon-o-stop',
                                    'test' => 'heroicon-o-beaker',
                                ])
                                ->default(function ($record) {
                                    $experimentLink = \App\Models\ExperimentLink::where('experiment_id', $record->experiment_id)
                                        ->where('user_id', Auth::id())
                                        ->first();
                                    return $experimentLink ? $experimentLink->status : 'stop';
                                })
                                ->reactive()
                                ->afterStateUpdated(function ($state, $set, $record) {
                                    // Récupérer le lien existant
                                    $existingLink = \App\Models\ExperimentLink::where('experiment_id', $record->experiment_id)  // Ici
                                        ->where('user_id', Auth::id())
                                        ->first();

                                    // Gestion du lien d'expérimentation selon l'état
                                    $linkValue = match ($state) {
                                        'start' => $existingLink?->link ?? Str::random(6),
                                        'pause' => $existingLink?->link ?? Str::random(6),
                                        'test' => Str::random(6),
                                        'stop' => null,
                                        default => null,
                                    };

                                    $experimentLink = ExperimentLink::updateOrCreate(
                                        [
                                            'experiment_id' => $record->experiment_id,
                                            'user_id' => Auth::id(),
                                        ],
                                        [
                                            'status' => $state,
                                            'link' => $linkValue,
                                            'is_creator' => false,
                                            'is_secondary' => false,
                                            'is_collaborator' => true
                                        ]
                                    );

                                    // Mise à jour de l'affichage
                                    if ($experimentLink->link) {
                                        $set('link', url("/experiment/{$experimentLink->link}"));
                                    } else {
                                        $set('link', 'No active session');
                                    }

                                    // Gestion du howitwork_page
                                    if ($state !== 'test') {
                                        $record->experiment->howitwork_page = false;  // Correction ici aussi
                                        $record->experiment->save();
                                    }
                                }),
                            Placeholder::make('Informations')
                                ->content(new HtmlString(
                                    '<div>' . Blade::render('<x-heroicon-o-play class="inline-block w-5 h-5 mr-2 text-green-500" />') .
                                        ' <strong>' . __('filament.resources.my_experiment.actions.status.start') . ':</strong> ' .
                                        __('filament.resources.my_experiment.actions.status.start_desc') . '</div><br>' .

                                        '<div>' . Blade::render('<x-heroicon-o-pause class="inline-block w-5 h-5 mr-2 text-yellow-500" />') .
                                        ' <strong>' . __('filament.resources.my_experiment.actions.status.pause') . ':</strong> ' .
                                        __('filament.resources.my_experiment.actions.status.pause_desc') . '</div><br>' .

                                        '<div>' . Blade::render('<x-heroicon-o-stop class="inline-block w-5 h-5 mr-2 text-red-500" />') .
                                        ' <strong>' . __('filament.resources.my_experiment.actions.status.stop') . ':</strong> ' .
                                        __('filament.resources.my_experiment.actions.status.stop_desc') . '</div><br>' .

                                        '<div>' . Blade::render('<x-heroicon-o-beaker class="inline-block w-5 h-5 mr-2 text-blue-500" />') .
                                        ' <strong>' . __('filament.resources.my_experiment.actions.status.test') . ':</strong> ' .
                                        __('filament.resources.my_experiment.actions.status.test_desc') . '</div>'
                                ))
                                ->columnSpan('full'),
                        ])
                        ->action(function ($data, $record) {
                            Notification::make()
                                ->title(__('filament.resources.my_experiment.notifications.session_updated'))
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\Action::make('viewResults')
                        ->label(__('filament.resources.borrowed_experiment.table.actions.results'))
                        ->color('info')
                        ->icon('heroicon-o-eye')
                        ->url(fn($record) => route('filament.admin.resources.experiment-sessions.index', ['record' => $record->experiment_id])),


                    Tables\Actions\Action::make('viewStatistics')
                        ->label(__('filament.resources.borrowed_experiment.table.actions.statistics'))
                        ->color('success')
                        ->icon('heroicon-o-chart-pie')
                        ->url(fn($record) => ExperimentStatistics::getUrl(['record' => $record->experiment])),

                    Tables\Actions\Action::make('viewDetails')
                        ->label(__('filament.widgets.experiment_table.actions.details'))
                        ->icon('heroicon-o-document-text')
                        ->visible(fn($record) => $record->type === 'access')
                        ->url(fn($record) => ExperimentDetails::getUrl(['record' => $record->experiment])),
                ])
                    ->visible(fn($record) => $record->status === 'approved')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->color('gray')
                    ->button()
                    ->label(__('filament.widgets.access_requests.column.actions'))
            ])
            ->defaultPaginationPageOption(5)
            ->defaultSort('created_at', 'desc');
    }

    public static function canView(): bool
    {
        /** @var \App\Models\User */
        $user = Auth::user();

        // Ne pas afficher ce widget si l'utilisateur est banni
        if ($user->status === 'banned') {
            return false;
        }

        // Ne pas afficher si c'est un secondary_experimenter dont le principal est banni
        if ($user->hasRole('secondary_experimenter')) {
            $principal = User::find($user->created_by);
            if ($principal && $principal->status === 'banned') {
                return false;
            }
        }

        return ExperimentAccessRequest::query()
            ->where('user_id', Auth::id())
            ->whereIn('status', ['pending', 'approved'])
            ->exists();
    }
}
