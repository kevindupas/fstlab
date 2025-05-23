<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExperimentLink;
use App\Models\ExperimentSession;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ExperimentSessionApiController extends Controller
{

    protected function resolveExperiment($link)
    {
        $experimentLink = ExperimentLink::with('experiment')
            ->where('link', $link)
            ->whereIn('status', ['start', 'pause', 'test'])
            ->first();

        if (!$experimentLink) {
            return response()->json([
                'message' => 'No active experiment associated with this link.'
            ], 404);
        }

        if ($experimentLink->status === 'pause') {
            return response()->json([
                'message' => 'This experiment is currently paused. Please try again later.'
            ], 403);
        }

        return $experimentLink;
    }

    protected function resolveExperimentForOtherMethods($link)
    {
        return ExperimentLink::with('experiment')
            ->where('link', $link)
            ->whereIn('status', ['start', 'pause', 'test'])
            ->first();
    }

    public function show($link)
    {
        try {
            // Récupérer directement l'ExperimentLink
            $experimentLink = ExperimentLink::with('experiment')
                ->where('link', $link)
                ->first();

            if (!$experimentLink) {
                return response()->json([
                    'experiment' => [
                        'status' => 'not_found'
                    ]
                ]);
            }

            // Vérifier le status et retourner la réponse appropriée
            if ($experimentLink->status === 'pause') {
                return response()->json([
                    'experiment' => [
                        'status' => 'pause'
                    ]
                ]);
            }

            if ($experimentLink->status === 'stop') {
                return response()->json([
                    'experiment' => [
                        'status' => 'stop'
                    ]
                ]);
            }

            // Pour les statuts start et test
            $experiment = $experimentLink->experiment;

            if (!$experiment) {
                return response()->json([
                    'experiment' => [
                        'status' => 'not_found'
                    ]
                ]);
            }

            $mediaArray = is_string($experiment->media) ?
                json_decode($experiment->media, true) : ($experiment->media ?? []);

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
                'experiment' => array_merge($experiment->toArray(), [
                    'status' => $experimentLink->status,
                    'link' => $experimentLink->link
                ]),
                'media' => $media,
                'experiment_id' => $experiment->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in show method: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json([
                'experiment' => [
                    'status' => 'unknown'
                ]
            ]);
        }
    }

    public function generateParticipantId($link)
    {
        try {
            $experimentLink = $this->resolveExperiment($link);
            if (!$experimentLink) {
                return response()->json(['message' => 'Experiment not found.'], 404);
            }

            $participantId = 'P-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4));

            return response()->json([
                'participantId' => $participantId,
            ]);
        } catch (\Exception $e) {
            Log::error('Error generating participant ID: ' . $e->getMessage());
            return response()->json(['message' => 'Error generating participant ID.'], 500);
        }
    }

    public function registerParticipant(Request $request, $link)
    {
        $experimentLink = $this->resolveExperiment($link);
        if (!$experimentLink || !$experimentLink->experiment) {
            return response()->json(['message' => 'Experiment not found.'], 404);
        }

        $experiment = $experimentLink->experiment;

        $request->validate([
            'participant_number' => 'required|string|max:255',
            'browser' => 'required|string',
            'device_type' => 'required|string',
            'operating_system' => 'required|string',
            'screen_width' => 'required|integer',
            'screen_height' => 'required|integer',
        ]);

        $session = ExperimentSession::create([
            'experiment_id' => $experiment->id,
            'experiment_link_id' => $experimentLink->id,
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
            'canvas_size' => json_encode($request->canvas_size),
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
