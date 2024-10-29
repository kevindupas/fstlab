<?php

namespace App\Filament\Resources\ExperimentResource\Pages;

use App\Filament\Resources\ExperimentResource;
use App\Models\Experiment;
use App\Models\ExperimentSession;
use Filament\Resources\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class ExperimentSessions extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = ExperimentResource::class;

    protected static string $view = 'filament.resources.experiment-resource.pages.experiment-sessions';

    public $experiment;

    public function mount($record): void
    {
        $this->experiment = Experiment::findOrFail($record);
    }

    public function table(Table $table): Table
    {
        /** @var \App\Models\User */
        $user = Auth::user();

        return $table
            ->query(
                ExperimentSession::query()
                    ->where('experiment_id', $this->experiment->id)
                    ->when(
                        $user->hasRole('principal_experimenter'),
                        fn($query) => $query->where('experiment_id', $this->experiment->id)
                            ->whereHas('experiment', fn($q) => $q->where('created_by', $user->id()))
                    )
            )
            ->columns([
                TextColumn::make('participant_name')
                    ->label('Nom du participant')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('participant_email')
                    ->label('Email du participant')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Date de participation')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->actions([
                Action::make('export')
                    ->label('Export JSON')
                    ->color('success')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(fn(ExperimentSession $record) => $this->exportJson($record)),

                Action::make('details')
                    ->label('Détails')
                    ->url(
                        fn(ExperimentSession $record): string =>
                        static::getResource()::getUrl('session-details', ['record' => $record->id])
                    )
                    ->icon('heroicon-o-eye'),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50, 100]);
    }

    public function exportJson(ExperimentSession $session)
    {
        $this->authorize('view', $session);

        $data = [
            'participant_name' => $session->participant_name,
            'participant_email' => $session->participant_email,
            'created_at' => $session->created_at,
            'group_data' => json_decode($session->group_data),
            'actions_log' => json_decode($session->actions_log),
            'duration' => $session->duration
        ];

        $filename = "session-{$session->id}-" . date('Y-m-d') . '.json';

        return response()->json($data)
            ->header('Content-Disposition', "attachment; filename={$filename}")
            ->header('Content-Type', 'application/json');
    }

    public function getTitle(): string | Htmlable
    {
        return new HtmlString('Participants pour l\'expérimentation' . ' : ' . $this->experiment->name);
    }
}
