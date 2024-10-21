<?php

namespace App\Filament\Resources\ExperimentResource\Pages;

use App\Filament\Resources\ExperimentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListExperiments extends ListRecords
{
    protected static string $resource = ExperimentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
