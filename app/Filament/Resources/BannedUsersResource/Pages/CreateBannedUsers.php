<?php

namespace App\Filament\Resources\BannedUsersResource\Pages;

use App\Filament\Resources\BannedUsersResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBannedUsers extends CreateRecord
{
    protected static string $resource = BannedUsersResource::class;
}
