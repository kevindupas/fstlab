<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Gate;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
}
