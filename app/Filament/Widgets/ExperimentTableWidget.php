<?php

namespace App\Filament\Widgets;

use App\Models\Experiment;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ExperimentTableWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(Experiment::query())
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
                    ->counts('sessions')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date de création')
                    ->date()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('open')
                    ->label('Détails')
                    ->url(fn(Experiment $record): string => route('filament.admin.resources.experiments.sessions', ['record' => $record->id])),
            ]);
    }
}
