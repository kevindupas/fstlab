<?php

use App\Filament\Pages\ExperimentSessions;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExperimentController;
use App\Http\Controllers\ExportSessionsController;
use App\Livewire\ExperimentSession;

// Route::get('/', function () {
//     return view('app');
// });

Route::get('/admin/export-sessions', [ExportSessionsController::class, 'export'])
    ->middleware(['web', 'auth']) // Important d'ajouter les middlewares
    ->name('export.sessions');

Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
