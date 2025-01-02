<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\Experiments\Details\ExperimentDetails;
use App\Filament\Pages\Experiments\Sessions\ExperimentSessions;
use App\Filament\Pages\Experiments\Statistics\ExperimentStatistics;
use App\Models\Experiment;
use App\Models\ExperimentLink;
use App\Models\User;
use App\Traits\HasExperimentAccess;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Filament\Forms;
use Filament\Forms\Components\Placeholder;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class ExperimentTableWidget extends BaseWidget
{
    use HasExperimentAccess;
    protected int | string | array $columnSpan = 'full';

    protected static string $recordRouteKeyName = 'id';

    public static function getIdColumn(): string
    {
        return 'id';
    }

    public function table(Table $table): Table
    {
        /** @var \App\Models\User */
        $user = Auth::user();

        return $table
            ->query(function () use ($user): Builder {
                $query = Experiment::query()
                    ->select([
                        'experiments.*',
                        DB::raw('(SELECT COUNT(*) FROM experiment_sessions WHERE experiments.id = experiment_sessions.experiment_id) as sessions_count')
                    ])
                    ->with(['creator']);

                if ($user->hasRole('supervisor')) {
                    $query->where('created_by', $user->id);
                } elseif ($user->hasRole('principal_experimenter')) {
                    $secondaryIds = $user->createdUsers()
                        ->role('secondary_experimenter')
                        ->pluck('id');

                    $query->where(function ($q) use ($user, $secondaryIds) {
                        $q->where('created_by', $user->id)
                            ->orWhereIn('created_by', $secondaryIds);
                    });
                } elseif ($user->hasRole('secondary_experimenter')) {
                    // Pour les secondaires, on montre les expérimentations qui leur sont attribuées
                    $query->whereHas('users', function ($q) use ($user) {
                        $q->where('users.id', $user->id);
                    });
                } else {
                    $query->where(function ($q) use ($user) {
                        $q->where('created_by', $user->id)
                            ->orWhereHas('accessRequests', function ($aq) use ($user) {
                                $aq->where('user_id', $user->id)
                                    ->where('type', 'results')
                                    ->where('status', 'approved');
                            });
                    });
                }

                return $query;
            })
            ->defaultPaginationPageOption(5)
            ->defaultSort('created_at', 'desc')
            ->columns([
                // Colonne du créateur uniquement visible pour les supervisors
                // Tables\Columns\TextColumn::make('creator.name')
                //     ->label(__('filament.widgets.experiment_table.column.creator'))
                //     ->searchable()
                //     ->sortable()
                //     ->visible(fn() => $user->hasRole('supervisor')),

                Tables\Columns\TextColumn::make('name')
                    ->label(__('filament.widgets.experiment_table.column.name'))
                    ->searchable()
                    ->words(3)
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label(__('filament.widgets.experiment_table.column.type.label'))
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'sound' => __('filament.widgets.experiment_table.column.type.options.sound'),
                        'image' => __('filament.widgets.experiment_table.column.type.options.image'),
                        'image_sound' => __('filament.widgets.experiment_table.column.type.options.image_sound'),
                        default => $state
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'sound' => 'success',
                        'image' => 'info',
                        'image_sound' => 'warning',
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('filament.widgets.experiment_table.column.status'))
                    ->badge()
                    ->state(function ($record) {
                        $experimentLink = \App\Models\ExperimentLink::where('experiment_id', $record->id)
                            ->where('user_id', Auth::id())
                            ->first();
                        return $experimentLink ? $experimentLink->status : 'stop';
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'start' => __('filament.widgets.experiment_table.column.start'),
                        'pause' => __('filament.widgets.experiment_table.column.pause'),
                        'stop' => __('filament.widgets.experiment_table.column.stop'),
                        'test' => __('filament.widgets.experiment_table.column.test'),
                        default => $state
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'none' => 'gray',
                        'start' => 'success',
                        'pause' => 'warning',
                        'stop' => 'danger',
                        'test' => 'info',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('sessions_count')
                    ->label(__('filament.widgets.experiment_table.column.sessions_count'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('filament.widgets.experiment_table.column.created_at'))
                    ->date()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    // Action pour lancer une session
                    Tables\Actions\Action::make('manageExperiment')
                        ->label(__('filament.resources.my_experiment.actions.session'))
                        ->color('success')
                        ->icon('heroicon-o-play')
                        ->modalWidth('xl')
                        ->form([
                            Forms\Components\TextInput::make('link')
                                ->label(__('filament.resources.my_experiment.actions.session_link'))
                                ->disabled(true)
                                ->visible(fn($get) => $get('experimentStatus') !== 'stop')
                                ->reactive()
                                ->default(function ($record) {
                                    $experimentLink = \App\Models\ExperimentLink::where('experiment_id', $record->id)
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
                                    $experimentLink = \App\Models\ExperimentLink::where('experiment_id', $record->id)
                                        ->where('user_id', Auth::id())
                                        ->first();
                                    return $experimentLink ? $experimentLink->status : 'stop';
                                })
                                ->reactive()
                                ->afterStateUpdated(function ($state, $set, $record) {
                                    // Récupérer le lien existant
                                    $existingLink = \App\Models\ExperimentLink::where('experiment_id', $record->id)
                                        ->where('user_id', Auth::id())
                                        ->first();

                                    // Gestion du lien d'expérimentation selon l'état
                                    $linkValue = match ($state) {
                                        'start' => $existingLink?->link ?? Str::random(6), // Nouveau lien si pas de lien existant
                                        'pause' => $existingLink?->link ?? Str::random(6), // Garde le même lien
                                        'test' => Str::random(6), // Toujours un nouveau lien
                                        'stop' => null, // Pas de lien
                                        default => null,
                                    };

                                    // Mise à jour ou création du lien
                                    $user = Auth::user();
                                    $experiment = Experiment::find($record->id);

                                    $isCreator = $experiment->created_by === $user->id;
                                    $isSecondary = !is_null($user->created_by) && $user->created_by === $experiment->created_by;

                                    $experimentLink = ExperimentLink::updateOrCreate(
                                        [
                                            'experiment_id' => $record->id,
                                            'user_id' => Auth::id(),
                                        ],
                                        [
                                            'status' => $state,
                                            'link' => $linkValue,
                                            'is_creator' => $isCreator,
                                            'is_secondary' => $isSecondary,
                                            'is_collaborator' => !$isCreator && !$isSecondary
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
                                        $record->howitwork_page = false;
                                        $record->save();
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



                    // Action pour contacter le créateur
                    Tables\Actions\Action::make('contact_creator')
                        ->label(__('filament.widgets.experiment_table.actions.contact_creator'))
                        ->icon('heroicon-o-envelope')
                        ->url(
                            fn(Experiment $record): string =>
                            "/admin/contact-principal?experiment={$record->id}"
                        )
                        ->visible(fn() => $user->hasRole('secondary_experimenter')),

                    Tables\Actions\Action::make('details')
                        ->label(__('filament.widgets.experiment_table.actions.details'))
                        ->icon('heroicon-o-document-text')
                        ->url(fn(Experiment $record): string =>
                        ExperimentDetails::getUrl(['record' => $record])),

                    Tables\Actions\Action::make('results')
                        ->label(__('filament.widgets.experiment_table.actions.results'))
                        ->icon('heroicon-o-eye')
                        ->color('info')
                        ->url(
                            fn(Experiment $record): string =>
                            route('filament.admin.resources.experiment-sessions.index', ['record' => $record->id])
                        ),

                    Tables\Actions\Action::make('statistics')
                        ->label(__('filament.widgets.experiment_table.actions.statistics'))
                        ->icon('heroicon-o-chart-pie')
                        ->color('success')
                        ->url(
                            fn(Experiment $record): string =>
                            ExperimentStatistics::getUrl(['record' => $record])
                        ),
                    // Action pour modifier (si droits)
                    Tables\Actions\Action::make('edit')
                        ->label(__('filament.widgets.experiment_table.actions.edit'))
                        ->icon('heroicon-o-pencil')
                        ->color('warning')
                        ->url(
                            fn(Experiment $record): string =>
                            route('filament.admin.resources.my-experiments.edit', ['record' => $record])
                        )
                        ->visible(function (Experiment $record) {
                            /** @var \App\Models\User */
                            $user = Auth::user();
                            return $user->hasRole('supervisor') || $user->hasRole('principal_experimenter') || $user->hasRole('secondary_experimenter') &&
                                $record->users()
                                ->wherePivot('user_id', $user->id)
                                ->wherePivot('can_configure', true)
                                ->exists();
                        }),
                ])
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->color('gray')
                    ->button()
                    ->label('Actions')
            ]);
    }

    public function getTableHeading(): string
    {
        /** @var User */
        $user = Auth::user();

        return match (true) {
            $user->hasRole('supervisor') || $user->hasRole('principal_experimenter') => __('filament.widgets.experiment_table.title'),
            $user->hasRole('secondary_experimenter') => __('filament.widgets.experiment_table.title_secondary_experimenter'),
            default => __('filament.widgets.experiment_table.title_default')
        };
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

        return true;
    }
}
