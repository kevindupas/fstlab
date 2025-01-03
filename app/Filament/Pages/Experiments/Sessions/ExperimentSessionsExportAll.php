<?php

namespace App\Filament\Pages\Experiments\Sessions;

use App\Models\Experiment;
use App\Models\ExperimentSession;
use App\Traits\HasExperimentAccess;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use League\Csv\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExperimentSessionsExportAll extends Page
{
    use HasExperimentAccess;

    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $slug = 'experiment-sessions-export-all';
    protected static string $view = 'filament.pages.experiments.sessions.experiment-sessions-export-all';

    public ?int $experiment_id = null;
    public ?string $currentTab = null;
    public ?array $data = [];

    public ?array $basic_fields = [];
    public ?array $group_fields = [];

    public function mount(): void
    {
        $this->experiment_id = request()->query('record');
        $this->currentTab = request()->query('tab');

        if (!$this->experiment_id) {
            redirect()->route('filament.admin.resources.experiment-sessions.index')
                ->with('error', __('pages.experiment_sessions_export_all.error.experiment_not_found'));
            return;
        }

        $this->basic_fields = ['participant_number', 'created_at', 'duration'];
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make(__('pages.experiment_sessions_export_all.export_options.title'))
                    ->tabs([
                        Tabs\Tab::make(__('pages.experiment_sessions_export_all.export_options.basic'))
                            ->schema([
                                CheckboxList::make('basic_fields')
                                    ->label(__('pages.experiment_sessions_export_all.labels.select_fields'))
                                    ->options([
                                        'participant_number' => __('pages.experiment_sessions_export_all.basic_fields.participant_number'),
                                        'experimenter_info' => __('pages.experiment_sessions_export_all.basic_fields.experimenter_info'),
                                        'dates' => __('pages.experiment_sessions_export_all.basic_fields.dates'),
                                        'duration' => __('pages.experiment_sessions_export_all.basic_fields.duration'),
                                        'system_info' => __('pages.experiment_sessions_export_all.basic_fields.system_info'),
                                        'feedback' => __('pages.experiment_sessions_export_all.basic_fields.feedback'),
                                    ])
                                    ->columns(2)
                                    ->helperText(__('pages.experiment_sessions_export_all.helper_text.basic')),
                            ]),

                        Tabs\Tab::make(__('pages.experiment_sessions_export_all.export_options.group'))
                            ->schema([
                                CheckboxList::make('group_fields')
                                    ->label(__('pages.experiment_sessions_export_all.labels.select_group_data'))
                                    ->options([
                                        'group_names' => __('pages.experiment_sessions_export_all.group_fields.group_names'),
                                        'group_comments' => __('pages.experiment_sessions_export_all.group_fields.group_comments'),
                                        'media_info' => __('pages.experiment_sessions_export_all.group_fields.media_info')
                                    ])
                                    ->columns(2)
                                    ->helperText(__('pages.experiment_sessions_export_all.helper_text.group')),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public function export(): StreamedResponse
    {
        $query = ExperimentSession::query()
            ->where('experiment_id', $this->experiment_id)
            ->where('status', 'completed');

        // Appliquer les filtres selon l'onglet actif
        if ($this->currentTab === 'creator') {
            $experiment = Experiment::find($this->experiment_id);
            $query->whereHas('experimentLink', function ($q) use ($experiment) {
                $q->where('is_creator', true)
                    ->orWhere('is_secondary', true);
            });
        } elseif ($this->currentTab === 'mine') {
            $query->whereHas('experimentLink', function ($q) {
                $q->where('user_id', Auth::id());
            });
        } elseif ($this->currentTab === 'collaborators') {
            $query->whereHas('experimentLink', function ($q) {
                $q->where('is_collaborator', true)
                    ->where('user_id', '!=', Auth::id());
            });
        }

        $sessions = $query->get();
        $data = $this->form->getState();

        return response()->streamDownload(function () use ($sessions, $data) {
            $csv = Writer::createFromString();
            $csv->setDelimiter(',');
            $csv->setEnclosure('"');

            // Construction des en-têtes en fonction des sélections
            $headers = [__('pages.experiment_sessions_export_all.csv_headers.session_id')];

            if (in_array('participant_number', $data['basic_fields'])) {
                $headers[] = __('pages.experiment_sessions_export_all.csv_headers.participant_number');
            }
            if (in_array('experimenter_info', $data['basic_fields'])) {
                $headers[] = __('pages.experiment_sessions_export_all.csv_headers.experimenter_name');
                $headers[] = __('pages.experiment_sessions_export_all.csv_headers.experimenter_type');
            }
            if (in_array('dates', $data['basic_fields'])) {
                $headers[] = __('pages.experiment_sessions_export_all.csv_headers.created_at');
                $headers[] = __('pages.experiment_sessions_export_all.csv_headers.completed_at');
            }
            if (in_array('duration', $data['basic_fields'])) {
                $headers[] = __('pages.experiment_sessions_export_all.csv_headers.duration_seconds');
            }
            if (in_array('system_info', $data['basic_fields'])) {
                $headers[] = __('pages.experiment_sessions_export_all.csv_headers.browser');
                $headers[] = __('pages.experiment_sessions_export_all.csv_headers.system');
                $headers[] = __('pages.experiment_sessions_export_all.csv_headers.device');
                $headers[] = __('pages.experiment_sessions_export_all.csv_headers.screen_width');
                $headers[] = __('pages.experiment_sessions_export_all.csv_headers.screen_height');
            }
            if (in_array('feedback', $data['basic_fields'])) {
                $headers[] = __('pages.experiment_sessions_export_all.csv_headers.feedback');
            }

            // En-têtes pour les groupes
            if (!empty($data['group_fields'])) {
                for ($i = 1; $i <= 3; $i++) {
                    if (in_array('group_names', $data['group_fields'])) {
                        $headers[] = __('pages.experiment_sessions_export_all.csv_headers.group_name', ['number' => $i]);
                    }
                    if (in_array('group_comments', $data['group_fields'])) {
                        $headers[] = __('pages.experiment_sessions_export_all.csv_headers.group_comment', ['number' => $i]);
                    }
                    if (in_array('media_info', $data['group_fields'])) {
                        for ($j = 1; $j <= 4; $j++) {
                            $headers[] = __('pages.experiment_sessions_export_all.csv_headers.media_name', ['group' => $i, 'number' => $j]);
                            $headers[] = __('pages.experiment_sessions_export_all.csv_headers.media_interactions', ['group' => $i, 'number' => $j]);
                            $headers[] = __('pages.experiment_sessions_export_all.csv_headers.media_x', ['group' => $i, 'number' => $j]);
                            $headers[] = __('pages.experiment_sessions_export_all.csv_headers.media_y', ['group' => $i, 'number' => $j]);
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
                    $row[] = $session->experimentLink?->user?->name ?? __('pages.experiment_sessions_export_all.values.na');
                    $row[] = __('pages.experiment_sessions_export_all.experimenter_types.' . $this->getExperimenterType($session));
                }
                if (in_array('dates', $data['basic_fields'])) {
                    $row[] = $session->created_at->format('Y-m-d H:i:s');
                    $row[] = $session->completed_at?->format('Y-m-d H:i:s') ?? __('pages.experiment_sessions_export_all.values.na');
                }
                if (in_array('duration', $data['basic_fields'])) {
                    $row[] = number_format($session->duration / 1000, 3, '.', '');
                }
                if (in_array('system_info', $data['basic_fields'])) {
                    $row[] = $session->browser ?? __('pages.experiment_sessions_export_all.values.na');
                    $row[] = $session->operating_system ?? __('pages.experiment_sessions_export_all.values.na');
                    $row[] = $session->device_type ?? __('pages.experiment_sessions_export_all.values.na');
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
                            $row[] = $group ? $this->cleanText($group['name'] ?? __('pages.experiment_sessions_export_all.values.na')) : __('pages.experiment_sessions_export_all.values.na');
                        }
                        if (in_array('group_comments', $data['group_fields'])) {
                            $row[] = $group ? $this->cleanText($group['comment'] ?? __('pages.experiment_sessions_export_all.values.na')) : __('pages.experiment_sessions_export_all.values.na');
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
                                    $row[] = __('pages.experiment_sessions_export_all.values.na');
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
        }, __('pages.experiment_sessions_export_all.download_filename', ['date' => date('Y-m-d')]), [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . __('pages.experiment_sessions_export_all.download_filename', ['date' => date('Y-m-d')]) . '"'
        ]);
    }

    private function cleanText(?string $text): string
    {
        if (empty($text)) return __('pages.experiment_sessions_export_all.values.na');
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
        $experiment = Experiment::find($this->experiment_id);
        $tabKey = match ($this->currentTab) {
            'creator' => 'creator',
            'mine' => 'mine',
            'collaborators' => 'collaborators',
            default => 'all'
        };

        return __('pages.experiment_sessions_export_all.title', [
            'tab' => __('pages.experiment_sessions_export_all.tabs.' . $tabKey),
            'name' => $experiment->name
        ]);
    }
}
