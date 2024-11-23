<?php

namespace App\Providers;


use Illuminate\Support\ServiceProvider;
use Statikbe\FilamentTranslationManager\FilamentTranslationManager;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        FilamentTranslationManager::setLocales(['en', 'fr', 'es']);
    }
}
