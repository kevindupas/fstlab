<?php

namespace App\Filament\Resources\ExperimentSessionResource\Pages;

use App\Filament\Resources\ExperimentSessionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditExperimentSession extends EditRecord
{
    protected static string $resource = ExperimentSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
