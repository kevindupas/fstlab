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
            abort(403, 'Seul le créateur peut exporter les données.');
        }

        $this->record = $record;

        // Définissez les valeurs par défaut ici
        $this->basic_fields = ['participant_number', 'created_at', 'duration'];

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
                                    ->label('Sélectionnez les champs à exporter')
                                    ->options([
                                        'participant_number' => 'Identifiant du participant',
                                        'created_at' => 'Date de création',
                                        'completed_at' => 'Date de complétion',
                                        'duration' => 'Durée (secondes)',
                                        'browser' => 'Navigateur',
                                        'operating_system' => 'Système d\'exploitation',
                                        'device_type' => 'Type d\'appareil',
                                        'screen_dimensions' => 'Dimensions de l\'écran',
                                        'feedback' => 'Feedback',
                                    ])
                                    ->columns(2)
                                    ->default([
                                        'participant_number',
                                        'created_at',
                                        'duration'
                                    ]),
                            ]),

                        Tabs\Tab::make('Données des groupes')
                            ->schema([
                                CheckboxList::make('group_fields')
                                    ->label('Sélectionnez les informations de groupe à exporter')
                                    ->options([
                                        'group_names' => 'Noms des groupes',
                                        'group_comments' => 'Commentaires des groupes',
                                        'media_positions' => 'Positions finales des médias',
                                        'media_interactions' => 'Nombre d\'interactions par média',
                                        'group_compositions' => 'Composition des groupes',
                                    ])
                                    ->columns(2),
                            ]),

                        Tabs\Tab::make('Journal des actions')
                            ->schema([
                                CheckboxList::make('action_fields')
                                    ->label('Sélectionnez les actions à exporter')
                                    ->options([
                                        'moves' => 'Déplacements',
                                        'sounds' => 'Écoutes de sons',
                                        'images' => 'Visualisations d\'images',
                                    ])
                                    ->columns(2),

                                Select::make('action_time_format')
                                    ->label('Format du temps')
                                    ->options([
                                        'timestamp' => 'Timestamp',
                                        'readable' => 'Format lisible (HH:mm:ss)',
                                        'elapsed' => 'Temps écoulé (secondes)',
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
            $csv->insertOne(array_keys($csvData[0])); // En-têtes
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

        // Structure pour garder l'ordre des colonnes
        $orderedColumns = [];

        // 1. D'abord les données de base si sélectionnées
        if (!empty($formData['basic_fields'])) {
            foreach ($formData['basic_fields'] as $field) {
                switch ($field) {
                    case 'participant_number':
                        $orderedColumns['Participant'] = '';
                        break;
                    case 'created_at':
                        $orderedColumns['Date création'] = '';
                        break;
                    case 'completed_at':
                        $orderedColumns['Date complétion'] = '';
                        break;
                    case 'duration':
                        $orderedColumns['Durée (s)'] = '';
                        break;
                    case 'browser':
                        $orderedColumns['Navigateur'] = '';
                        break;
                    case 'operating_system':
                        $orderedColumns['Système'] = '';
                        break;
                    case 'device_type':
                        $orderedColumns['Appareil'] = '';
                        break;
                    case 'screen_dimensions':
                        $orderedColumns['Dimensions écran'] = '';
                        break;
                    case 'feedback':
                        $orderedColumns['Feedback'] = '';
                        break;
                }
            }
        }

        // 2. Ensuite les données des groupes
        if (!empty($formData['group_fields'])) {
            foreach ($groupData as $groupIndex => $group) {
                $groupPrefix = "Groupe " . ($groupIndex + 1);

                if (in_array('group_names', $formData['group_fields'])) {
                    $orderedColumns["{$groupPrefix} - Nom"] = '';
                }
                if (in_array('group_comments', $formData['group_fields'])) {
                    $orderedColumns["{$groupPrefix} - Commentaire"] = '';
                }
                if (in_array('group_compositions', $formData['group_fields'])) {
                    $orderedColumns["{$groupPrefix} - Médias"] = '';
                }
                if (in_array('media_interactions', $formData['group_fields'])) {
                    foreach ($group['elements'] as $element) {
                        $mediaName = basename($element['url']);
                        $orderedColumns["{$groupPrefix} - {$mediaName} - Interactions"] = '';
                    }
                }
                if (in_array('media_positions', $formData['group_fields'])) {
                    foreach ($group['elements'] as $element) {
                        $mediaName = basename($element['url']);
                        $orderedColumns["{$groupPrefix} - {$mediaName} - Position"] = '';
                    }
                }
            }
        }

        // 3. Enfin, ajout des colonnes pour les actions si nécessaire
        if (!empty($formData['action_fields'])) {
            $orderedColumns['Temps'] = '';
            $orderedColumns['Type'] = '';
            $orderedColumns['Média'] = '';
            $orderedColumns['Position X'] = '';
            $orderedColumns['Position Y'] = '';
        }

        // Création de la première ligne avec les données de base
        $row = array_fill_keys(array_keys($orderedColumns), '');

        // Remplissage des données de base
        if (!empty($formData['basic_fields'])) {
            foreach ($formData['basic_fields'] as $field) {
                switch ($field) {
                    case 'participant_number':
                        $row['Participant'] = $this->record->participant_number;
                        break;
                    case 'created_at':
                        $row['Date création'] = $this->record->created_at->format('Y-m-d H:i:s');
                        break;
                    case 'completed_at':
                        $row['Date complétion'] = $this->record->completed_at->format('Y-m-d H:i:s');
                        break;
                    case 'duration':
                        $row['Durée (s)'] = number_format($this->record->duration / 1000, 2);
                        break;
                    case 'browser':
                        $row['Navigateur'] = $this->record->browser;
                        break;
                    case 'operating_system':
                        $row['Système'] = $this->record->operating_system;
                        break;
                    case 'device_type':
                        $row['Appareil'] = $this->record->device_type;
                        break;
                    case 'screen_dimensions':
                        $row['Dimensions écran'] = "{$this->record->screen_width}x{$this->record->screen_height}";
                        break;
                    case 'feedback':
                        $row['Feedback'] = $this->record->feedback;
                        break;
                }
            }
        }

        // Remplissage des données des groupes
        if (!empty($formData['group_fields'])) {
            foreach ($groupData as $groupIndex => $group) {
                $groupPrefix = "Groupe " . ($groupIndex + 1);

                if (in_array('group_names', $formData['group_fields'])) {
                    $row["{$groupPrefix} - Nom"] = $group['name'];
                }
                if (in_array('group_comments', $formData['group_fields'])) {
                    $row["{$groupPrefix} - Commentaire"] = $group['comment'] ?? '';
                }
                if (in_array('group_compositions', $formData['group_fields'])) {
                    $mediaNames = collect($group['elements'])
                        ->map(fn($element) => basename($element['url']))
                        ->join(', ');
                    $row["{$groupPrefix} - Médias"] = $mediaNames;
                }
                if (in_array('media_interactions', $formData['group_fields'])) {
                    foreach ($group['elements'] as $element) {
                        $mediaName = basename($element['url']);
                        $row["{$groupPrefix} - {$mediaName} - Interactions"] = $element['interactions'] ?? 0;
                    }
                }
                if (in_array('media_positions', $formData['group_fields'])) {
                    foreach ($group['elements'] as $element) {
                        $mediaName = basename($element['url']);
                        $row["{$groupPrefix} - {$mediaName} - Position"] =
                            "X:" . number_format($element['x'], 2) .
                            ", Y:" . number_format($element['y'], 2);
                    }
                }
            }
        }

        // Si on a des actions, créer plusieurs lignes
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
                            $actionRow['Temps'] = $action['time'];
                            break;
                        case 'readable':
                            $actionRow['Temps'] = \Carbon\Carbon::createFromTimestampMs($action['time'])
                                ->format('H:i:s.v');
                            break;
                        case 'elapsed':
                            $actionRow['Temps'] = number_format(
                                ($action['time'] - strtotime($this->record->created_at) * 1000) / 1000,
                                3
                            );
                            break;
                    }

                    $actionRow['Type'] = match($action['type']) {
                        'move' => 'Déplacement',
                        'sound' => 'Lecture son',
                        'image' => 'Vue image',
                    };

                    $actionRow['Média'] = basename($action['id']);

                    if ($action['type'] === 'move') {
                        $actionRow['Position X'] = number_format($action['x'], 2);
                        $actionRow['Position Y'] = number_format($action['y'], 2);
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
        return "Exporter les données de la session - {$this->record->participant_number}";
    }
}
