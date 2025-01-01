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
        $experiments = Experiment::with(['creator', 'links'])
            ->whereHas('links', function ($query) {
                $query->where('status', '!=', 'test')
                    ->whereColumn('user_id', 'experiments.created_by');
            })
            ->withCount(['sessions as completed_sessions_count' => function ($query) {
                $query->whereNotNull('completed_at');
            }])
            ->get()
            ->map(function ($experiment) {
                // Gérer les médias
                $media = [];
                if ($experiment->media) {
                    // Si c'est une string JSON, on la décode
                    if (is_string($experiment->media)) {
                        $mediaArray = json_decode($experiment->media, true) ?? [];
                    } else {
                        $mediaArray = $experiment->media;
                    }
                    // Ajouter le préfixe /storage/
                    $media = array_map(function ($path) {
                        return '/storage/' . $path;
                    }, $mediaArray);
                }

                // Gérer les documents
                $documents = [];
                if ($experiment->documents) {
                    // Si c'est une string JSON, on la décode
                    if (is_string($experiment->documents)) {
                        $docsArray = json_decode($experiment->documents, true) ?? [];
                    } else {
                        $docsArray = $experiment->documents;
                    }
                    // Ajouter le préfixe /storage/
                    $documents = array_map(function ($path) {
                        return '/storage/' . $path;
                    }, $docsArray);
                }

                // Assemble toutes les données
                return [
                    'id' => $experiment->id,
                    'name' => $experiment->name,
                    'description' => $experiment->description,
                    'instruction' => $experiment->instruction,
                    'type' => $experiment->type,
                    'media' => $media,
                    'documents' => $documents,
                    'completed_sessions_count' => $experiment->completed_sessions_count,
                    'creator_name' => $experiment->creator ? $experiment->creator->name : null,
                    'created_by' => $experiment->created_by,
                    'doi' => $experiment->doi,
                    'status' => $experiment->links->where('user_id', $experiment->created_by)->first()?->status ?? 'stop',
                    'original_creator_name' => $experiment->originalCreator ? $experiment->originalCreator->name : null,
                ];
            });

        return response()->json($experiments);
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

            $accessRequest->user->notify(new AccessRequestSubmitted($accessRequest));
            $experiment->creator->notify(new NewAccessRequestReceived($accessRequest));


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

    public function requestDuplicate(Request $request, $experimentId)
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

        $experiment = Experiment::findOrFail($experimentId);

        // Vérifier si l'utilisateur n'est pas déjà le créateur
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
                'type' => 'duplicate',
                'status' => 'pending',
                'request_message' => $request->message,
            ]);
            $accessRequest->save();

            // Envoyer les notifications
            $accessRequest->user->notify(new AccessRequestSubmitted($accessRequest));
            $experiment->creator->notify(new NewAccessRequestReceived($accessRequest));

            return response()->json([
                'success' => true,
                'message' => 'Demande de duplication envoyée avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating duplicate request:', [
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
}
