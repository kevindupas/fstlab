<?php

namespace App\Filament\Resources;

use App\Filament\Pages\Experiments\Details\ExperimentDetails;
use App\Filament\Pages\Experiments\Sessions\ExperimentSessions;
use App\Filament\Pages\Experiments\Statistics\ExperimentStatistics;
use App\Filament\Resources\BorrowedExperimentsResource\Pages;
use App\Models\Experiment;
use App\Models\ExperimentAccessRequest;
use App\Models\ExperimentLink;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Placeholder;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class BorrowedExperimentsResource extends Resource
{
    protected static ?string $model = ExperimentAccessRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-cloud-arrow-down';
    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): string
    {
        return __('navigation.group.experiments');
    }

    public static function getNavigationLabel(): string
    {
        return __('navigation.borrowed_experiments');
    }

    public static function getModelLabel(): string
    {
        return __('navigation.borrowed_experiments');
    }

    public static function getPluralModelLabel(): string
    {
        return __('navigation.borrowed_experiments');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->select([
                'experiment_access_requests.*',
                DB::raw('(SELECT COUNT(*) FROM experiment_sessions WHERE experiment_sessions.experiment_id = experiment_access_requests.experiment_id) as sessions_count')
            ])
            ->where('status', 'approved')
            ->where('user_id', Auth::id())
            ->with(['experiment', 'experiment.creator', 'experiment.links']);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('experiment.name')
                    ->label(__('filament.resources.borrowed_experiment.table.columns.experiment'))
                    ->searchable()
                    ->lineClamp(2)
                    ->words(3)
                    ->sortable(),
                Tables\Columns\TextColumn::make('experiment.creator.name')
                    ->label(__('filament.resources.borrowed_experiment.table.columns.created_by'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('experiment.type')
                    ->label(__('filament.resources.borrowed_experiment.table.columns.type_experiments.label'))
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'sound' => __('filament.resources.borrowed_experiment.table.columns.type_experiments.options.sound'),
                        'image' => __('filament.resources.borrowed_experiment.table.columns.type_experiments.options.image'),
                        'image_sound' => __('filament.resources.borrowed_experiment.table.columns.type_experiments.options.image_sound'),
                        default => $state
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'sound' => 'success',
                        'image' => 'info',
                        'image_sound' => 'warning',
                        default => 'gray'
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('filament.resources.borrowed_experiment.table.columns.status.label'))
                    ->badge()
                    ->getStateUsing(function ($record) {
                        $experimentLink = $record->experiment->links()
                            ->where('user_id', $record->type === 'access' ? Auth::id() : $record->experiment->created_by)
                            ->first();
                        return $experimentLink ? $experimentLink->status : 'stop';
                    })
                    ->formatStateUsing(fn($state): string => $state ? match ($state) {
                        'start' => __('filament.resources.borrowed_experiment.table.columns.status.options.start'),
                        'pause' => __('filament.resources.borrowed_experiment.table.columns.status.options.pause'),
                        'stop' => __('filament.resources.borrowed_experiment.table.columns.status.options.stop'),
                        'test' => __('filament.resources.borrowed_experiment.table.columns.status.options.test'),
                        default => $state
                    } : '')
                    ->color(fn($state): string => $state ? match ($state) {
                        'start' => 'success',
                        'pause' => 'warning',
                        'stop' => 'danger',
                        'test' => 'info',
                        default => 'secondary'
                    } : 'secondary'),
                Tables\Columns\TextColumn::make('type')
                    ->label(__('filament.resources.borrowed_experiment.table.columns.type_access.label'))
                    ->badge()
                    ->colors([
                        'info' => 'access',
                        'warning' => 'results'
                    ])
                    ->formatStateUsing(fn($state): string => $state ? match ($state) {
                        'results' => __('filament.resources.borrowed_experiment.table.columns.type_access.results'),
                        'access' => __('filament.resources.borrowed_experiment.table.columns.type_access.access'),
                        default => $state
                    } : ''),
                Tables\Columns\TextColumn::make('sessions_count')
                    ->label(__('filament.widgets.experiment_table.column.sessions_count'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('filament.resources.borrowed_experiment.table.columns.access_granted_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('manageExperiment')
                        ->label(__('filament.resources.borrowed_experiment.table.actions.sessions'))
                        ->icon('heroicon-o-play')
                        ->color('success')
                        ->visible(fn($record) => $record->type === 'access')
                        ->modalWidth('xl')
                        ->form([
                            Forms\Components\TextInput::make('link')
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

                            Forms\Components\ToggleButtons::make('experimentStatus')
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
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->color('gray')
                    ->button()
                    ->label('Actions')
            ]);
    }

    public static function shouldRegisterNavigation(): bool
    {
        /** @var \App\Models\User */
        $user = Auth::user();

        // Vérifie si l'utilisateur ou son principal est banni
        if ($user->status === 'banned') {
            abort(403, __('filament.resources.experiment-access-request.message.banned'));
        }

        if ($user->hasRole('secondary_experimenter')) {
            $principal = User::find($user->created_by);
            if ($principal && $principal->status === 'banned') {
                abort(403, __('filament.resources.experiment-access-request.message.banned_secondary'));
            }
        }

        return true;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBorrowedExperiments::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canAccess(): bool
    {
        /** @var \App\Models\User */
        $user = Auth::user();

        if ($user->hasRole('secondary_experimenter')) {
            return false;
        }

        return true;
    }

    // Et aussi pour bien s'assurer que même l'accès à la liste est bloqué
    // public static function canViewAny(): bool
    // {
    //     /** @var \App\Models\User */
    //     $user = Auth::user();

    //     if ($user->hasRole('secondary_experimenter')) {
    //         abort(403, __('filament.resources.borrowed_experiment.message.no_access_section'));
    //     }

    //     return true;
    // }
}
