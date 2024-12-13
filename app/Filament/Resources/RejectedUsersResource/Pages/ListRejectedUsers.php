<?php

namespace App\Filament\Resources\RejectedUsersResource\Pages;

use App\Filament\Resources\RejectedUsersResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRejectedUsers extends ListRecords
{
    protected static string $resource = RejectedUsersResource::class;
}
