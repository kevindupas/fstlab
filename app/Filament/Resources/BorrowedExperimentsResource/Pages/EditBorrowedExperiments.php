<?php

namespace App\Filament\Resources\BorrowedExperimentsResource\Pages;

use App\Filament\Resources\BorrowedExperimentsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBorrowedExperiments extends EditRecord
{
    protected static string $resource = BorrowedExperimentsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
