<?php

namespace App\Filament\Pages;

use App\Models\Experiment;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class DashboardTest extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.dashboardtest';

    public $experiments;

    public function mount()
    {
        /** @var \App\Models\User */
        $user = Auth::user();

        if ($user->hasRole('supervisor')) {
            // Le superviseur voit toutes les expérimentations
            $this->experiments = Experiment::with('sessions')->get();
        } else if ($user->hasRole('principal_experimenter')) {
            // L'expérimentateur principal voit seulement les expérimentations qu'il a créées
            $this->experiments = Experiment::where('created_by', $user->id)->with('sessions')->get();
        }
    }

    protected function getViewData(): array
    {
        return [
            'experiments' => $this->experiments,
        ];
    }
}
