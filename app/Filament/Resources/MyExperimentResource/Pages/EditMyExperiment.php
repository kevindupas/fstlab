<?php

namespace App\Filament\Resources\MyExperimentResource\Pages;

use App\Filament\Resources\MyExperimentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMyExperiment extends EditRecord
{
    protected static string $resource = MyExperimentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
