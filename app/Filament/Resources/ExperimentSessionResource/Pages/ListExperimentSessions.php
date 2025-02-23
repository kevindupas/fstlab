<?php

namespace App\Filament\Resources\ExperimentSessionResource\Pages;

use App\Filament\Resources\ExperimentSessionResource;
use App\Models\Experiment;
use App\Models\ExperimentSession;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;

class ListExperimentSessions extends ListRecords
{
    protected static string $resource = ExperimentSessionResource::class;
    protected static string $view = 'filament.resources.experiment-sessions.pages.list-sessions';

    // Propriétés publiques pour les données du Blade
    public $experimentId;
    public $currentTab;
    public $searchTerm;
    public $isCreator;
    public $isSecondaryAccount;
    public $hasCollaboratorAccess;
    public $counts;
    public $selected_ids = [];
    public $selectedSessionIds = [];

    public function mount(): void
    {
        $this->experimentId = request()->query('record');
        $this->currentTab = request()->query('tab');
        $this->searchTerm = request()->query('search');

        if (!$this->experimentId) {
            return;
        }

        $experiment = Experiment::find($this->experimentId);
        $this->isCreator = $experiment?->created_by === Auth::id();
        $this->isSecondaryAccount = Auth::user()->created_by === $experiment?->created_by;
        $this->hasCollaboratorAccess = $experiment
            ->accessRequests()
            ->where('user_id', Auth::id())
            ->where('type', 'access')
            ->where('status', 'approved')
            ->exists();

        $baseQuery = ExperimentSession::where('experiment_id', $this->experimentId);
        if ($this->searchTerm) {
            $baseQuery->where(function ($q) use ($experiment) {
                $q->where('group_data', 'like', "%{$this->searchTerm}%")
                    ->orWhere('feedback', 'like', "%{$this->searchTerm}%");
            });
        }

        $allQuery = clone $baseQuery;
        $creatorQuery = clone $baseQuery;
        $creatorQuery->whereHas('experimentLink', function ($q) use ($experiment) {
            $q->where(function ($subQ) {
                $subQ->where('is_creator', true)->orWhere('is_secondary', true);
            });
        });
        $myQuery = clone $baseQuery;
        $myQuery->whereHas('experimentLink', function ($q) {
            $q->where('user_id', Auth::id());
        });
        $collaboratorsQuery = clone $baseQuery;
        $collaboratorsQuery->whereHas('experimentLink', function ($q) {
            $q->where('is_collaborator', true)->where('user_id', '!=', Auth::id());
        });

        $this->counts = [
            'all' => $allQuery->count(),
            'creator' => $creatorQuery->count(),
            'mine' => $myQuery->count(),
            'collaborators' => $collaboratorsQuery->count(),
        ];
    }

    public function table(Table $table): Table
    {
        $experimentId = request()->query('record');
        $tab = request()->query('tab');
        $search = request()->query('search');
        $experiment = Experiment::find($experimentId);

        $hasCollaboratorAccess = $experiment ? $experiment->accessRequests()
            ->where('user_id', Auth::id())
            ->where('type', 'access')
            ->where('status', 'approved')
            ->exists() : false;

        return parent::table($table)
            ->modifyQueryUsing(function (Builder $query) use ($experimentId, $tab, $experiment, $search, $hasCollaboratorAccess) {
                if ($experimentId) {
                    $query->where('experiment_id', $experimentId);
                    if ($search) {
                        $query->where(function ($q) use ($search) {
                            $q->where('group_data', 'like', "%{$search}%")
                                ->orWhere('feedback', 'like', "%{$search}%");
                        });
                    }
                    if ($experiment) {
                        switch ($tab) {
                            case 'creator':
                                $query->whereHas('experimentLink', function ($q) use ($experiment) {
                                    $q->where('is_creator', true)
                                        ->orWhere('is_secondary', true);
                                });
                                break;
                            case 'mine':
                                if (!$hasCollaboratorAccess) {
                                    $query->where('id', 0);
                                } else {
                                    $query->whereHas('experimentLink', function ($q) {
                                        $q->where('user_id', Auth::id());
                                    });
                                }
                                break;
                            case 'collaborators':
                                $query->whereHas('experimentLink', function ($q) use ($experiment) {
                                    $q->whereHas('user', function ($userQ) use ($experiment) {
                                        $userQ->where(function ($sq) use ($experiment) {
                                            $sq->where('id', '!=', $experiment->created_by)
                                                ->where(function ($innerQ) use ($experiment) {
                                                    $innerQ->where('created_by', '!=', $experiment->created_by)
                                                        ->orWhereNull('created_by');
                                                });
                                        })->where('user_id', '!=', Auth::id());
                                    });
                                });
                                break;
                        }
                    }
                }
                return $query;
            });
    }

    public function deleteSession($sessionId)
    {
        $session = ExperimentSession::findOrFail($sessionId);

        if (!$session->experiment || Auth::id() !== $session->experiment->created_by) {
            Notification::make()
                ->title('Erreur')
                ->body('Seul le créateur de l\'expérimentation peut supprimer cette session.')
                ->danger()
                ->send();
            return;
        }

        $session->delete();

        Notification::make()
            ->title('Succès')
            ->body('Session supprimée avec succès !')
            ->success()
            ->send();

        return redirect()->route('filament.admin.resources.experiment-sessions.index', [
            'record' => $session->experiment_id,
        ]);
    }

    #[On('deleteAllSessions')]
    public function deleteAllSessions()
    {
        Log::debug('Début de deleteAllSessions');
        Log::debug('ExperimentId from property:', ['id' => $this->experimentId]);

        $experiment = Experiment::findOrFail($this->experimentId);

        if (Auth::id() !== $experiment->created_by) {
            Notification::make()
                ->title('Erreur')
                ->body('Seul le créateur de l\'expérimentation peut supprimer toutes les sessions.')
                ->danger()
                ->send();
            return;
        }

        ExperimentSession::where('experiment_id', $this->experimentId)->delete();

        Notification::make()
            ->title('Succès')
            ->body('Toutes les sessions ont été supprimées avec succès !')
            ->success()
            ->send();

        return redirect()->route('filament.admin.resources.experiment-sessions.index', [
            'record' => $this->experimentId,
        ]);
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }

    protected function notify($type, $message)
    {
        $this->dispatch('notify', type: $type, message: $message);
    }
}
