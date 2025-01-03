<?php

namespace App\Filament\Resources\ExperimentListResource\Pages;

use App\Filament\Resources\ExperimentListResource;
use App\Models\Experiment;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;


class ListExperimentLists extends ListRecords
{
    protected static string $resource = ExperimentListResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('filament.resources.experiment_list.tabs.all'))
                ->badge(Experiment::query()->count())
                ->badgeColor('primary')
                ->icon('heroicon-o-document-text'),
            'sound' => Tab::make(__('filament.resources.experiment_list.tabs.sound'))
                ->modifyQueryUsing(fn(Builder $query) => $query->where('type', 'sound'))
                ->badge(Experiment::query()->where('type', 'sound')->count())
                ->badgeColor('success')
                ->icon('heroicon-m-musical-note'),
            'image' => Tab::make(__('filament.resources.experiment_list.tabs.image'))
                ->modifyQueryUsing(fn(Builder $query) => $query->where('type', 'image'))
                ->badge(Experiment::query()->where('type', 'image')->count())
                ->badgeColor('info')
                ->icon('heroicon-m-photo'),
            'image_sound' => Tab::make(__('filament.resources.experiment_list.tabs.image_sound'))
                ->modifyQueryUsing(fn(Builder $query) => $query->where('type', 'image_sound'))
                ->badge(Experiment::query()->where('type', 'image_sound')->count())
                ->badgeColor('warning')
                ->icon('heroicon-m-speaker-wave')
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('clearFilter')
                ->label(__('actions.clearFilter'))
                ->icon('heroicon-o-x-mark')
                ->color('danger')
                ->url('/admin/experiments-list')
                ->visible(fn() => request()->has('filter_user'))
        ];
    }

    public function getDefaultActiveTab(): string | int | null
    {
        return 'all';
    }
}
