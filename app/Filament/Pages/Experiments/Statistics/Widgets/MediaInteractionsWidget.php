<?php

namespace App\Filament\Pages\Experiments\Statistics\Widgets;

use App\Models\Experiment;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class MediaInteractionsWidget extends Widget
{
    protected static string $view = 'filament.pages.experiments.statistics.widgets.media-interactions-widget';

    public Experiment $record;
    protected int | string | array $columnSpan = 'full';

    protected function getMediaStats(): Collection
    {
        $sessions = $this->record->sessions()
            ->whereNotNull('actions_log')
            ->whereNotNull('group_data')
            ->get();

        $mediaStats = collect();
        $groupCreations = collect();

        foreach ($sessions as $session) {
            $actions = json_decode($session->actions_log, true);
            $groups = json_decode($session->group_data, true);

            // On collecte d'abord les créations de groupes
            foreach ($actions as $action) {
                if ($action['type'] === 'group_created') {
                    $groupCreations->push([
                        'name' => $action['group_name'],
                        'color' => $action['group_color'],
                        'time' => $action['time']
                    ]);
                }
            }

            foreach ($actions as $action) {
                if (!isset($action['id']) && !isset($action['item_id'])) continue;

                // On gère les deux cas possibles pour l'ID
                $mediaId = $action['id'] ?? $action['item_id'];

                $stats = $mediaStats->get($mediaId, [
                    'name' => $mediaId,
                    'type' => $this->determineMediaType($mediaId),
                    'view_count' => 0,
                    'move_count' => 0,
                    'group_changes' => 0,
                    'groups' => [],
                    'total_interactions' => 0,
                    'groups_history' => [],
                    'created_groups' => $groupCreations->pluck('name')->toArray()
                ]);

                switch ($action['type']) {
                    case 'sound':
                    case 'image':
                        $stats['view_count']++;
                        $stats['total_interactions']++;
                        break;
                    case 'move':
                        $stats['move_count']++;
                        $stats['total_interactions']++;
                        break;
                    case 'item_moved_between_groups':
                        $stats['group_changes']++;
                        $stats['total_interactions']++;
                        $stats['groups_history'][] = [
                            'from' => $action['from_group'],
                            'to' => $action['to_group'],
                            'time' => $action['time']
                        ];
                        break;
                }

                $mediaStats->put($mediaId, $stats);
            }
        }

        return $mediaStats;
    }

    private function determineMediaType(string $path): string
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return match ($extension) {
            'wav', 'mp3', 'ogg' => 'son',
            'jpg', 'jpeg', 'png', 'gif', 'webp' => 'image',
            default => 'autre'
        };
    }

    protected function getViewData(): array
    {
        return [
            'mediaStats' => $this->getMediaStats()
        ];
    }
}
