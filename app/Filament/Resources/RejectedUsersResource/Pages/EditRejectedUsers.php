<?php

namespace App\Filament\Resources\RejectedUsersResource\Pages;

use App\Filament\Resources\RejectedUsersResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRejectedUsers extends EditRecord
{
    protected static string $resource = RejectedUsersResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
