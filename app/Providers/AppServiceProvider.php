<?php

namespace App\Providers;

use App\Models\Experiment;
use App\Models\User;
use App\Observers\ExperimentObserver;
use App\Observers\UserObserver;
use BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (env('APP_ENV') === 'production') {
            URL::forceScheme('https');

            \Livewire\Livewire::setUpdateRoute(function ($handle) {
                return Route::post('/livewire/update', $handle)
                    ->middleware(['web', 'auth'])
                    ->name('livewire.update');
            });
        }

        User::observe(UserObserver::class);
        Experiment::observe(ExperimentObserver::class);
        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $switch
                ->locales(['fr', 'en'])
                ->flags([
                    'fr' => asset('flags/fr.svg'),
                    'en' => asset('flags/um.svg'),
                ])
                ->visible(outsidePanels: true);
        });
    }
}
