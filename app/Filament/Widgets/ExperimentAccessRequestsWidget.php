<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\Experiments\Sessions\ExperimentSessions;
use App\Filament\Pages\Experiments\Statistics\ExperimentStatistics;
use App\Models\ExperimentAccessRequest;
use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

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
                    Tables\Actions\Action::make('statistics')
                        ->label(__('filament.widgets.access_requests.column.statistics'))
                        ->icon('heroicon-o-chart-pie')
                        ->color('success')
                        ->url(fn($record) => ExperimentStatistics::getUrl(['record' => $record->experiment]))
                        ->visible(fn($record) => $record->status === 'approved'),

                    Tables\Actions\Action::make('sessions')
                        ->label(__('filament.widgets.access_requests.column.results'))
                        ->icon('heroicon-o-eye')
                        ->url(fn($record) => ExperimentSessions::getUrl(['record' => $record->experiment]))
                        ->visible(fn($record) => $record->status === 'approved'),
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
