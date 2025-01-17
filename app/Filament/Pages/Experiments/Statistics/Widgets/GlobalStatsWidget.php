<?php

namespace App\Filament\Pages\Experiments\Statistics\Widgets;

use App\Models\Experiment;
use App\Models\ExperimentSession;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class GlobalStatsWidget extends BaseWidget
{
    public Experiment $record;
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $sessions = ExperimentSession::where('experiment_id', $this->record->id)
            ->whereNotNull('actions_log')
            ->get();

        $canvasSizes = $sessions->map(function ($session) {
            if ($session->canvas_size) {
                $size = json_decode($session->canvas_size, true);
                return [
                    'width_cm' => $size['width_cm'],
                    'height_cm' => $size['height_cm'],
                    'width_px' => $size['width_px'],
                    'height_px' => $size['height_px'],
                    'dpi' => $size['dpi'],
                ];
            }
            return null;
        })->filter();

        // Canvas stats
        $avgCanvasWidth = $canvasSizes->avg('width_cm');
        $avgCanvasHeight = $canvasSizes->avg('height_cm');
        $avgDPI = $canvasSizes->avg('dpi');

        // Actions stats
        $totalMoves = 0;
        $totalPlays = 0;
        $totalGroupChanges = 0;

        foreach ($sessions as $session) {
            $actions = collect(json_decode($session->actions_log, true));

            $totalMoves += $actions->where('type', 'move')->count();
            $totalPlays += $actions->whereIn('type', ['sound', 'image'])->count();
            $totalGroupChanges += $actions->where('type', 'item_moved_between_groups')->count();
        }

        $avgMovesPerSession = $sessions->count() > 0 ? round($totalMoves / $sessions->count(), 1) : 0;
        $avgPlaysPerSession = $sessions->count() > 0 ? round($totalPlays / $sessions->count(), 1) : 0;
        $avgGroupChangesPerSession = $sessions->count() > 0 ? round($totalGroupChanges / $sessions->count(), 1) : 0;

        return [
            // Taille moyenne du canvas
            Stat::make(
                'Taille moyenne du canvas',
                sprintf('%.1f × %.1f cm', $avgCanvasWidth, $avgCanvasHeight)
            )
                ->description(sprintf('Résolution moyenne : %.0f DPI', $avgDPI))
                ->icon('heroicon-o-rectangle-group')
                ->color('purple'),

            // Statistiques par session
            Stat::make(
                'Actions moyennes par session',
                sprintf('%.1f actions', $avgMovesPerSession + $avgPlaysPerSession)
            )
                ->description(sprintf('%.1f déplacements, %.1f lectures/vues', $avgMovesPerSession, $avgPlaysPerSession))
                ->icon('heroicon-o-cursor-arrow-rays')
                ->color('success'),

            // Changements de groupe
            Stat::make(
                'Organisation des groupes',
                sprintf('%.1f changements/session', $avgGroupChangesPerSession)
            )
                ->description(sprintf('Total : %d changements', $totalGroupChanges))
                ->icon('heroicon-o-user-group')
                ->color('warning'),

            // Statistiques globales
            Stat::make(
                'Totaux globaux',
                sprintf('%d actions', $totalMoves + $totalPlays)
            )
                ->description(sprintf('%d déplacements, %d lectures/vues', $totalMoves, $totalPlays))
                ->icon('heroicon-o-chart-bar')
                ->color('info'),
        ];
    }
}
