<?php

namespace App\Filament\Pages\Experiments\Sessions;

use App\Models\ExperimentSession;
use App\Traits\HasExperimentAccess;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
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
    public ?array $data = [];

    // Ajoutez ces propriétés publiques pour les champs du formulaire
    public ?array $basic_fields = [];
    public ?array $group_fields = [];
    public ?array $action_fields = [];
    public ?string $action_time_format = 'readable';

    public function mount(ExperimentSession $record): void
    {
        if ($record->experiment->created_by !== Auth::id()) {
            abort(403, __('filament.pages.experiments_sessions_export.access_denied'));
        }

        $this->record = $record;
        $this->basic_fields = ['participant_number', 'created_at', 'duration'];
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make(__('filament.pages.experiments_sessions_export.tabs.title'))
                    ->tabs([
                        Tabs\Tab::make(__('filament.pages.experiments_sessions_export.tabs.basic_info'))
                            ->schema([
                                CheckboxList::make('basic_fields')
                                    ->label(__('filament.pages.experiments_sessions_export.fields.basic_fields.label'))
                                    ->options([
                                        'participant_number' => __('filament.pages.experiments_sessions_export.fields.basic_fields.options.participant_number'),
                                        'created_at' => __('filament.pages.experiments_sessions_export.fields.basic_fields.options.created_at'),
                                        'completed_at' => __('filament.pages.experiments_sessions_export.fields.basic_fields.options.completed_at'),
                                        'duration' => __('filament.pages.experiments_sessions_export.fields.basic_fields.options.duration'),
                                        'browser' => __('filament.pages.experiments_sessions_export.fields.basic_fields.options.browser'),
                                        'operating_system' => __('filament.pages.experiments_sessions_export.fields.basic_fields.options.operating_system'),
                                        'device_type' => __('filament.pages.experiments_sessions_export.fields.basic_fields.options.device_type'),
                                        'screen_dimensions' => __('filament.pages.experiments_sessions_export.fields.basic_fields.options.screen_dimensions'),
                                        'feedback' => __('filament.pages.experiments_sessions_export.fields.basic_fields.options.feedback'),
                                    ])
                                    ->columns(2)
                                    ->default([
                                        'participant_number',
                                        'created_at',
                                        'duration'
                                    ]),
                            ]),

                        Tabs\Tab::make(__('filament.pages.experiments_sessions_export.tabs.group_data'))
                            ->schema([
                                CheckboxList::make('group_fields')
                                    ->label(__('filament.pages.experiments_sessions_export.fields.group_fields.label'))
                                    ->options([
                                        'group_names' => __('filament.pages.experiments_sessions_export.fields.group_fields.options.group_names'),
                                        'group_comments' => __('filament.pages.experiments_sessions_export.fields.group_fields.options.group_comments'),
                                        'media_positions' => __('filament.pages.experiments_sessions_export.fields.group_fields.options.media_positions'),
                                        'media_interactions' => __('filament.pages.experiments_sessions_export.fields.group_fields.options.media_interactions'),
                                        'group_compositions' => __('filament.pages.experiments_sessions_export.fields.group_fields.options.group_compositions'),
                                    ])
                                    ->columns(2),
                            ]),

                        Tabs\Tab::make(__('filament.pages.experiments_sessions_export.tabs.action_log'))
                            ->schema([
                                CheckboxList::make('action_fields')
                                    ->label(__('filament.pages.experiments_sessions_export.fields.action_fields.label'))
                                    ->options([
                                        'moves' => __('filament.pages.experiments_sessions_export.fields.action_fields.options.moves'),
                                        'sounds' => __('filament.pages.experiments_sessions_export.fields.action_fields.options.sounds'),
                                        'images' => __('filament.pages.experiments_sessions_export.fields.action_fields.options.images'),
                                    ])
                                    ->columns(2),

                                Select::make('action_time_format')
                                    ->label(__('filament.pages.experiments_sessions_export.fields.time_format.label'))
                                    ->options([
                                        'timestamp' => __('filament.pages.experiments_sessions_export.fields.time_format.options.timestamp'),
                                        'readable' => __('filament.pages.experiments_sessions_export.fields.time_format.options.readable'),
                                        'elapsed' => __('filament.pages.experiments_sessions_export.fields.time_format.options.elapsed'),
                                    ])
                                    ->default('readable'),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public function export(): StreamedResponse
    {
        $data = $this->form->getState();
        $csvData = $this->prepareExportData($data);

        return response()->streamDownload(function () use ($csvData) {
            $csv = Writer::createFromString();
            $csv->insertOne(array_keys($csvData[0]));
            $csv->insertAll($csvData);

            echo $csv->toString();
        }, "session-{$this->record->id}-" . date('Y-m-d') . '.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    protected function prepareExportData(array $formData): array
    {
        $exportData = [];
        $groupData = json_decode($this->record->group_data, true);
        $actionsLog = json_decode($this->record->actions_log, true);

        $orderedColumns = [];

        // 1. Données de base
        if (!empty($formData['basic_fields'])) {
            foreach ($formData['basic_fields'] as $field) {
                switch ($field) {
                    case 'participant_number':
                        $orderedColumns[__('filament.pages.experiments_sessions_export.csv.participant')] = '';
                        break;
                    case 'created_at':
                        $orderedColumns[__('filament.pages.experiments_sessions_export.csv.created_at')] = '';
                        break;
                    case 'completed_at':
                        $orderedColumns[__('filament.pages.experiments_sessions_export.csv.completed_at')] = '';
                        break;
                    case 'duration':
                        $orderedColumns[__('filament.pages.experiments_sessions_export.csv.duration')] = '';
                        break;
                    case 'browser':
                        $orderedColumns[__('filament.pages.experiments_sessions_export.csv.browser')] = '';
                        break;
                    case 'operating_system':
                        $orderedColumns[__('filament.pages.experiments_sessions_export.csv.system')] = '';
                        break;
                    case 'device_type':
                        $orderedColumns[__('filament.pages.experiments_sessions_export.csv.device')] = '';
                        break;
                    case 'screen_dimensions':
                        $orderedColumns[__('filament.pages.experiments_sessions_export.csv.screen')] = '';
                        break;
                    case 'feedback':
                        $orderedColumns[__('filament.pages.experiments_sessions_export.csv.feedback')] = '';
                        break;
                }
            }
        }

        // 2. Données des groupes
        if (!empty($formData['group_fields'])) {
            foreach ($groupData as $groupIndex => $group) {
                $groupNumber = $groupIndex + 1;
                $groupPrefix = __('filament.pages.experiments_sessions_export.csv.group_prefix', ['number' => $groupNumber]);

                if (in_array('group_names', $formData['group_fields'])) {
                    $orderedColumns["{$groupPrefix} - " . __('filament.pages.experiments_sessions_export.csv.name')] = '';
                }
                if (in_array('group_comments', $formData['group_fields'])) {
                    $orderedColumns["{$groupPrefix} - " . __('filament.pages.experiments_sessions_export.csv.comment')] = '';
                }
                if (in_array('group_compositions', $formData['group_fields'])) {
                    $orderedColumns["{$groupPrefix} - " . __('filament.pages.experiments_sessions_export.csv.media')] = '';
                }
                if (in_array('media_interactions', $formData['group_fields'])) {
                    foreach ($group['elements'] as $element) {
                        $mediaName = basename($element['url']);
                        $orderedColumns["{$groupPrefix} - {$mediaName} - " . __('filament.pages.experiments_sessions_export.csv.interactions')] = '';
                    }
                }
                if (in_array('media_positions', $formData['group_fields'])) {
                    foreach ($group['elements'] as $element) {
                        $mediaName = basename($element['url']);
                        $orderedColumns["{$groupPrefix} - {$mediaName} - " . __('filament.pages.experiments_sessions_export.csv.position')] = '';
                    }
                }
            }
        }

        // 3. Actions
        if (!empty($formData['action_fields'])) {
            $orderedColumns[__('filament.pages.experiments_sessions_export.csv.time')] = '';
            $orderedColumns[__('filament.pages.experiments_sessions_export.csv.type')] = '';
            $orderedColumns[__('filament.pages.experiments_sessions_export.csv.media')] = '';
            $orderedColumns[__('filament.pages.experiments_sessions_export.csv.position_x')] = '';
            $orderedColumns[__('filament.pages.experiments_sessions_export.csv.position_y')] = '';
        }

        // Création de la ligne avec les données de base
        $row = array_fill_keys(array_keys($orderedColumns), '');

        // Remplissage des données de base
        if (!empty($formData['basic_fields'])) {
            foreach ($formData['basic_fields'] as $field) {
                switch ($field) {
                    case 'participant_number':
                        $row[__('filament.pages.experiments_sessions_export.csv.participant')] = $this->record->participant_number;
                        break;
                    case 'created_at':
                        $row[__('filament.pages.experiments_sessions_export.csv.created_at')] = $this->record->created_at->format('Y-m-d H:i:s');
                        break;
                    case 'completed_at':
                        $row[__('filament.pages.experiments_sessions_export.csv.completed_at')] = $this->record->completed_at->format('Y-m-d H:i:s');
                        break;
                    case 'duration':
                        $row[__('filament.pages.experiments_sessions_export.csv.duration')] = number_format($this->record->duration / 1000, 2);
                        break;
                    case 'browser':
                        $row[__('filament.pages.experiments_sessions_export.csv.browser')] = $this->record->browser;
                        break;
                    case 'operating_system':
                        $row[__('filament.pages.experiments_sessions_export.csv.system')] = $this->record->operating_system;
                        break;
                    case 'device_type':
                        $row[__('filament.pages.experiments_sessions_export.csv.device')] = $this->record->device_type;
                        break;
                    case 'screen_dimensions':
                        $row[__('filament.pages.experiments_sessions_export.csv.screen')] = "{$this->record->screen_width}x{$this->record->screen_height}";
                        break;
                    case 'feedback':
                        $row[__('filament.pages.experiments_sessions_export.csv.feedback')] = $this->record->feedback;
                        break;
                }
            }
        }

        // Remplissage des données des groupes
        if (!empty($formData['group_fields'])) {
            foreach ($groupData as $groupIndex => $group) {
                $groupNumber = $groupIndex + 1;
                $groupPrefix = __('filament.pages.experiments_sessions_export.csv.group_prefix', ['number' => $groupNumber]);

                if (in_array('group_names', $formData['group_fields'])) {
                    $row["{$groupPrefix} - " . __('filament.pages.experiments_sessions_export.csv.name')] = $group['name'];
                }
                if (in_array('group_comments', $formData['group_fields'])) {
                    $row["{$groupPrefix} - " . __('filament.pages.experiments_sessions_export.csv.comment')] = $group['comment'] ?? '';
                }
                if (in_array('group_compositions', $formData['group_fields'])) {
                    $mediaNames = collect($group['elements'])
                        ->map(fn($element) => basename($element['url']))
                        ->join(', ');
                    $row["{$groupPrefix} - " . __('filament.pages.experiments_sessions_export.csv.media')] = $mediaNames;
                }
                if (in_array('media_interactions', $formData['group_fields'])) {
                    foreach ($group['elements'] as $element) {
                        $mediaName = basename($element['url']);
                        $row["{$groupPrefix} - {$mediaName} - " . __('filament.pages.experiments_sessions_export.csv.interactions')] = $element['interactions'] ?? 0;
                    }
                }
                if (in_array('media_positions', $formData['group_fields'])) {
                    foreach ($group['elements'] as $element) {
                        $mediaName = basename($element['url']);
                        $row["{$groupPrefix} - {$mediaName} - " . __('filament.pages.experiments_sessions_export.csv.position')] =
                            __('filament.pages.experiments_sessions_export.csv.position_format', [
                                'x' => number_format($element['x'], 2),
                                'y' => number_format($element['y'], 2)
                            ]);
                    }
                }
            }
        }

        // Actions
        if (!empty($formData['action_fields'])) {
            $actionRows = [];
            foreach ($actionsLog as $action) {
                if (
                    (in_array('moves', $formData['action_fields']) && $action['type'] === 'move') ||
                    (in_array('sounds', $formData['action_fields']) && $action['type'] === 'sound') ||
                    (in_array('images', $formData['action_fields']) && $action['type'] === 'image')
                ) {
                    $actionRow = $row;

                    switch ($formData['action_time_format']) {
                        case 'timestamp':
                            $actionRow[__('filament.pages.experiments_sessions_export.csv.time')] = $action['time'];
                            break;
                        case 'readable':
                            $actionRow[__('filament.pages.experiments_sessions_export.csv.time')] = \Carbon\Carbon::createFromTimestampMs($action['time'])->format('H:i:s.v');
                            break;
                        case 'elapsed':
                            $actionRow[__('filament.pages.experiments_sessions_export.csv.time')] = number_format(
                                ($action['time'] - strtotime($this->record->created_at) * 1000) / 1000,
                                3
                            );
                            break;
                    }

                    $actionRow[__('filament.pages.experiments_sessions_export.csv.type')] = match ($action['type']) {
                        'move' => __('filament.pages.experiments_sessions_export.csv.action_types.move'),
                        'sound' => __('filament.pages.experiments_sessions_export.csv.action_types.sound'),
                        'image' => __('filament.pages.experiments_sessions_export.csv.action_types.image'),
                    };

                    $actionRow[__('filament.pages.experiments_sessions_export.csv.media')] = basename($action['id']);

                    if ($action['type'] === 'move') {
                        $actionRow[__('filament.pages.experiments_sessions_export.csv.position_x')] = number_format($action['x'], 2);
                        $actionRow[__('filament.pages.experiments_sessions_export.csv.position_y')] = number_format($action['y'], 2);
                    }

                    $actionRows[] = $actionRow;
                }
            }

            $exportData = !empty($actionRows) ? $actionRows : [$row];
        } else {
            $exportData = [$row];
        }

        return $exportData;
    }

    public function getTitle(): string
    {
        return __('filament.pages.experiments_sessions_export.title', [
            'participant' => $this->record->participant_number
        ]);
    }
}
