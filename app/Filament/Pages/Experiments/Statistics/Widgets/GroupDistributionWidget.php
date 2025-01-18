<?php

namespace App\Filament\Pages\Experiments\Statistics\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\ExperimentSession;

class GroupDistributionWidget extends ChartWidget
{
    protected static ?string $heading = 'Distribution des éléments par groupe';
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $sessions = ExperimentSession::where('experiment_id', $this->record->id)
            ->whereNotNull('group_data')
            ->get();

        $groupCounts = [];
        $totalInteractions = [];

        foreach ($sessions as $session) {
            $groups = json_decode($session->group_data, true);
            foreach ($groups as $group) {
                $name = $group['name'];
                if (!isset($groupCounts[$name])) {
                    $groupCounts[$name] = 0;
                    $totalInteractions[$name] = 0;
                }
                $groupCounts[$name] += count($group['elements']);
                foreach ($group['elements'] as $element) {
                    $totalInteractions[$name] += $element['interactions'];
                }
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Nombre d\'éléments',
                    'data' => array_values($groupCounts),
                    'backgroundColor' => ['#FF0000', '#00FF00', '#0000FF'],
                ],
                [
                    'label' => 'Nombre d\'interactions',
                    'data' => array_values($totalInteractions),
                    'backgroundColor' => ['rgba(255,0,0,0.5)', 'rgba(0,255,0,0.5)', 'rgba(0,0,255,0.5)'],
                ],
            ],
            'labels' => array_keys($groupCounts),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
