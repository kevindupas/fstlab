<?php

namespace App\Filament\Resources\BannedUsersResource\Pages;

use App\Filament\Resources\BannedUsersResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBannedUsers extends ListRecords
{
    protected static string $resource = BannedUsersResource::class;
}
