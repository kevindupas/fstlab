<?php

namespace App\Filament\Resources\ExperimentListResource\Pages;

use App\Filament\Resources\ExperimentListResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditExperimentList extends EditRecord
{
    protected static string $resource = ExperimentListResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
