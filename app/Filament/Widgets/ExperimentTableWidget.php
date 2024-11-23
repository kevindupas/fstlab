<?php

namespace App\Filament\Widgets;

use App\Models\Experiment;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExperimentTableWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(function (): Builder {
                // Requête de base avec les colonnes sélectionnées explicitement
                $createdExperiments = Experiment::select([
                    'experiments.*',
                    DB::raw('(SELECT COUNT(*) FROM experiment_sessions WHERE experiments.id = experiment_sessions.experiment_id) as sessions_count')
                ])->where('created_by', Auth::id());

                // Requête pour les expériences accessibles avec les mêmes colonnes
                $accessibleExperiments = Experiment::select([
                    'experiments.*',
                    DB::raw('(SELECT COUNT(*) FROM experiment_sessions WHERE experiments.id = experiment_sessions.experiment_id) as sessions_count')
                ])->whereHas('accessRequests', function ($query) {
                    $query->where('user_id', Auth::id())
                        ->where('type', 'results')
                        ->where('status', 'approved');
                });

                // Union des deux requêtes
                return $createdExperiments->union($accessibleExperiments);
            })
            ->defaultPaginationPageOption(5)
            ->defaultSort('created_at', 'desc')
            ->columns([
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
                        if ($record->created_by === Auth::id()) {
                            return 'Créateur';
                        }
                        return 'Observateur';
                    })
                    ->badge()
                    ->color(fn(string $state): string => $state === 'Créateur' ? 'success' : 'info'),
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
        $user = Auth::user();

        $hasCreatedExperiments = Experiment::where('created_by', $user->id)->exists();

        $hasAccessToResults = Experiment::whereHas('accessRequests', function ($query) use ($user) {
            $query->where('user_id', $user->id)
                ->where('type', 'results')
                ->where('status', 'approved');
        })->exists();

        return $hasCreatedExperiments || $hasAccessToResults;
    }
}
