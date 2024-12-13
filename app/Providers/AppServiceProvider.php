<?php

namespace App\Providers;

use App\Models\User;
use App\Observers\UserObserver;
use BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        User::observe(UserObserver::class);
        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $switch
                ->locales(['fr', 'en', 'es'])
                // ->renderHook('panels::global-search.before')
                // ->visible(outsidePanels: true)
                ->circular()
                ->flags([
                    'fr' => asset('flags/fr.svg'),
                    'en' => asset('flags/um.svg'),
                    'es' => asset('flags/es.svg'),
                ]);
        });
    }
}
