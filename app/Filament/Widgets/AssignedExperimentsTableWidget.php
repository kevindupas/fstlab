<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\Experiments\Details\ExperimentDetails;
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
use Filament\Forms;
use Filament\Forms\Components\Placeholder;
use Filament\Notifications\Notification;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class AssignedExperimentsTableWidget extends BaseWidget
{
    use HasExperimentAccess;
    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 1;

    public function table(Table $table): Table
    {
        /** @var \App\Models\User */
        $user = Auth::user();

        return $table
            ->query(function () use ($user): Builder {
                return Experiment::query()
                    ->select([
                        'experiments.*',
                        DB::raw('(SELECT COUNT(*) FROM experiment_sessions WHERE experiments.id = experiment_sessions.experiment_id) as sessions_count')
                    ])
                    ->with(['creator'])
                    ->whereHas('users', function ($query) use ($user) {
                        $query->where('users.id', $user->id);
                    })
                    ->whereDoesntHave('creator', function ($query) use ($user) {
                        // Exclure les expériences où l'utilisateur est le créateur
                        $query->where('users.id', $user->id);
                    });
            })
            ->defaultPaginationPageOption(5)
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('widgets.experiment_table.column.name'))
                    ->searchable()
                    ->words(3)
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label(__('widgets.experiment_table.column.type.label'))
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'sound' => __('widgets.experiment_table.column.type.options.sound'),
                        'image' => __('widgets.experiment_table.column.type.options.image'),
                        'image_sound' => __('widgets.experiment_table.column.type.options.image_sound'),
                        default => $state
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'sound' => 'success',
                        'image' => 'info',
                        'image_sound' => 'warning',
                    }),

                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Créateur')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('widgets.experiment_table.column.status.label'))
                    ->badge()
                    ->state(function ($record) {
                        $experimentLink = ExperimentLink::where('experiment_id', $record->id)
                            ->where('user_id', Auth::id())
                            ->first();
                        return $experimentLink ? $experimentLink->status : 'stop';
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'start' => __('widgets.experiment_table.column.status.options.start'),
                        'pause' => __('widgets.experiment_table.column.status.options.pause'),
                        'stop' => __('widgets.experiment_table.column.status.options.stop'),
                        'test' => __('widgets.experiment_table.column.status.options.test'),
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
                    ->label(__('widgets.experiment_table.column.sessions_count'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('widgets.experiment_table.column.created_at'))
                    ->date()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    // Action pour lancer une session
                    Tables\Actions\Action::make('manageExperiment')
                        ->label(__('actions.manage_session.label'))
                        ->color('success')
                        ->icon('heroicon-o-play')
                        ->modalWidth('xl')
                        ->form([
                            Forms\Components\TextInput::make('link')
                                ->label(__('actions.manage_session.session_link'))
                                ->disabled(true)
                                ->visible(fn($get) => $get('experimentStatus') !== 'stop')
                                ->reactive()
                                ->default(function ($record) {
                                    $experimentLink = \App\Models\ExperimentLink::where('experiment_id', $record->id)
                                        ->where('user_id', Auth::id())
                                        ->first();

                                    return $experimentLink && $experimentLink->link
                                        ? url("/experiment/{$experimentLink->link}")
                                        : __('actions.manage_session.no_session');
                                }),

                            Forms\Components\ToggleButtons::make('experimentStatus')
                                ->options([
                                    'start' => __('actions.manage_session.options.start'),
                                    'pause' => __('actions.manage_session.options.pause'),
                                    'stop' => __('actions.manage_session.options.stop'),
                                    'test' => __('actions.manage_session.options.test'),
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

                                    $experimentLink = ExperimentLink::updateOrCreate(
                                        [
                                            'experiment_id' => $record->id,
                                            'user_id' => Auth::id(),
                                        ],
                                        [
                                            'status' => $state,
                                            'link' => $linkValue,
                                            'is_creator' => false,
                                            'is_secondary' => true,
                                            'is_collaborator' => false
                                        ]
                                    );

                                    // Mise à jour de l'affichage
                                    if ($experimentLink->link) {
                                        $set('link', url("/experiment/{$experimentLink->link}"));
                                    } else {
                                        $set('link', __('actions.manage_session.no_session'));
                                    }

                                    // Gestion du howitwork_page
                                    if ($state !== 'test') {
                                        $record->howitwork_page = false;
                                        $record->save();
                                    }
                                }),
                            Placeholder::make(__('actions.manage_session.information'))
                                ->content(new HtmlString(
                                    '<div>' . Blade::render('<x-heroicon-o-play class="inline-block w-5 h-5 mr-2 text-green-500" />') .
                                        ' <strong>' . __('actions.manage_session.options.start') . ':</strong> ' .
                                        __('actions.manage_session.start_desc') . '</div><br>' .

                                        '<div>' . Blade::render('<x-heroicon-o-pause class="inline-block w-5 h-5 mr-2 text-yellow-500" />') .
                                        ' <strong>' . __('actions.manage_session.options.pause') . ':</strong> ' .
                                        __('actions.manage_session.pause_desc') . '</div><br>' .

                                        '<div>' . Blade::render('<x-heroicon-o-stop class="inline-block w-5 h-5 mr-2 text-red-500" />') .
                                        ' <strong>' . __('actions.manage_session.options.stop') . ':</strong> ' .
                                        __('actions.manage_session.stop_desc') . '</div><br>' .

                                        '<div>' . Blade::render('<x-heroicon-o-beaker class="inline-block w-5 h-5 mr-2 text-blue-500" />') .
                                        ' <strong>' . __('actions.manage_session.options.test') . ':</strong> ' .
                                        __('actions.manage_session.test_desc') . '</div>'

                                ))
                                ->columnSpan('full'),
                        ])
                        ->visible(
                            fn(Experiment $record) =>
                            $record->users()->where('users.id', Auth::id())
                                ->wherePivot('can_pass', true)
                                ->exists()
                        )
                        ->action(function ($data, $record) {
                            Notification::make()
                                ->title(__('actions.manage_session.success'))
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\Action::make('viewStatistics')
                        ->label(__('actions.statistics'))
                        ->color('success')
                        ->icon('heroicon-o-chart-pie')
                        ->url(fn(Experiment $record): string =>
                        ExperimentStatistics::getUrl(['record' => $record->id])),


                    Tables\Actions\Action::make('details')
                        ->label(__('actions.details_experiment'))
                        ->icon('heroicon-o-document-text')
                        ->url(fn(Experiment $record): string =>
                        ExperimentDetails::getUrl(['record' => $record->id])),

                    Tables\Actions\Action::make('results')
                        ->label(__('actions.results'))
                        ->icon('heroicon-o-eye')
                        ->color('info')
                        ->url(fn(Experiment $record): string =>
                        route('filament.admin.resources.experiment-sessions.index', ['record' => $record->id])),

                    Tables\Actions\Action::make('edit')
                        ->label(__('actions.edit_experiment'))
                        ->icon('heroicon-o-pencil')
                        ->color('warning')
                        ->url(fn(Experiment $record): string =>
                        route('filament.admin.resources.my-experiments.edit', ['record' => $record]))
                        ->visible(
                            fn(Experiment $record) =>
                            $record->users()->where('users.id', Auth::id())
                                ->wherePivot('can_configure', true)
                                ->exists()
                        ),
                ])
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->color('gray')
                    ->dropdownWidth(MaxWidth::ExtraSmall)
                    ->button()
                    ->label('Actions')
            ]);
    }

    public function getTableHeading(): string
    {
        return 'Expérimentations Attribuées';
    }

    public static function canView(): bool
    {
        /** @var \App\Models\User */
        $user = Auth::user();

        if ($user->status === 'banned' || !$user->hasRole('principal_experimenter')) {
            return false;
        }

        // Vérifier si l'utilisateur a des expérimentations attribuées
        return Experiment::whereHas('users', function ($query) use ($user) {
            $query->where('users.id', $user->id);
        })
            ->whereDoesntHave('creator', function ($query) use ($user) {
                $query->where('users.id', $user->id);
            })
            ->exists();
    }
}
