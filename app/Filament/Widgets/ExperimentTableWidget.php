<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\Experiments\Sessions\ExperimentSessions;
use App\Filament\Pages\Experiments\Statistics\ExperimentStatistics;
use App\Models\Experiment;
use App\Models\User;
use App\Traits\HasExperimentAccess;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
                Tables\Columns\TextColumn::make('creator.name')
                    ->label(__('filament.widgets.experiment_table.column.creator'))
                    ->searchable()
                    ->sortable()
                    ->visible(fn() => $user->hasRole('supervisor')),

                Tables\Columns\TextColumn::make('name')
                    ->label(__('filament.widgets.experiment_table.column.name'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('filament.widgets.experiment_table.column.status'))
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'start' => __('filament.widgets.experiment_table.column.start'),
                        'pause' => __('filament.widgets.experiment_table.column.pause'),
                        'stop' => __('filament.widgets.experiment_table.column.stop'),
                        'test' => __('filament.widgets.experiment_table.column.test'),
                        default => $state
                    })
                    ->colors([
                        'success' => 'start',
                        'warning' => 'pause',
                        'danger' => 'stop',
                        'info' => 'test'
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('sessions_count')
                    ->label(__('filament.widgets.experiment_table.column.sessions_count'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('filament.widgets.experiment_table.column.created_at'))
                    ->date()
                    ->sortable(),

                // Colonne du rôle uniquement visible pour les supervisors
//                Tables\Columns\TextColumn::make('user_role')
//                    ->label(__('filament.widgets.experiment_table.columns.user_role'))
//                    ->state(function (Experiment $record): string {
//                        /** @var \App\Models\User */
//                        $user = Auth::user();
//                        if ($user->hasRole('supervisor')) {
//                            return 'supervisor';
//                        }
//                        if ($record->created_by === $user->id) {
//                            return 'creator';
//                        }
//                        if (
//                            $user->hasRole('principal_experimenter') &&
//                            $user->createdUsers()->where('id', $record->created_by)->exists()
//                        ) {
//                            return 'manager';
//                        }
//                        return 'observer';
//                    })
//                    ->formatStateUsing(fn(string $state) => __("filament.widgets.experiment_table.roles.$state"))
//                    ->badge()
//                    ->color(fn(string $state): string => match ($state) {
//                        'supervisor' => 'warning',
//                        'creator' => 'success',
//                        'manager' => 'primary',
//                        'observer' => 'info',
//                        default => 'gray'
//                    })
//                    ->visible(fn() => $user->hasRole('supervisor')),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('statistics')
                        ->label(__('filament.widgets.experiment_table.actions.statistics'))
                        ->color('success')
                        ->icon('heroicon-o-chart-pie')
                        ->url(function (Experiment $record) {
                            $url = ExperimentStatistics::getUrl(['record' => $record]);
                            Log::info('Statistics URL', [
                                'url' => $url,
                                'experiment_id' => $record->id
                            ]);
                            return $url;
                        }),
                    Tables\Actions\Action::make('details')
                        ->label(__('filament.widgets.experiment_table.actions.details'))
                        ->icon('heroicon-o-eye')
                        ->url(function (Experiment $record) {
                            $url = ExperimentSessions::getUrl(['record' => $record]);
                            Log::info('Details URL', [
                                'url' => $url,
                                'experiment_id' => $record->id
                            ]);
                            return $url;
                        })
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

        if ($user->hasRole('supervisor') || $user->hasRole('principal_experimenter')) {
            return 'Mes expérimentations';
        } else {
            return 'Expérimentations disponibles';
        }
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
