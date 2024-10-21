<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExperimentController;
use App\Livewire\ExperimentSession;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/experiments/export/{id}', [ExperimentController::class, 'export'])->name('export.experiment');
Route::get('/experiments/{experimentId}/download', [ExperimentController::class, 'download'])->name('download');

Route::get('/experiment/session/{token}', ExperimentSession::class);
