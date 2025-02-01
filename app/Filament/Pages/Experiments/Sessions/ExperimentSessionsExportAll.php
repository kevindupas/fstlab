<?php

namespace App\Filament\Pages\Experiments\Sessions;

use App\Models\Experiment;
use App\Models\ExperimentSession;
use App\Traits\HasExperimentAccess;
use Filament\Forms\Components\Radio;
use Filament\Forms\Form;
use Filament\Pages\Page;
use League\Csv\Writer;
use ZipArchive;

class ExperimentSessionsExportAll extends Page
{
    use HasExperimentAccess;
    protected static bool $shouldRegisterNavigation = false;
    protected static string $view = 'filament.pages.experiments.sessions.experiment-sessions-export-all';


    public ?int $experiment_id = null;
    public string $export_format = 'matrix';
    public string $csv_delimiter = 'tab';

    public function mount(): void
    {
        $this->experiment_id = request()->query('record');

        if (!$this->experiment_id) {
            redirect()->route('filament.admin.resources.experiment-sessions.index')
                ->with('error', 'Expérience non trouvée');
            return;
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Radio::make('export_format')
                    ->label(false)
                    ->options([
                        'matrix' => 'Format Matrice',
                        'individual' => 'Format Fichiers Individuels',
                        'both' => 'Les Deux Formats',
                    ])
                    ->default('matrix')
                    ->inline()
                    ->live(),

                Radio::make('csv_delimiter')
                    ->label(false)
                    ->options([
                        'tab' => 'Tabulation',
                        'comma' => 'Virgule (,)',
                        'semicolon' => 'Point-virgule (;)',
                    ])
                    ->default('tab')
                    ->inline()
                    ->live()
            ]);
    }

    private function getDelimiter(): string
    {
        return match ($this->csv_delimiter) {
            'tab' => "\t",
            'comma' => ',',
            'semicolon' => ';',
        };
    }

    public function export()
    {
        $sessions = ExperimentSession::query()
            ->where('experiment_id', $this->experiment_id)
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

        $headers = [''];
        foreach ($sessions as $session) {
            $headers[] = $session->participant_number;
        }
        $csv->insertOne($headers);

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

        $allMediaNames = array_keys($allMediaNames);
        sort($allMediaNames);

        foreach ($allMediaNames as $mediaName) {
            $row = [$mediaName];
            foreach ($sessions as $session) {
                $row[] = $mediaGroups[$mediaName][$session->id] ?? '';
            }
            $csv->insertOne($row);
        }

        echo $csv->toString();
    }

    private function exportIndividualFiles($sessions)
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

        $zip = new ZipArchive();
        $zipFileName = $tempDir . '/export_' . uniqid() . '.zip';

        if ($zip->open($zipFileName, ZipArchive::CREATE) !== true) {
            throw new \RuntimeException("Could not create ZIP archive");
        }

        // Ajoute la matrice
        ob_start();
        $this->writeMatrixContent($sessions);
        $matrixContent = ob_get_clean();
        $zip->addFromString('matrice.csv', $matrixContent);

        // Ajoute les fichiers individuels dans un sous-dossier
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

    private function cleanMediaName(string $filename): string
    {
        return preg_replace('/^\d{4}-\d{2}-\d{2}-\d{6}-/', '', basename($filename));
    }

    public function getTitle(): string
    {
        return "Export des données";
    }
}
