<?php

namespace App\Filament\Pages\Experiments\Sessions;

use App\Models\ExperimentSession;
use App\Traits\HasExperimentAccess;
use Filament\Forms\Form;
use Filament\Pages\Page;
use League\Csv\Writer;

class BulkExperimentSessionExport extends Page
{
    use HasExperimentAccess;

    protected static bool $shouldRegisterNavigation = false;
    protected static string $view = 'filament.pages.experiments.sessions.bulk-experiment-session-export';

    public array $recordIds = [];
    public ?int $experiment_id = null;
    public string $export_format = 'matrix';
    public string $csv_delimiter = 'tab';

    public function mount(): void
    {
        $this->recordIds = session('selected_sessions', []);
        if (empty($this->recordIds)) {
            redirect()->route('filament.admin.resources.experiment-sessions.index')
                ->with('error', __('pages.bulk_experiment_session_export.error.no_selection'));
            return;
        }

        $this->experiment_id = ExperimentSession::find($this->recordIds[0])->experiment_id;
    }

    private function getDelimiter(): string
    {
        return match ($this->csv_delimiter) {
            'tab' => "\t",
            'comma' => ',',
            'semicolon' => ';',
        };
    }

    private function cleanMediaName(string $filename): string
    {
        return preg_replace('/^\d{4}-\d{2}-\d{2}-\d{6}-/', '', basename($filename));
    }

    public function export()
    {
        $sessions = ExperimentSession::query()
            ->whereIn('id', $this->recordIds)
            ->where('status', 'completed')
            ->get();

        return match ($this->export_format) {
            'matrix' => $this->exportMatrix($sessions),
            'individual' => $this->exportIndividualFiles($sessions),
            'both' => $this->exportBoth($sessions),
        };
    }

    private function exportMatrix($sessions)
    {
        return response()->streamDownload(function () use ($sessions) {
            $this->writeMatrixContent($sessions);
        }, 'matrice_' . date('Y-m-d') . '.csv');
    }

    private function writeMatrixContent($sessions)
    {
        $csv = Writer::createFromString();
        $csv->setDelimiter($this->getDelimiter());
        $csv->setEnclosure('"');

        // En-têtes avec les IDs des participants
        $headers = [''];  // Cellule vide pour la première colonne
        foreach ($sessions as $session) {
            $headers[] = $session->participant_number;
        }
        $csv->insertOne($headers);

        // Mapping des médias -> groupes pour chaque session
        $mediaGroups = [];
        $allMediaNames = [];

        foreach ($sessions as $session) {
            $groupData = json_decode($session->group_data, true) ?? [];
            foreach ($groupData as $groupIndex => $group) {
                foreach ($group['elements'] ?? [] as $element) {
                    $mediaName = $this->cleanMediaName($element['url']);
                    $mediaGroups[$mediaName][$session->id] = $groupIndex + 1;
                    $allMediaNames[$mediaName] = true;
                }
            }
        }

        // Trie les noms des médias
        $allMediaNames = array_keys($allMediaNames);
        sort($allMediaNames);

        // Une ligne par média
        foreach ($allMediaNames as $mediaName) {
            $row = [$mediaName];
            foreach ($sessions as $session) {
                $row[] = $mediaGroups[$mediaName][$session->id] ?? '';
            }
            $csv->insertOne($row);
        }

        echo "\xEF\xBB\xBF"; // BOM UTF-8
        echo $csv->toString();
    }

    private function exportIndividualFiles($sessions)
    {
        $tempDir = storage_path('app/temp');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $zip = new \ZipArchive();
        $zipFileName = $tempDir . '/export_' . uniqid() . '.zip';

        if ($zip->open($zipFileName, \ZipArchive::CREATE) !== true) {
            throw new \RuntimeException("Could not create ZIP archive");
        }

        foreach ($sessions as $session) {
            $participantId = $session->participant_number;
            $groupData = json_decode($session->group_data, true) ?? [];

            // Fichier principal
            $mainContent = $this->generateParticipantContent($participantId, $groupData, false);
            $zip->addFromString($participantId . '.csv', $mainContent);

            // Fichier commentaires
            $commentContent = $this->generateParticipantContent($participantId, $groupData, true);
            $zip->addFromString($participantId . '-comment.csv', $commentContent);
        }

        $zip->close();

        return response()->download($zipFileName, 'export_participants_' . date('Y-m-d') . '.zip')
            ->deleteFileAfterSend();
    }

    private function exportBoth($sessions)
    {
        $tempDir = storage_path('app/temp');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $zip = new \ZipArchive();
        $zipFileName = $tempDir . '/export_' . uniqid() . '.zip';

        if ($zip->open($zipFileName, \ZipArchive::CREATE) !== true) {
            throw new \RuntimeException("Could not create ZIP archive");
        }

        // Ajoute la matrice
        ob_start();
        $this->writeMatrixContent($sessions);
        $matrixContent = ob_get_clean();
        $zip->addFromString('matrice.csv', $matrixContent);

        // Ajoute les fichiers individuels
        foreach ($sessions as $session) {
            $participantId = $session->participant_number;
            $groupData = json_decode($session->group_data, true) ?? [];

            // Fichier principal
            $mainContent = $this->generateParticipantContent($participantId, $groupData, false);
            $zip->addFromString('participants/' . $participantId . '.csv', $mainContent);

            // Fichier commentaires
            $commentContent = $this->generateParticipantContent($participantId, $groupData, true);
            $zip->addFromString('participants/' . $participantId . '-comment.csv', $commentContent);
        }

        $zip->close();

        return response()->download($zipFileName, 'export_complet_' . date('Y-m-d') . '.zip')
            ->deleteFileAfterSend();
    }

    private function generateParticipantContent($participantId, $groupData, $withComments): string
    {
        $delimiter = $this->getDelimiter();
        $content = $participantId . "\n";
        foreach ($groupData as $group) {
            $mediaNames = array_map(fn($e) => $this->cleanMediaName($e['url']), $group['elements'] ?? []);
            $content .= implode($delimiter, $mediaNames) . "\n";
            if ($withComments) {
                $content .= ($group['comment'] ?? '') . "\n";
            }
        }
        return $content;
    }

    public function getTitle(): string
    {
        $count = count($this->recordIds);
        return __('pages.bulk_experiment_session_export.title', [
            'count' => $count,
            'plural' => $count > 1 ? 's' : ''
        ]);
    }
}
