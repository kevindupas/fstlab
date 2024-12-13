<?php

namespace App\Filament\Resources\MyExperimentResource\Pages;

use App\Filament\Resources\MyExperimentResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CreateMyExperiment extends CreateRecord
{
    protected static string $resource = MyExperimentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = Auth::id();

        return $data;
    }

    // protected function getRedirectUrl(): string
    // {
    //     // Utiliser la route de l'index de la resource au lieu de pages
    //     return $this->getResource()::getUrl('index');
    // }

    protected function afterCreate(): void
    {
        $experiment = $this->record;

        $experiment->link = Str::random(6);
        $experiment->save();
    }
}
