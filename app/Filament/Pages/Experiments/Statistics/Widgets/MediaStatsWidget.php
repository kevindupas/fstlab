<?php

namespace App\Filament\Pages\Experiments\Statistics\Widgets;

use App\Models\Experiment;
use App\Models\ExperimentSession;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Storage;

class MediaStatsWidget extends BaseWidget
{
    protected static ?string $heading = 'Statistiques des médias';
    public Experiment $record;
    protected int | string | array $columnSpan = 'full';


    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Média')
                    ->formatStateUsing(fn($record) => basename($record['id']))
                    ->searchable(),

                TextColumn::make('type')
                    ->label('Type')
                    ->formatStateUsing(fn($record) => ucfirst($record['type'])),

                TextColumn::make('play_count')
                    ->label('Lectures/Vues')
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('move_count')
                    ->label('Déplacements')
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('group_changes_count')
                    ->label('Changements de groupe')
                    ->alignCenter()
                    ->sortable(),

                ViewColumn::make('group_changes')
                    ->label('Historique des groupes')
                    ->view('filament.tables.columns.group-changes-history'),

                TextColumn::make('avg_position')
                    ->label('Position moyenne')
                    ->formatStateUsing(fn($record) => sprintf('x: %.1f, y: %.1f', $record['avg_x'], $record['avg_y']))
                    ->alignCenter(),

                TextColumn::make('last_position')
                    ->label('Position finale')
                    ->formatStateUsing(fn($record) => sprintf('x: %.1f, y: %.1f', $record['last_x'], $record['last_y']))
                    ->alignCenter(),
            ])
            ->defaultSort('play_count', 'desc')
            ->striped();
    }

    protected function getData(): array
    {
        return $this->getMediaStats()->toArray();
    }


    private function getMediaStats()
    {
        $sessions = ExperimentSession::where('experiment_id', $this->record->id)
            ->whereNotNull('actions_log')
            ->get();

        $mediaStats = collect();

        foreach ($sessions as $session) {
            $actions = collect(json_decode($session->actions_log, true));
            $groupData = collect(json_decode($session->group_data, true));

            // Process each media
            foreach ($groupData->pluck('elements')->flatten(1) as $element) {
                $mediaId = $element['id'];
                $stats = $mediaStats->get($mediaId, [
                    'id' => $mediaId,
                    'type' => $element['type'],
                    'play_count' => 0,
                    'move_count' => 0,
                    'group_changes_count' => 0,
                    'group_changes' => [],
                    'positions' => [],
                    'last_x' => $element['x'],
                    'last_y' => $element['y'],
                ]);

                // Update stats based on actions
                $elementActions = $actions->filter(
                    fn($a) => ($a['type'] === 'move' || $a['type'] === 'sound' || $a['type'] === 'image') &&
                        $a['id'] === $mediaId
                );

                foreach ($elementActions as $action) {
                    if ($action['type'] === 'move') {
                        $stats['move_count']++;
                        $stats['positions'][] = [
                            'x' => $action['x'],
                            'y' => $action['y']
                        ];
                    } elseif ($action['type'] === 'sound' || $action['type'] === 'image') {
                        $stats['play_count']++;
                    }
                }

                // Process group changes
                $groupChanges = $actions->filter(
                    fn($a) =>
                    $a['type'] === 'item_moved_between_groups' &&
                        $a['item_id'] === $mediaId
                );

                foreach ($groupChanges as $change) {
                    $stats['group_changes_count']++;
                    $stats['group_changes'][] = [
                        'from' => $change['from_group'],
                        'to' => $change['to_group'],
                        'time' => $change['time']
                    ];
                }

                // Calculate averages
                if (!empty($stats['positions'])) {
                    $stats['avg_x'] = collect($stats['positions'])->avg('x');
                    $stats['avg_y'] = collect($stats['positions'])->avg('y');
                } else {
                    $stats['avg_x'] = $stats['last_x'];
                    $stats['avg_y'] = $stats['last_y'];
                }

                $mediaStats[$mediaId] = $stats;
            }
        }

        return $mediaStats->values();
    }
}
