<?php

use App\Http\Controllers\Api\ExperimentApiController;
use App\Http\Controllers\Api\HowItWorkApiController;
use App\Http\Controllers\Api\ExperimentSessionApiController;
use App\Http\Controllers\Api\TranslationController;
use Illuminate\Support\Facades\Route;

// Récupérer les traductions
Route::get('/translations/{locale}', [TranslationController::class, 'getTranslations']);

// Récupérer la liste des expériences
// Route::get('/experiments', [ExperimentApiController::class, 'index']);

Route::get('/howitwork/experiments', [HowItWorkApiController::class, 'getTestExperiments']);

// api.php

Route::middleware(['web'])->group(function () {
    Route::get('/experiments', [ExperimentApiController::class, 'index']);
    Route::get('/experiment/{id}', [ExperimentApiController::class, 'show']);
    Route::get('/user/auth-status', [ExperimentApiController::class, 'getAuthStatus']);
    Route::get('/experiment/access-status/{experiment}', [ExperimentApiController::class, 'checkAccessStatus']);
    Route::post('/experiment/request-access/{experimentId}', [ExperimentApiController::class, 'requestAccess']);
    Route::post('/experiment/request-results/{experimentId}', [ExperimentApiController::class, 'requestResults']);
    Route::post('/experiment/request-duplicate/{experimentId}', [ExperimentApiController::class, 'requestDuplicate']);
});

// Récupérer les données d'une expérience en fonction du token
Route::get('/experiment/session/{link}', [ExperimentSessionApiController::class, 'show']);

// Générer un participant ID pour une expérience
Route::get('/experiment/generate-participant-id/{link}', [ExperimentSessionApiController::class, 'generateParticipantId']);

// Enregistrer un participant pour une expérience
Route::post('/experiment/register/{link}', [ExperimentSessionApiController::class, 'registerParticipant']);

// Supprimer une session d'expérience
Route::delete('/experiment/session/{sessionId}', [ExperimentSessionApiController::class, 'deleteSession']);

// Sauvegarder les données d'une session d'expérience
Route::post('/experiment/save/{sessionId}', [ExperimentSessionApiController::class, 'saveExperimentData']);
