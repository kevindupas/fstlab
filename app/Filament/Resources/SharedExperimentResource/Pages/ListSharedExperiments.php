<?php

namespace App\Filament\Resources\SharedExperimentResource\Pages;

use App\Filament\Resources\SharedExperimentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSharedExperiments extends ListRecords
{
    protected static string $resource = SharedExperimentResource::class;

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Actions\CreateAction::make(),
    //     ];
    // }
}
