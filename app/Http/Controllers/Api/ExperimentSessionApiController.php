<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Experiment;
use App\Models\ExperimentSession;
use Illuminate\Http\Request;

class ExperimentSessionApiController extends Controller
{

    protected function resolveExperiment($link)
    {
        $experiment = Experiment::where('link', $link)->first();

        if (!$experiment) {
            return response()->json([
                'message' => 'No experiment associated with this link.'
            ], 404);
        }

        return $experiment;
    }

    public function show($link)
    {
        $experiment = $this->resolveExperiment($link);
        if (!$experiment) {
            return response()->json([
                'message' => 'No experiment associated with this link.'
            ], 404);
        }

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
            'experiment_id' => $experiment->id,
        ]);
    }

    public function registerParticipant(Request $request, $link)
    {

        $experiment = $this->resolveExperiment($link);
        if (!$experiment) {
            return response()->json(['message' => 'Experiment not found.'], 404);
        }

        $request->validate([
            'participant_number' => 'required|string|max:255',
            'browser' => 'required|string',
            'device_type' => 'required|string',
            'operating_system' => 'required|string',
            'screen_width' => 'required|integer',
            'screen_height' => 'required|integer',
        ]);

        // Vérification si l'email existe déjà
        $existingSession = ExperimentSession::where('experiment_id', $experiment->id)
            ->where('participant_number', $request->participant_number)
            ->first();

        if ($existingSession) {
            return response()->json(['message' => 'This number has already been used for this experiment.'], 409);
        }

        $session = ExperimentSession::create([
            'experiment_id' => $experiment->id,
            'participant_number' => $request->participant_number,
            'status' => 'created',
            'started_at' => now(),
            'browser' => $request->browser,
            'device_type' => $request->device_type,
            'operating_system' => $request->operating_system,
            'screen_width' => $request->screen_width,
            'screen_height' => $request->screen_height,
        ]);

        return response()->json([
            'message' => 'Participant registered successfully.',
            'session' => $session,
        ]);
    }

    public function saveExperimentData(Request $request, $sessionId)
    {
        $request->validate([
            'group_data' => 'required|array',
            'duration' => 'required|integer',
            'feedback' => 'nullable|string',

        ]);

        $session = ExperimentSession::find($sessionId);

        if (!$session) {
            return response()->json(['message' => 'Session not found.'], 404);
        }

        $session->update([
            'group_data' => json_encode($request->group_data),
            'actions_log' => json_encode($request->actions_log),
            'duration' => $request->duration,
            'completed_at' => now(),
            'feedback' => $request->feedback,
            'errors_log' => json_encode($request->errors_log),
            'status' => 'completed',

        ]);

        return response()->json([
            'message' => 'Experiment data saved successfully.',
        ]);
    }

    public function deleteSession($sessionId)
    {
        $session = ExperimentSession::find($sessionId);

        if (!$session) {
            return response()->json(['message' => 'Session not found.'], 404);
        }

        $session->delete();

        return response()->json([
            'message' => 'Session deleted successfully.',
        ]);
    }
}
