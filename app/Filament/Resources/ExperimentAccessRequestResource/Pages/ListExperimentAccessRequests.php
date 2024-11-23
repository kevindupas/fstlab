<?php

namespace App\Filament\Resources\ExperimentAccessRequestResource\Pages;

use App\Filament\Resources\ExperimentAccessRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListExperimentAccessRequests extends ListRecords
{
    protected static string $resource = ExperimentAccessRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
