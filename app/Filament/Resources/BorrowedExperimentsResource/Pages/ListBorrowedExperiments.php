<?php

namespace App\Filament\Resources\BorrowedExperimentsResource\Pages;

use App\Filament\Resources\BorrowedExperimentsResource;
use Filament\Resources\Pages\ListRecords;
use App\Models\ExperimentAccessRequest;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListBorrowedExperiments extends ListRecords
{
    protected static string $resource = BorrowedExperimentsResource::class;

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Actions\CreateAction::make(),
    //     ];
    // }

    public function getTabs(): array
    {
        $userId = Auth::id();
        return [
            'all' => Tab::make(__('filament.resources.borrowed_experiment.tabs.all'))
                ->badge(
                    ExperimentAccessRequest::query()
                        ->where('user_id', $userId)
                        ->where('status', 'approved')
                        ->whereIn('type', ['results', 'access'])
                        ->count()
                )
                ->badgeColor('primary')
                ->icon('heroicon-o-document-text'),
            'results' => Tab::make('Résultats seulement')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('type', 'results'))
                ->icon('heroicon-m-document-text')
                ->badge(
                    ExperimentAccessRequest::query()
                        ->where('user_id', $userId)
                        ->where('status', 'approved')
                        ->where('type', 'results')
                        ->count()
                )
                ->badgeColor('warning'),
            'access' => Tab::make('Collaborations')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('type', 'access'))
                ->icon('heroicon-m-users')
                ->badge(
                    ExperimentAccessRequest::query()
                        ->where('user_id', $userId)
                        ->where('status', 'approved')
                        ->where('type', 'access')
                        ->count()
                )
                ->badgeColor('success'),
        ];
    }

    public function getDefaultActiveTab(): string | int | null
    {
        return 'all';
    }
}
