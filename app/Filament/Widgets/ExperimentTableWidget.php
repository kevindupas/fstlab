<?php

namespace App\Filament\Widgets;

use App\Models\Experiment;
use App\Models\User;
use App\Traits\HasExperimentAccess;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExperimentTableWidget extends BaseWidget
{
    use HasExperimentAccess;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        /** @var User */
        $user = Auth::user();

        return $table
            ->query(function () use ($user): Builder {
                $baseQuery = Experiment::select([
                    'experiments.*',
                    DB::raw('(SELECT COUNT(*) FROM experiment_sessions WHERE experiments.id = experiment_sessions.experiment_id) as sessions_count')
                ]);

                return $baseQuery->where(function ($query) use ($user) {
                    // Accès approuvés
                    $query->whereHas('accessRequests', function ($query) use ($user) {
                        $query->where('user_id', $user->id)
                            ->where('type', 'results')
                            ->where('status', 'approved');
                    });

                    if ($user->hasRole('supervisor')) {
                        $principalIds = $this->getPrincipalIds();
                        $secondaryIds = $this->getSecondaryIds($principalIds);

                        $query->orWhere(function ($q) use ($user, $principalIds, $secondaryIds) {
                            $q->where('created_by', $user->id)
                                ->orWhereIn('created_by', $principalIds)
                                ->orWhereIn('created_by', $secondaryIds);
                        });
                    } elseif ($user->hasRole('principal_experimenter')) {
                        $query->orWhere('created_by', $user->id)
                            ->orWhereIn(
                                'created_by',
                                $user->createdUsers()->role('secondary_experimenter')->pluck('id')
                            );
                    } else {
                        $query->orWhere('created_by', $user->id);
                    }
                });

                return $query;
            })
            ->defaultPaginationPageOption(5)
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Créé par')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom de l\'expérimentation')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('État')
                    ->badge()
                    ->colors([
                        'success' => 'start',
                        'warning' => 'pause',
                        'danger' => 'stop',
                    ])
                    ->sortable(),
                Tables\Columns\TextColumn::make('sessions_count')
                    ->label('Nombre de participants')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date de création')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user_role')
                    ->label('Votre rôle')
                    ->state(function (Experiment $record): string {
                        /** @var \App\Models\User */
                        $user = Auth::user();
                        if ($user->hasRole('supervisor')) {
                            return 'supervisor';
                        }
                        if ($record->created_by === $user->id) {
                            return 'Créateur';
                        }
                        if (
                            $user->hasRole('principal_experimenter') &&
                            $user->createdUsers()->where('id', $record->created_by)->exists()
                        ) {
                            return 'Responsable';
                        }
                        return 'Observateur';
                    })
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'supervisor' => 'warning',
                        'Créateur' => 'success',
                        'Responsable' => 'primary',
                        'Observateur' => 'info',
                        default => 'gray'
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('statistics')
                    ->label('Statistiques')
                    ->color('success')
                    ->icon('heroicon-o-chart-pie')
                    ->url(fn(Experiment $record): string => route('filament.admin.resources.experiments.statistics', ['record' => $record->id])),
                Tables\Actions\Action::make('details')
                    ->label('Détails')
                    ->icon('heroicon-o-eye')
                    ->url(fn(Experiment $record): string => route('filament.admin.resources.experiments.sessions', ['record' => $record->id])),
            ]);
    }

    public static function canView(): bool
    {
        return true;
    }
}
