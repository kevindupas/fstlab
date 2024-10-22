<?php

namespace App\Http\Controllers;

use App\Models\Experiment;
use App\Models\ExperimentSession;
use Illuminate\Http\Request;

class ExperimentSessionController extends Controller
{
    // Récupérer les données de l'expérience pour l'envoyer à React
    public function show($token)
    {
        $experiment = Experiment::where('link', $token)->first();

        if (!$experiment) {
            return response()->json([
                'message' => 'No experiment associated with this token.'
            ], 404);
        }

        // Traitement des médias associés à l'expérience
        $mediaArray = is_string($experiment->media) ? json_decode($experiment->media, true) : $experiment->media;
        $media = collect($mediaArray)->map(function ($item) use ($experiment) {
            return [
                'id' => $item,
                'url' => asset('storage/' . $item),
                'type' => $experiment->type,
                'button_size' => $experiment->button_size ?? '100',
                'button_color' => $experiment->button_color ?? '#0000FF',
            ];
        });

        return response()->json([
            'experiment' => $experiment,
            'media' => $media,
        ]);
    }

    // Valider l'utilisateur avant de commencer l'expérience
    public function registerParticipant(Request $request, $token)
    {
        $request->validate([
            'participant_name' => 'required|string|max:255',
            'participant_email' => 'required|email|max:255',
        ]);

        $experiment = Experiment::where('link', $token)->first();

        if (!$experiment) {
            return response()->json(['message' => 'Experiment not found.'], 404);
        }

        // Vérification si l'email existe déjà
        $existingSession = ExperimentSession::where('experiment_id', $experiment->id)
            ->where('participant_email', $request->participant_email)
            ->first();

        if ($existingSession) {
            return response()->json(['message' => 'This email has already been used for this experiment.'], 409);
        }

        // Créer la session pour ce participant
        $session = ExperimentSession::create([
            'experiment_id' => $experiment->id,
            'participant_name' => $request->participant_name,
            'participant_email' => $request->participant_email,
        ]);

        return response()->json([
            'message' => 'Participant registered successfully.',
            'session' => $session,
        ]);
    }

    // Sauvegarder les données d'expérience après sa réalisation
    public function saveExperimentData(Request $request, $sessionId)
    {
        $request->validate([
            'group_data' => 'required|array',
            'actions_log' => 'required|array',
            'duration' => 'required|integer',
        ]);

        $session = ExperimentSession::find($sessionId);

        if (!$session) {
            return response()->json(['message' => 'Session not found.'], 404);
        }

        $session->update([
            'group_data' => json_encode($request->group_data),
            'actions_log' => json_encode($request->actions_log),
            'duration' => $request->duration,
        ]);

        return response()->json([
            'message' => 'Experiment data saved successfully.',
        ]);
    }
}
