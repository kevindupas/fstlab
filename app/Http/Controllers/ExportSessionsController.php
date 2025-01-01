<?php

namespace App\Http\Controllers;

use App\Filament\Pages\Experiments\Sessions\ExperimentSessionExport;
use App\Models\Experiment;
use App\Models\ExperimentSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use League\Csv\Writer;

class ExportSessionsController extends Controller
{
    public function export(Request $request)
    {

        $userId = Auth::id();

        try {
            $experimentId = $request->query('record');
            $currentTab = $request->query('tab');

            $query = ExperimentSession::query()
                ->where('experiment_id', $experimentId)
                ->where('status', 'completed');

            // Appliquer les filtres selon l'onglet actif
            if ($currentTab === 'creator') {
                $experiment = Experiment::find($experimentId);
                $query->whereHas('experimentLink', function ($q) use ($experiment) {
                    $q->whereHas('user', function ($userQ) use ($experiment) {
                        $userQ->where(function ($sq) use ($experiment) {
                            $sq->where('id', $experiment->created_by)
                                ->orWhere('created_by', $experiment->created_by);
                        });
                    });
                });
            } elseif ($currentTab === 'mine') {
                $query->whereHas('experimentLink', fn($q) => $q->where('user_id', $userId));
            } elseif ($currentTab === 'collaborators') {
                $experiment = Experiment::find($experimentId);
                $query->whereHas('experimentLink', function ($q) use ($experiment) {
                    $q->whereHas('user', function ($userQ) use ($experiment) {
                        $userQ->where(function ($sq) use ($experiment) {
                            $sq->where('id', '!=', $experiment->created_by)
                                ->where('created_by', '!=', $experiment->created_by)
                                ->orWhereNull('created_by');
                        });
                    })->where('user_id', '!=', Auth::id());
                });
            }

            $sessions = $query->get();

            return response()->streamDownload(function () use ($sessions) {
                echo "\xEF\xBB\xBF"; // BOM UTF-8

                $csv = Writer::createFromString('');
                $csv->setDelimiter(',');
                $csv->setEnclosure('"');

                // Définition des en-têtes
                $headers = [
                    'session_id',
                    'experimenter_name',
                    'experimenter_type',
                    'participant_number',
                    'created_at',
                    'completed_at',
                    'duration_seconds',
                    'browser',
                    'system',
                    'device',
                    'screen_width',
                    'screen_height',
                    'feedback'
                ];

                // En-têtes des groupes
                for ($i = 1; $i <= 3; $i++) {
                    $headers[] = "group{$i}_name";
                    $headers[] = "group{$i}_comment";
                    for ($j = 1; $j <= 4; $j++) {
                        $headers[] = "group{$i}_media{$j}_name";
                        $headers[] = "group{$i}_media{$j}_interactions";
                        $headers[] = "group{$i}_media{$j}_x";
                        $headers[] = "group{$i}_media{$j}_y";
                    }
                }

                $csv->insertOne($headers);

                foreach ($sessions as $session) {
                    $row = [
                        (int)$session->id,                                        // Forcé en entier
                        $session->experimentLink?->user?->name ?? 'NA',           // String
                        $this->getExperimenterType($session),                    // String
                        (string)$session->participant_number,                     // String
                        $session->created_at->format('Y-m-d H:i:s'),             // Date formatée
                        $session->completed_at?->format('Y-m-d H:i:s') ?? 'NA',  // Date formatée
                        number_format($session->duration / 1000, 3, '.', ''),    // Float avec 3 décimales
                        $session->browser ?? 'NA',                               // String
                        $session->operating_system ?? 'NA',                      // String
                        $session->device_type ?? 'NA',                          // String
                        (string)$session->screen_width,                          // String
                        (string)$session->screen_height,                         // String
                        $this->cleanText($session->feedback)                     // String nettoyé
                    ];

                    // Traitement des groupes
                    $groupData = json_decode($session->group_data, true) ?? [];
                    for ($i = 0; $i < 3; $i++) {
                        $group = $groupData[$i] ?? null;
                        if ($group) {
                            $row[] = $this->cleanText($group['name'] ?? 'NA');
                            $row[] = $this->cleanText($group['comment'] ?? 'NA');

                            $elements = $group['elements'] ?? [];
                            for ($j = 0; $j < 4; $j++) {
                                $element = $elements[$j] ?? null;
                                if ($element) {
                                    $row[] = basename($element['url']);
                                    $row[] = (int)($element['interactions'] ?? 0);    // Forcé en entier
                                    $row[] = number_format($element['x'] ?? 0, 3, '.', ''); // Float 3 décimales
                                    $row[] = number_format($element['y'] ?? 0, 3, '.', ''); // Float 3 décimales
                                } else {
                                    $row[] = 'NA';
                                    $row[] = '0';
                                    $row[] = '0.000';
                                    $row[] = '0.000';
                                }
                            }
                        } else {
                            // Remplir les colonnes vides pour le groupe
                            $row[] = 'NA';  // nom
                            $row[] = 'NA';  // commentaire
                            for ($j = 0; $j < 4; $j++) {
                                $row[] = 'NA';   // nom média
                                $row[] = '0';    // interactions
                                $row[] = '0.000'; // x
                                $row[] = '0.000'; // y
                            }
                        }
                    }

                    $csv->insertOne($row);
                }

                echo $csv->toString();
            }, "sessions-export-" . date('Y-m-d') . '.csv', [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="sessions-export-' . date('Y-m-d') . '.csv"'
            ]);
        } catch (\Exception $e) {
            Log::error('Export error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Une erreur est survenue lors de l\'export des sessions'
            ], 500);
        }
    }

    private function cleanText(?string $text): string
    {
        if (empty($text)) return 'NA';
        return str_replace(["\n", "\r", ",", ";"], [" ", " ", " ", " "], $text);
    }

    private function getExperimenterType(ExperimentSession $session): string
    {
        if (!$session->experimentLink) {
            return 'NA';
        }

        if ($session->experimentLink->user_id === $session->experiment->created_by) {
            return 'creator';
        }

        if ($session->experimentLink->user->created_by === $session->experiment->created_by) {
            return 'secondary';
        }

        return 'collaborator';
    }
}
