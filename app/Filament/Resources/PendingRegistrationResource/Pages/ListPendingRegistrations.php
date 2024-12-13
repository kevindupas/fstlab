<?php

namespace App\Filament\Resources\PendingRegistrationResource\Pages;

use App\Filament\Resources\PendingRegistrationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPendingRegistrations extends ListRecords
{
    protected static string $resource = PendingRegistrationResource::class;
}
