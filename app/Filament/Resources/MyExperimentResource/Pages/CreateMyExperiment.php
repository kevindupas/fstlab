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

        if (!isset($data['doi'])) {
            $data['doi'] = Str::random(10);
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $experiment = $this->record;
        $selectedStatus = $this->data['status'] ?? 'stop';

        \App\Models\ExperimentLink::create([
            'experiment_id' => $experiment->id,
            'user_id' => Auth::id(),
            'link' => $this->data['temp_link'] ?? null,
            'status' => $selectedStatus,
            'is_creator' => true,
            'is_secondary' => false,
            'is_collaborator' => false
        ]);
    }
}
