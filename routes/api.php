<?php

use App\Http\Controllers\ExperimentSessionController;
use Illuminate\Support\Facades\Route;

// Récupérer les données d'une expérience en fonction du token
Route::get('/experiment/session/{token}', [ExperimentSessionController::class, 'show']);

// Enregistrer un participant pour une expérience
Route::post('/experiment/register/{token}', [ExperimentSessionController::class, 'registerParticipant']);

// Sauvegarder les données d'une session d'expérience
Route::post('/experiment/save/{sessionId}', [ExperimentSessionController::class, 'saveExperimentData']);
