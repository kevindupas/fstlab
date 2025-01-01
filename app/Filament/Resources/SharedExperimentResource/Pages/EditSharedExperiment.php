<?php

namespace App\Filament\Resources\SharedExperimentResource\Pages;

use App\Filament\Resources\SharedExperimentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSharedExperiment extends EditRecord
{
    protected static string $resource = SharedExperimentResource::class;

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Actions\DeleteAction::make(),
    //     ];
    // }
}
