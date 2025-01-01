<?php

namespace App\Filament\Pages\Experiments\Sessions;

use App\Models\ExperimentSession;
use App\Traits\HasExperimentAccess;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Form;
use Filament\Pages\Page;
use League\Csv\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExperimentSessionExport extends Page
{
    use HasExperimentAccess;

    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $slug = 'experiment-session-export/{record}';
    protected static ?string $model = ExperimentSession::class;
    protected static string $view = 'filament.pages.experiments.sessions.experiment-session-export';

    public ExperimentSession $record;
    public ?int $experiment_id = null;
    public ?array $data = [];

    public ?array $basic_fields = [];
    public ?array $group_fields = [];

    public function mount(): void
    {

        // Vérifie si l'enregistrement existe
        if (!$this->record) {
            redirect()->route('filament.admin.resources.experiment-sessions.index')->with('error', 'Session not found');
            return;
        }

        $this->experiment_id = $this->record->experiment_id;
        $this->basic_fields = ['participant_number', 'created_at', 'duration'];
        $this->form->fill();

        // Valeurs par défaut
        $this->basic_fields = [
            'participant_number',
            'experimenter_info',
            'dates',
            'duration',
            'system_info',
            'feedback'
        ];
        $this->group_fields = [
            'group_names',
            'group_comments',
            'media_info'
        ];

        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Export Options')
                    ->tabs([
                        Tabs\Tab::make('Informations basiques')
                            ->schema([
                                CheckboxList::make('basic_fields')
                                    ->label('Sélectionner les champs')
                                    ->options([
                                        'participant_number' => 'Numéro du participant',
                                        'experimenter_info' => 'Informations sur l\'expérimentateur (nom et type)',
                                        'dates' => 'Dates (création et complétion)',
                                        'duration' => 'Durée',
                                        'system_info' => 'Informations système (navigateur, OS, appareil, résolution)',
                                        'feedback' => 'Feedback',
                                    ])
                                    ->columns(2)
                                    ->helperText('Ces informations seront exportées pour chaque session'),
                            ]),

                        Tabs\Tab::make('Données des groupes')
                            ->schema([
                                CheckboxList::make('group_fields')
                                    ->label('Sélectionner les données des groupes')
                                    ->options([
                                        'group_names' => 'Noms des groupes',
                                        'group_comments' => 'Commentaires des groupes',
                                        'media_info' => 'Informations sur les médias (noms, positions, interactions)'
                                    ])
                                    ->columns(2)
                                    ->helperText('Ces informations seront exportées pour chaque groupe'),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public function export(): StreamedResponse
    {
        $sessions = collect([$this->record]);
        $data = $this->form->getState();

        return response()->streamDownload(function () use ($sessions, $data) {
            $csv = Writer::createFromString();
            $csv->setDelimiter(',');
            $csv->setEnclosure('"');

            // Construction des en-têtes en fonction des sélections
            $headers = ['session_id'];

            if (in_array('participant_number', $data['basic_fields'])) {
                $headers[] = 'participant_number';
            }
            if (in_array('experimenter_info', $data['basic_fields'])) {
                $headers[] = 'experimenter_name';
                $headers[] = 'experimenter_type';
            }
            if (in_array('dates', $data['basic_fields'])) {
                $headers[] = 'created_at';
                $headers[] = 'completed_at';
            }
            if (in_array('duration', $data['basic_fields'])) {
                $headers[] = 'duration_seconds';
            }
            if (in_array('system_info', $data['basic_fields'])) {
                $headers[] = 'browser';
                $headers[] = 'system';
                $headers[] = 'device';
                $headers[] = 'screen_width';
                $headers[] = 'screen_height';
            }
            if (in_array('feedback', $data['basic_fields'])) {
                $headers[] = 'feedback';
            }

            // En-têtes pour les groupes
            if (!empty($data['group_fields'])) {
                for ($i = 1; $i <= 3; $i++) {
                    if (in_array('group_names', $data['group_fields'])) {
                        $headers[] = "group{$i}_name";
                    }
                    if (in_array('group_comments', $data['group_fields'])) {
                        $headers[] = "group{$i}_comment";
                    }
                    if (in_array('media_info', $data['group_fields'])) {
                        for ($j = 1; $j <= 4; $j++) {
                            $headers[] = "group{$i}_media{$j}_name";
                            $headers[] = "group{$i}_media{$j}_interactions";
                            $headers[] = "group{$i}_media{$j}_x";
                            $headers[] = "group{$i}_media{$j}_y";
                        }
                    }
                }
            }

            $csv->insertOne($headers);

            foreach ($sessions as $session) {
                $row = [(int)$session->id];

                if (in_array('participant_number', $data['basic_fields'])) {
                    $row[] = (string)$session->participant_number;
                }
                if (in_array('experimenter_info', $data['basic_fields'])) {
                    $row[] = $session->experimentLink?->user?->name ?? 'NA';
                    $row[] = $this->getExperimenterType($session);
                }
                if (in_array('dates', $data['basic_fields'])) {
                    $row[] = $session->created_at->format('Y-m-d H:i:s');
                    $row[] = $session->completed_at?->format('Y-m-d H:i:s') ?? 'NA';
                }
                if (in_array('duration', $data['basic_fields'])) {
                    $row[] = number_format($session->duration / 1000, 3, '.', '');
                }
                if (in_array('system_info', $data['basic_fields'])) {
                    $row[] = $session->browser ?? 'NA';
                    $row[] = $session->operating_system ?? 'NA';
                    $row[] = $session->device_type ?? 'NA';
                    $row[] = (string)$session->screen_width;
                    $row[] = (string)$session->screen_height;
                }
                if (in_array('feedback', $data['basic_fields'])) {
                    $row[] = $this->cleanText($session->feedback);
                }

                // Données des groupes
                if (!empty($data['group_fields'])) {
                    $groupData = json_decode($session->group_data, true) ?? [];
                    for ($i = 0; $i < 3; $i++) {
                        $group = $groupData[$i] ?? null;

                        if (in_array('group_names', $data['group_fields'])) {
                            $row[] = $group ? $this->cleanText($group['name'] ?? 'NA') : 'NA';
                        }
                        if (in_array('group_comments', $data['group_fields'])) {
                            $row[] = $group ? $this->cleanText($group['comment'] ?? 'NA') : 'NA';
                        }
                        if (in_array('media_info', $data['group_fields'])) {
                            $elements = $group ? ($group['elements'] ?? []) : [];
                            for ($j = 0; $j < 4; $j++) {
                                $element = $elements[$j] ?? null;
                                if ($element) {
                                    $row[] = basename($element['url']);
                                    $row[] = (int)($element['interactions'] ?? 0);
                                    $row[] = number_format($element['x'] ?? 0, 3, '.', '');
                                    $row[] = number_format($element['y'] ?? 0, 3, '.', '');
                                } else {
                                    $row[] = 'NA';
                                    $row[] = '0';
                                    $row[] = '0.000';
                                    $row[] = '0.000';
                                }
                            }
                        }
                    }
                }

                $csv->insertOne($row);
            }

            echo "\xEF\xBB\xBF"; // BOM UTF-8
            echo $csv->toString();
        }, "sessions-export-" . date('Y-m-d') . '.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="sessions-export-' . date('Y-m-d') . '.csv"'
        ]);
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

        if ($session->experimentLink->is_creator) {
            return 'creator';
        }

        if ($session->experimentLink->is_secondary) {
            return 'secondary';
        }

        return 'collaborator';
    }

    public function getTitle(): string
    {
        return __('filament.pages.experiments_sessions_export.title', [
            'participant' => $this->record->participant_number
        ]);
    }
}
