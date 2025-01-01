<?php

namespace App\Filament\Resources\ExperimentAccessRequestResource\Pages;

use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

use App\Filament\Resources\ExperimentAccessRequestResource;
use App\Models\ExperimentAccessRequest;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListExperimentAccessRequests extends ListRecords
{
    protected static string $resource = ExperimentAccessRequestResource::class;

    public function getTabs(): array
    {
        $userId = Auth::id();
        return [
            'all' => Tab::make(__('filament.resources.experiment-access-request.tabs.all'))
                ->badge(
                    ExperimentAccessRequest::query()
                        ->whereHas('experiment', fn($query) => $query->where('created_by', $userId))
                        ->whereIn('status', ['approved', 'pending', 'rejected', 'revoked'])
                        ->count()
                )
                ->badgeColor('primary')
                ->icon('heroicon-o-document-text'),
            'pending' => Tab::make(__('filament.resources.experiment-access-request.tabs.pending'))
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'pending'))
                ->icon('heroicon-m-clock')
                ->badge(
                    ExperimentAccessRequest::query()
                        ->whereHas('experiment', fn($query) => $query->where('created_by', $userId))
                        ->where('status', 'pending')
                        ->count()
                )
                ->badgeColor('warning'),
            'approved' => Tab::make(__('filament.resources.experiment-access-request.tabs.approved'))
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'approved'))
                ->icon('heroicon-m-check-circle')
                ->badge(
                    ExperimentAccessRequest::query()
                        ->whereHas('experiment', fn($query) => $query->where('created_by', $userId))
                        ->where('status', 'approved')
                        ->count()
                )
                ->badgeColor('success'),
            'rejected' => Tab::make(__('filament.resources.experiment-access-request.tabs.revoked'))
                ->modifyQueryUsing(fn(Builder $query) => $query->whereIn('status', ['rejected', 'revoked']))
                ->icon('heroicon-m-x-circle')
                ->badge(
                    ExperimentAccessRequest::query()
                        ->whereHas('experiment', fn($query) => $query->where('created_by', $userId))
                        ->whereIn('status', ['rejected', 'revoked'])
                        ->count()
                )
                ->badgeColor('danger'),
        ];
    }

    public function getDefaultActiveTab(): string | int | null
    {
        return 'approved';
    }
}
