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

class BulkExperimentSessionExport extends Page
{
    use HasExperimentAccess;

    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $slug = 'bulk-experiment-session-export';
    protected static string $view = 'filament.pages.experiments.sessions.bulk-experiment-session-export';

    public array $recordIds = [];
    public ?int $experiment_id = null;
    public ?array $data = [];

    public ?array $basic_fields = [];
    public ?array $group_fields = [];

    public function mount(): void
    {
        $this->recordIds = session('selected_sessions', []);
        if (empty($this->recordIds)) {
            redirect()->route('filament.admin.resources.experiment-sessions.index')
                ->with('error', __('pages.bulk_experiment_session_export.error.no_selection'));
            return;
        }

        $this->experiment_id = ExperimentSession::find($this->recordIds[0])->experiment_id;

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
                Tabs::make(__('pages.bulk_experiment_session_export.export_options.title'))
                    ->tabs([
                        Tabs\Tab::make(__('pages.bulk_experiment_session_export.export_options.basic'))
                            ->schema([
                                CheckboxList::make('basic_fields')
                                    ->label(__('pages.bulk_experiment_session_export.labels.select_fields'))
                                    ->options([
                                        'participant_number' => __('pages.bulk_experiment_session_export.basic_fields.participant_number'),
                                        'experimenter_info' => __('pages.bulk_experiment_session_export.basic_fields.experimenter_info'),
                                        'dates' => __('pages.bulk_experiment_session_export.basic_fields.dates'),
                                        'duration' => __('pages.bulk_experiment_session_export.basic_fields.duration'),
                                        'system_info' => __('pages.bulk_experiment_session_export.basic_fields.system_info'),
                                        'feedback' => __('pages.bulk_experiment_session_export.basic_fields.feedback'),
                                    ])
                                    ->columns(2)
                                    ->helperText(__('pages.bulk_experiment_session_export.helper_text.basic')),
                            ]),

                        Tabs\Tab::make(__('pages.bulk_experiment_session_export.export_options.group'))
                            ->schema([
                                CheckboxList::make('group_fields')
                                    ->label(__('pages.bulk_experiment_session_export.labels.select_group_data'))
                                    ->options([
                                        'group_names' => __('pages.bulk_experiment_session_export.group_fields.group_names'),
                                        'group_comments' => __('pages.bulk_experiment_session_export.group_fields.group_comments'),
                                        'media_info' => __('pages.bulk_experiment_session_export.group_fields.media_info')
                                    ])
                                    ->columns(2)
                                    ->helperText(__('pages.bulk_experiment_session_export.helper_text.group')),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public function export(): StreamedResponse
    {
        $sessions = ExperimentSession::findMany($this->recordIds);
        $data = $this->form->getState();

        return response()->streamDownload(function () use ($sessions, $data) {
            $csv = Writer::createFromString();
            $csv->setDelimiter(',');
            $csv->setEnclosure('"');

            // Construction des en-têtes en fonction des sélections
            $headers = [__('pages.bulk_experiment_session_export.csv_headers.session_id')];

            if (in_array('participant_number', $data['basic_fields'])) {
                $headers[] = __('pages.bulk_experiment_session_export.csv_headers.participant_number');
            }
            if (in_array('experimenter_info', $data['basic_fields'])) {
                $headers[] = __('pages.bulk_experiment_session_export.csv_headers.experimenter_name');
                $headers[] = __('pages.bulk_experiment_session_export.csv_headers.experimenter_type');
            }
            if (in_array('dates', $data['basic_fields'])) {
                $headers[] = __('pages.bulk_experiment_session_export.csv_headers.created_at');
                $headers[] = __('pages.bulk_experiment_session_export.csv_headers.completed_at');
            }
            if (in_array('duration', $data['basic_fields'])) {
                $headers[] = __('pages.bulk_experiment_session_export.csv_headers.duration_seconds');
            }
            if (in_array('system_info', $data['basic_fields'])) {
                $headers[] = __('pages.bulk_experiment_session_export.csv_headers.browser');
                $headers[] = __('pages.bulk_experiment_session_export.csv_headers.system');
                $headers[] = __('pages.bulk_experiment_session_export.csv_headers.device');
                $headers[] = __('pages.bulk_experiment_session_export.csv_headers.screen_width');
                $headers[] = __('pages.bulk_experiment_session_export.csv_headers.screen_height');
            }
            if (in_array('feedback', $data['basic_fields'])) {
                $headers[] = __('pages.bulk_experiment_session_export.csv_headers.feedback');
            }

            // En-têtes pour les groupes
            if (!empty($data['group_fields'])) {
                for ($i = 1; $i <= 3; $i++) {
                    if (in_array('group_names', $data['group_fields'])) {
                        $headers[] = __('pages.bulk_experiment_session_export.csv_headers.group_name', ['number' => $i]);
                    }
                    if (in_array('group_comments', $data['group_fields'])) {
                        $headers[] = __('pages.bulk_experiment_session_export.csv_headers.group_comment', ['number' => $i]);
                    }
                    if (in_array('media_info', $data['group_fields'])) {
                        for ($j = 1; $j <= 4; $j++) {
                            $headers[] = __('pages.bulk_experiment_session_export.csv_headers.media_name', ['group' => $i, 'number' => $j]);
                            $headers[] = __('pages.bulk_experiment_session_export.csv_headers.media_interactions', ['group' => $i, 'number' => $j]);
                            $headers[] = __('pages.bulk_experiment_session_export.csv_headers.media_x', ['group' => $i, 'number' => $j]);
                            $headers[] = __('pages.bulk_experiment_session_export.csv_headers.media_y', ['group' => $i, 'number' => $j]);
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
                    $row[] = $session->experimentLink?->user?->name ?? __('pages.bulk_experiment_session_export.values.na');
                    $row[] = __('pages.bulk_experiment_session_export.experimenter_types.' . $this->getExperimenterType($session));
                }
                if (in_array('dates', $data['basic_fields'])) {
                    $row[] = $session->created_at->format('Y-m-d H:i:s');
                    $row[] = $session->completed_at?->format('Y-m-d H:i:s') ?? __('pages.bulk_experiment_session_export.values.na');
                }
                if (in_array('duration', $data['basic_fields'])) {
                    $row[] = number_format($session->duration / 1000, 3, '.', '');
                }
                if (in_array('system_info', $data['basic_fields'])) {
                    $row[] = $session->browser ?? __('pages.bulk_experiment_session_export.values.na');
                    $row[] = $session->operating_system ?? __('pages.bulk_experiment_session_export.values.na');
                    $row[] = $session->device_type ?? __('pages.bulk_experiment_session_export.values.na');
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
                            $row[] = $group ? $this->cleanText($group['name'] ?? __('pages.bulk_experiment_session_export.values.na')) : __('pages.bulk_experiment_session_export.values.na');
                        }
                        if (in_array('group_comments', $data['group_fields'])) {
                            $row[] = $group ? $this->cleanText($group['comment'] ?? __('pages.bulk_experiment_session_export.values.na')) : __('pages.bulk_experiment_session_export.values.na');
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
                                    $row[] = __('pages.bulk_experiment_session_export.values.na');
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
        }, __('pages.bulk_experiment_session_export.download_filename', ['date' => date('Y-m-d')]), [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . __('pages.bulk_experiment_session_export.download_filename', ['date' => date('Y-m-d')]) . '"'
        ]);
    }

    private function cleanText(?string $text): string
    {
        if (empty($text)) return __('pages.bulk_experiment_session_export.values.na');
        return str_replace(["\n", "\r", ",", ";"], [" ", " ", " ", " "], $text);
    }

    private function getExperimenterType(ExperimentSession $session): string
    {
        if (!$session->experimentLink) {
            return 'na';
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
        $count = count($this->recordIds);
        return __('pages.bulk_experiment_session_export.title', [
            'count' => $count,
            'plural' => $count > 1 ? 's' : ''
        ]);
    }
}
