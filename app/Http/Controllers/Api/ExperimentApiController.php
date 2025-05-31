<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Experiment;
use App\Models\ExperimentAccessRequest;
use App\Notifications\AccessRequestSubmitted;
use App\Notifications\AccessUpgradeRequestSubmitted;
use App\Notifications\NewAccessRequestReceived;
use App\Notifications\NewAccessUpgradeRequestReceived;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ExperimentApiController extends Controller
{
    public function index()
    {
        $auth = Filament::auth();
        /** @var \App\Models\User|null */
        $user = $auth->user();
        $experiments = Experiment::with(['creator', 'links'])
            ->withCount(['sessions as completed_sessions_count' => function ($query) {
                $query->whereNotNull('completed_at');
            }])
            ->get()
            ->filter(function ($experiment) {
                return $experiment->is_public === true;
            })
            ->map(function ($experiment) use ($user) {
                $media = [];
                // Vérifie si on doit inclure les médias
                if (config('app.add_media_in_api', false)) {
                    if ($experiment->media) {
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

                $hasFullAccess = false;
                $hasResultsAccess = false;

                if ($user) {
                    // Vérifier dans experiment_user
                    $experimentUser = $experiment->users()
                        ->where('user_id', $user->id)
                        ->first();

                    if ($experimentUser) {
                        $hasFullAccess = $experimentUser->pivot->can_configure || $experimentUser->pivot->can_pass;
                        $hasResultsAccess = true;
                    }

                    // Vérifier aussi les demandes d'accès approuvées
                    $accessRequest = ExperimentAccessRequest::where('user_id', $user->id)
                        ->where('experiment_id', $experiment->id)
                        ->where('status', 'approved')
                        ->get();

                    if ($accessRequest->contains('type', 'access')) {
                        $hasFullAccess = true;
                    }

                    if ($accessRequest->contains('type', 'results')) {
                        $hasResultsAccess = true;
                    }
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
                    'hasFullAccess' => $hasFullAccess,
                    'hasResultsAccess' => $hasResultsAccess,
                    'language' => $experiment->language,
                ];
            })
            ->values()
            ->all();

        return response()->json($experiments);
    }

    public function getAuthStatus()
    {
        $auth = Filament::auth();
        /** @var \App\Models\User|null */
        $user = $auth->user();

        return response()->json([
            'isAuthenticated' => !is_null($user),
            'user' => $user ? [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'isSecondary' => $user->hasRole('secondary_experimenter'),
            ] : null,
        ]);
    }

    public function checkAccessStatus(Experiment $experiment)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Vérifier les accès dans la table pivot experiment_user
        $experimentUser = $experiment->users()
            ->where('user_id', $user->id)
            ->first();

        $hasFullAccess = false;
        $hasResultsAccess = false;

        // Si l'utilisateur est dans la table pivot
        if ($experimentUser) {
            $hasFullAccess = $experimentUser->pivot->can_configure || $experimentUser->pivot->can_pass;
            $hasResultsAccess = true;  // S'il est dans la table pivot, il a au moins accès aux résultats
        }

        // Vérifier aussi les demandes d'accès approuvées
        if (!$hasFullAccess || !$hasResultsAccess) {
            $existingRequests = ExperimentAccessRequest::where('user_id', $user->id)
                ->where('experiment_id', $experiment->id)
                ->where('status', 'approved')
                ->get();

            $hasFullAccess = $hasFullAccess || $existingRequests->contains('type', 'access');
            $hasResultsAccess = $hasResultsAccess || $existingRequests->contains('type', 'results');
        }

        return response()->json([
            'hasFullAccess' => $hasFullAccess,
            'hasResultsAccess' => $hasResultsAccess,
        ]);
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

        $experiment = Experiment::findOrFail($experimentId);
        if ($experiment->created_by === Auth::id()) {
            return response()->json([
                'error' => 'Forbidden',
                'message' => 'Vous êtes le créateur de cette expérience'
            ], 403);
        }

        try {
            // Vérifier s'il existe une demande approuvée de type "results"
            $existingResultsRequest = ExperimentAccessRequest::where('user_id', Auth::id())
                ->where('experiment_id', $experimentId)
                ->where('type', 'results')
                ->where('status', 'approved')
                ->first();

            // Vérifier s'il existe déjà une demande en cours du type access
            $pendingRequest = ExperimentAccessRequest::where('user_id', Auth::id())
                ->where('experiment_id', $experimentId)
                ->where('type', 'access')
                ->whereIn('status', ['pending', 'approved'])
                ->first();

            if ($pendingRequest) {
                return response()->json([
                    'error' => 'Forbidden',
                    'message' => 'Une demande est déjà en cours ou vous avez déjà cet accès'
                ], 403);
            }

            // Créer la nouvelle demande d'accès
            $accessRequest = new ExperimentAccessRequest([
                'user_id' => Auth::id(),
                'experiment_id' => $experimentId,
                'type' => 'access',
                'status' => 'pending',
                'request_message' => $request->message,
            ]);
            $accessRequest->save();

            // Envoyer les notifications appropriées selon le contexte
            if ($existingResultsRequest) {
                // Cas de mise à niveau : notifications spécifiques
                $accessRequest->user->notify(new AccessUpgradeRequestSubmitted($accessRequest));
                $experiment->creator->notify(new NewAccessUpgradeRequestReceived($accessRequest));
            } else {
                // Cas standard : notifications normales
                $accessRequest->user->notify(new AccessRequestSubmitted($accessRequest));
                $experiment->creator->notify(new NewAccessRequestReceived($accessRequest));
            }

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
