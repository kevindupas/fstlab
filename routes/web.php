<?php

use App\Filament\Pages\ExperimentSessions;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExperimentController;
use App\Livewire\ExperimentSession;

// Route::get('/', function () {
//     return view('app');
// });

Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');

// Route::get('/experiments/export/{id}', [ExperimentController::class, 'export'])->name('export.experiment');
// Route::get('/experiments/{experimentId}/download', [ExperimentController::class, 'download'])->name('download');
