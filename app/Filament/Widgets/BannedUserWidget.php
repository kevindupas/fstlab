<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class BannedUserWidget extends Widget
{
    protected static string $view = 'filament.widgets.banned-user';

    public static function canView(): bool
    {
        /** @var \App\Models\User */
        $user = Auth::user();

        if ($user->status === 'banned') {
            return true;
        }

        if ($user->hasRole('secondary_experimenter')) {
            $principal = \App\Models\User::find($user->created_by);
            return $principal && $principal->status === 'banned';
        }

        return false;
    }

    protected int | string | array $columnSpan = 'full';
}
