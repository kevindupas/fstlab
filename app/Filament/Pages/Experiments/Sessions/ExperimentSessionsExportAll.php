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
                ->with('error', 'Expérimentation non trouvée');
            return;
        }

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
            echo "\xEF\xBB\xBF"; // BOM UTF-8

            $csv = Writer::createFromString();
            $csv->setDelimiter(',');
            $csv->setEnclosure('"');

            $allRows = [];
            foreach ($sessions as $session) {
                $csvData = $this->prepareExportData($data, $session);
                $allRows = array_merge($allRows, $csvData);
            }

            if (!empty($allRows)) {
                $csv->insertOne(array_keys($allRows[0]));
                $csv->insertAll($allRows);
            }

            echo $csv->toString();
        }, "all-sessions-export-" . date('Y-m-d') . '.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    // protected function prepareExportData(array $formData, ExperimentSession $session): array
    // {

    // }

    public function getTitle(): string
    {
        $experiment = Experiment::find($this->experiment_id);
        $tabName = match ($this->currentTab) {
            'creator' => 'du créateur',
            'mine' => 'mes résultats',
            'collaborators' => 'des collaborateurs',
            default => 'tous les résultats'
        };

        return "Export {$tabName} - {$experiment->name}";
    }
}
