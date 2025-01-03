<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\Experiments\Details\ExperimentDetails;

use App\Filament\Pages\Experiments\Statistics\ExperimentStatistics;
use App\Models\ExperimentAccessRequest;
use App\Models\ExperimentLink;
use App\Models\User;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Notifications\Notification;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class ExperimentAccessRequestsWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public function getTableHeading(): string
    {
        return __('widgets.access_requests.heading');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ExperimentAccessRequest::query()
                    ->select([
                        'experiment_access_requests.*',
                        DB::raw('(SELECT COUNT(*) FROM experiment_sessions WHERE experiment_sessions.experiment_id = experiment_access_requests.experiment_id) as sessions_count')
                    ])
                    ->where('user_id', Auth::id())
                    ->whereIn('status', ['pending', 'approved'])
                    ->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('experiment.name')
                    ->label(__('widgets.access_requests.column.name'))
                    ->words(3)
                    ->searchable(),
                Tables\Columns\TextColumn::make('experiment.creator.name')
                    ->label(__('widgets.access_requests.column.created_by'))
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
                Tables\Columns\TextColumn::make('sessions')
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
                    ->label(__('widgets.access_requests.column.type.label'))
                    ->badge()
                    ->colors([
                        'info' => 'access',
                        'warning' => 'results'
                    ])
                    ->formatStateUsing(fn($state): string => $state ? match ($state) {
                        'results' => __('widgets.access_requests.column.type.options.results'),
                        'access' => __('widgets.access_requests.column.type.options.access'),
                        default => $state
                    } : ''),
                Tables\Columns\TextColumn::make('sessions_count')
                    ->label(__('widgets.access_requests.column.sessions_count'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('widgets.access_requests.column.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('manageExperiment')
                        ->label(__('actions.manage_session.label'))
                        ->icon('heroicon-o-play')
                        ->color('success')
                        ->visible(fn($record) => $record->type === 'access')
                        ->modalWidth('xl')
                        ->form([
                            TextInput::make('link')
                                ->label(__('actions.manage_session.session_link'))
                                ->disabled(true)
                                ->reactive()
                                ->default(function ($record) {
                                    $experimentLink = \App\Models\ExperimentLink::where('experiment_id', $record->experiment_id)
                                        ->where('user_id', Auth::id())
                                        ->first();

                                    return $experimentLink && $experimentLink->link
                                        ? url("/experiment/{$experimentLink->link}")
                                        : __('actions.manage_session.no_session');
                                }),

                            ToggleButtons::make('experimentStatus')
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
                                    $experimentLink = \App\Models\ExperimentLink::where('experiment_id', $record->experiment_id)
                                        ->where('user_id', Auth::id())
                                        ->first();
                                    return $experimentLink ? $experimentLink->status : 'stop';
                                })
                                ->reactive()
                                ->afterStateUpdated(function ($state, $set, $record) {
                                    // Récupérer le lien existant
                                    $existingLink = \App\Models\ExperimentLink::where('experiment_id', $record->experiment_id)
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
                                        $set('link', __('actions.manage_session.no_session'));
                                    }

                                    // Gestion du howitwork_page
                                    if ($state !== 'test') {
                                        $record->experiment->howitwork_page = false;
                                        $record->experiment->save();
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
                        ->action(function ($data, $record) {
                            Notification::make()
                                ->title(__('actions.manage_session.success'))
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\Action::make('viewResults')
                        ->label(__('actions.results'))
                        ->color('info')
                        ->icon('heroicon-o-eye')
                        ->url(fn($record) => route('filament.admin.resources.experiment-sessions.index', ['record' => $record->experiment_id])),


                    Tables\Actions\Action::make('viewStatistics')
                        ->label(__('actions.statistics'))
                        ->color('success')
                        ->icon('heroicon-o-chart-pie')
                        ->url(fn($record) => ExperimentStatistics::getUrl(['record' => $record->experiment])),

                    Tables\Actions\Action::make('viewDetails')
                        ->label(__('actions.details_experiment'))
                        ->icon('heroicon-o-document-text')
                        ->visible(fn($record) => $record->type === 'access')
                        ->url(fn($record) => ExperimentDetails::getUrl(['record' => $record->experiment])),
                ])
                    ->visible(fn($record) => $record->status === 'approved')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->dropdownWidth(MaxWidth::ExtraSmall)
                    ->color('gray')
                    ->button()
                    ->label(__('actions.actions'))
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
