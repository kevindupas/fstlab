<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Experiment;
use App\Models\ExperimentAccessRequest;
use App\Notifications\AccessRequestSubmitted;
use App\Notifications\NewAccessRequestReceived;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ExperimentApiController extends Controller
{
    public function index()
    {
        $experiments = Experiment::with('creator')
            ->where('status', '!=', 'test')
            ->withCount(['sessions as completed_sessions_count' => function ($query) {
                $query->whereNotNull('completed_at');
            }])
            ->get()
            ->map(function ($experiment) {
                return [
                    'id' => $experiment->id,
                    'name' => $experiment->name,
                    'description' => $experiment->description,
                    'completed_sessions_count' => $experiment->completed_sessions_count,
                    'creator_name' => $experiment->creator ? $experiment->creator->name : null,
                    'created_by' => $experiment->created_by,
                ];
            });

        return response()->json($experiments);
    }


    public function show($id)
    {
        try {
            $experiment = Experiment::with(['creator'])
                ->where('status', '!=', 'test')  // Exclusion des expériences en test
                ->withCount('completed_sessions')
                ->findOrFail($id);

            return response()->json($experiment);
        } catch (\Exception $e) {
            return response()->json([
                'error' => "L'expérience n'a pas été trouvée"
            ], 404);
        }
    }

    // ExperimentApiController.php
    public function getAuthStatus()
    {
        // Utiliser l'auth de Filament
        $auth = Filament::auth();
        $user = $auth->user();

        return response()->json([
            'isAuthenticated' => !is_null($user),
            'user' => $user ? [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ] : null,
        ]);
    }

    public function requestAccess(Request $request, $experimentId)
    {
        if (!Auth::check()) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Vous devez être connecté pour effectuer cette action'
            ], 401);
        }

        $request->validate([
            'message' => 'required|string|min:10',
        ]);

        // Vérifier si l'utilisateur n'est pas déjà le créateur
        $experiment = Experiment::findOrFail($experimentId);
        if ($experiment->created_by === Auth::id()) {
            return response()->json([
                'error' => 'Forbidden',
                'message' => 'Vous êtes le créateur de cette expérience'
            ], 403);
        }

        try {
            $accessRequest = new ExperimentAccessRequest([
                'user_id' => Auth::id(),
                'experiment_id' => $experimentId,
                'type' => 'access',
                'status' => 'pending',
                'request_message' => $request->message,
            ]);
            $accessRequest->save();

            return response()->json([
                'success' => true,
                'message' => 'Demande d\'accès envoyée avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating access request:', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'experiment_id' => $experimentId
            ]);

            return response()->json([
                'error' => 'Internal Server Error',
                'message' => 'Une erreur est survenue lors de la création de la demande'
            ], 500);
        }
    }

    public function requestResults(Request $request, $experimentId)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $request->validate([
            'message' => 'required|string|min:10',
        ]);

        $experiment = Experiment::findOrFail($experimentId);

        $accessRequest = new ExperimentAccessRequest([
            'user_id' => Auth::id(),
            'experiment_id' => $experimentId,
            'type' => 'results',
            'status' => 'pending',
            'request_message' => $request->message,
        ]);
        $accessRequest->save();

        // Envoyer les notifications
        $accessRequest->user->notify(new AccessRequestSubmitted($accessRequest));
        $experiment->creator->notify(new NewAccessRequestReceived($accessRequest));

        return response()->json(['message' => 'Demande d\'accès aux résultats envoyée avec succès']);
    }
}
