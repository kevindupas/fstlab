<?php

namespace App\Filament\Resources\MyExperimentResource\Pages;

use App\Filament\Resources\MyExperimentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMyExperiments extends ListRecords
{
    protected static string $resource = MyExperimentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label(__('filament.resources.my_experiment.actions.create')),
        ];
    }
}
