<?php

namespace App\Filament\Pages\Experiments\Sessions;

use App\Models\ExperimentSession;
use App\Traits\HasExperimentAccess;
use Filament\Forms\Form;
use Filament\Pages\Page;
use League\Csv\Writer;
use ZipArchive;

class ExperimentSessionExport extends Page
{
    use HasExperimentAccess;

    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $slug = 'experiment-session-export/{record}';
    protected static ?string $model = ExperimentSession::class;
    protected static string $view = 'filament.pages.experiments.sessions.experiment-session-export';

    public ExperimentSession $record;
    public ?int $experiment_id = null;
    public string $export_format = 'matrix';
    public string $csv_delimiter = 'tab';

    public function mount(): void
    {
        if (!$this->record) {
            redirect()->route('filament.admin.resources.experiment-sessions.index')
                ->with('error', __('pages.experiment_session_export.error.not_found'));
            return;
        }

        $this->experiment_id = $this->record->experiment_id;
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
        return match ($this->export_format) {
            'matrix' => $this->exportMatrix(),
            'individual' => $this->exportIndividualFiles(),
            'both' => $this->exportBoth(),
        };
    }

    private function exportMatrix()
    {
        return response()->streamDownload(function () {
            $csv = Writer::createFromString();
            $csv->setDelimiter($this->getDelimiter());

            // En-têtes avec l'ID du participant
            $headers = ['', $this->record->participant_number];
            $csv->insertOne($headers);

            // Mapping des médias -> groupes
            $mediaGroups = [];
            $allMediaNames = [];

            $groupData = json_decode($this->record->group_data, true) ?? [];
            foreach ($groupData as $groupIndex => $group) {
                foreach ($group['elements'] ?? [] as $element) {
                    $mediaName = $this->cleanMediaName($element['url']);
                    $mediaGroups[$mediaName] = $groupIndex + 1;
                    $allMediaNames[$mediaName] = true;
                }
            }

            // Trie les noms des médias
            $allMediaNames = array_keys($allMediaNames);
            sort($allMediaNames);

            // Une ligne par média
            foreach ($allMediaNames as $mediaName) {
                $row = [$mediaName, $mediaGroups[$mediaName] ?? ''];
                $csv->insertOne($row);
            }

            echo $csv->toString();
        }, 'matrice_' . $this->record->participant_number . '_' . date('Y-m-d') . '.csv');
    }

    private function exportIndividualFiles()
    {
        $tempDir = storage_path('app/temp');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $zip = new ZipArchive();
        $zipFileName = $tempDir . '/export_' . uniqid() . '.zip';

        if ($zip->open($zipFileName, ZipArchive::CREATE) !== true) {
            throw new \RuntimeException("Could not create ZIP archive");
        }

        $participantId = $this->record->participant_number;
        $groupData = json_decode($this->record->group_data, true) ?? [];

        // Fichier principal
        $mainContent = $this->generateParticipantContent($participantId, $groupData, false);
        $zip->addFromString($participantId . '.csv', $mainContent);

        // Fichier commentaires
        $commentContent = $this->generateParticipantContent($participantId, $groupData, true);
        $zip->addFromString($participantId . '-comment.csv', $commentContent);

        $zip->close();

        return response()->download($zipFileName, 'export_' . $this->record->participant_number . '_' . date('Y-m-d') . '.zip')
            ->deleteFileAfterSend();
    }

    private function exportBoth()
    {
        $tempDir = storage_path('app/temp');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $zip = new ZipArchive();
        $zipFileName = $tempDir . '/export_' . uniqid() . '.zip';

        if ($zip->open($zipFileName, ZipArchive::CREATE) !== true) {
            throw new \RuntimeException("Could not create ZIP archive");
        }

        // Ajoute la matrice
        ob_start();
        $csv = Writer::createFromString();
        $csv->setDelimiter($this->getDelimiter());

        // En-têtes avec l'ID du participant
        $headers = ['', $this->record->participant_number];
        $csv->insertOne($headers);

        // Mapping des médias -> groupes
        $mediaGroups = [];
        $allMediaNames = [];

        $groupData = json_decode($this->record->group_data, true) ?? [];
        foreach ($groupData as $groupIndex => $group) {
            foreach ($group['elements'] ?? [] as $element) {
                $mediaName = $this->cleanMediaName($element['url']);
                $mediaGroups[$mediaName] = $groupIndex + 1;
                $allMediaNames[$mediaName] = true;
            }
        }

        // Trie les noms des médias
        $allMediaNames = array_keys($allMediaNames);
        sort($allMediaNames);

        // Une ligne par média
        foreach ($allMediaNames as $mediaName) {
            $row = [$mediaName, $mediaGroups[$mediaName] ?? ''];
            $csv->insertOne($row);
        }

        $matrixContent = $csv->toString();
        ob_end_clean();

        $zip->addFromString('matrice.csv', $matrixContent);

        // Ajoute les fichiers individuels
        $participantId = $this->record->participant_number;

        // Fichier principal
        $mainContent = $this->generateParticipantContent($participantId, $groupData, false);
        $zip->addFromString($participantId . '.csv', $mainContent);

        // Fichier commentaires
        $commentContent = $this->generateParticipantContent($participantId, $groupData, true);
        $zip->addFromString($participantId . '-comment.csv', $commentContent);

        $zip->close();

        return response()->download($zipFileName, 'export_complet_' . $this->record->participant_number . '_' . date('Y-m-d') . '.zip')
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
        return __('pages.experiment_session_export.title', [
            'participant' => $this->record->participant_number
        ]);
    }
}
