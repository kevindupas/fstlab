<?php

use App\Filament\Pages\ExperimentSessions;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExperimentController;
use App\Http\Controllers\ExportSessionsController;
use App\Livewire\ExperimentSession;

// Route::get('/', function () {
//     return view('app');
// });

Route::get('/login', function () {
    return redirect()->route('filament.admin.auth.login');
})->name('login');

Route::get('/admin/export-sessions', [ExportSessionsController::class, 'export'])
    ->middleware(['web', 'auth']) // Important d'ajouter les middlewares
    ->name('export.sessions');

Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
