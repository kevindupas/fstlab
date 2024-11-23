<?php

namespace App\Filament\Resources\ExperimentResource\Pages;

use App\Filament\Resources\ExperimentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Kenepa\ResourceLock\Resources\Pages\Concerns\UsesResourceLock;

class EditExperiment extends EditRecord
{
    use UsesResourceLock;

    protected static string $resource = ExperimentResource::class;

    protected function handleRecordUpdate($record, array $data): Model
    {
        $record->fill($data);
        $record->save();

        if (isset($data['user_ids'])) {
            $record->users()->sync($data['user_ids']);
        }

        return $record;
    }


    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
