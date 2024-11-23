<?php

namespace App\Filament\Resources\ExperimentAccessRequestResource\Pages;

use App\Filament\Resources\ExperimentAccessRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditExperimentAccessRequest extends EditRecord
{
    protected static string $resource = ExperimentAccessRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
