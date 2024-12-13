<?php

namespace App\Filament\Resources\PendingRegistrationResource\Pages;

use App\Filament\Resources\PendingRegistrationResource;
use App\Notifications\RegistrationApproved;
use App\Notifications\RegistrationRejected;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPendingRegistration extends EditRecord
{
    protected static string $resource = PendingRegistrationResource::class;

    protected function afterSave(): void
    {
        $record = $this->record;

        if ($record->wasChanged('status')) {
            if ($record->status === 'approved') {
                $record->notify(new RegistrationApproved());
            } else {
                $record->notify(new RegistrationRejected($record->rejection_reason));
            }
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
