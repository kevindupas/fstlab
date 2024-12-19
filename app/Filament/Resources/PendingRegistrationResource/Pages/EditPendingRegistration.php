<?php

namespace App\Filament\Resources\PendingRegistrationResource\Pages;

use App\Filament\Resources\PendingRegistrationResource;
use App\Notifications\RegistrationApproved;
use App\Notifications\RegistrationRejected;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

class EditPendingRegistration extends EditRecord
{
    protected static string $resource = PendingRegistrationResource::class;

    public function getTitle(): string | Htmlable
    {
        return __('filament.resources.pending_registration.title') . " : " . $this->record->name;
    }


    protected function afterSave(): void
    {
        $record = $this->record;

        if ($record->wasChanged('status')) {
            if ($record->status === 'approved') {
                $record->notify(new RegistrationApproved());
                $this->redirect($this->getResource()::getUrl('index'));
            } else {
                $record->notify(new RegistrationRejected($record->rejection_reason));
                $this->redirect($this->getResource()::getUrl('index'));
            }
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->label(__('filament.resources.pending_registration.action.delete')),
        ];
    }
}
