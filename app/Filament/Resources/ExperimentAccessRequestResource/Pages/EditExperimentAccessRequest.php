<?php

namespace App\Filament\Resources\ExperimentAccessRequestResource\Pages;

use App\Filament\Resources\ExperimentAccessRequestResource;
use App\Notifications\AccessRequestProcessed;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditExperimentAccessRequest extends EditRecord
{
    protected static string $resource = ExperimentAccessRequestResource::class;

    protected function afterSave(): void
    {
        $record = $this->record;

        if ($record->wasChanged('status') && in_array($record->status, ['approved', 'rejected'])) {
            $record->user->notify(
                new AccessRequestProcessed(
                    $record,
                    $record->status === 'approved'
                )
            );
            $this->redirect($this->getResource()::getUrl('index'));
        }


    }

    protected function authorizeAccess(): void
    {
        parent::authorizeAccess();

        $record = $this->getRecord();

        // if ($record->status !== 'pending') {
        //     $this->redirect($this->getResource()::getUrl('index'));
        //     return;
        // }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
