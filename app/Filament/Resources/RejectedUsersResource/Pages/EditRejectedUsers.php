<?php

namespace App\Filament\Resources\RejectedUsersResource\Pages;

use App\Filament\Resources\RejectedUsersResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

class EditRejectedUsers extends EditRecord
{
    protected static string $resource = RejectedUsersResource::class;

    public function getTitle(): string | Htmlable
    {
        return __('filament.resources.rejected_user.title') . " : " . $this->record->name;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->label(__('filament.resources.rejected_user.action.delete')),
        ];
    }
}
