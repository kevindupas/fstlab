<?php

namespace App\Filament\Resources\BannedUsersResource\Pages;

use App\Filament\Resources\BannedUsersResource;
use App\Notifications\UserUnbanned;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Log;

class EditBannedUsers extends EditRecord
{
    protected static string $resource = BannedUsersResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $user = $this->record;


        Log::info('User status changed', [
            'status' => $user->status,
            'unbanned_reason' => $user->unbanned_reason,
            'status_changed' => $user->wasChanged('status'),
        ]);

        if ($user->wasChanged('status') && $user->status === 'approved' && $user->unbanned_reason) {
            $user->notify(new UserUnbanned($user->unbanned_reason));
        }
    }
}
