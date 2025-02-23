<?php

namespace App\Filament\Pages\Experiments\Sessions;

use App\Models\Experiment;
use App\Models\ExperimentSession;
use App\Traits\HasExperimentAccess;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class DeleteSelectedSessions extends Page
{
    use HasExperimentAccess;

    protected static bool $shouldRegisterNavigation = false;
    protected static string $view = 'filament.pages.experiments.sessions.delete-selected-sessions';

    public array $recordIds = [];
    public ?int $experiment_id = null;

    public function mount(): void
    {
        $this->recordIds = session('selected_sessions', []);
        if (empty($this->recordIds)) {
            redirect()->route('filament.admin.resources.experiment-sessions.index', [
                'record' => request()->query('record')
            ])->with('error', 'Aucune session sélectionnée');
            return;
        }

        $session = ExperimentSession::find($this->recordIds[0]);
        if (!$session) {
            redirect()->route('filament.admin.resources.experiment-sessions.index', [
                'record' => request()->query('record')
            ])->with('error', 'Session introuvable');
            return;
        }

        $this->experiment_id = $session->experiment_id;
    }

    public function deleteSelected()
    {
        $experiment = Experiment::findOrFail($this->experiment_id);

        if (Auth::id() !== $experiment->created_by) {
            Notification::make()
                ->title('Erreur')
                ->body('Seul le créateur de l\'expérimentation peut supprimer les sessions.')
                ->danger()
                ->send();
            return redirect()->route('filament.admin.resources.experiment-sessions.index', [
                'record' => $this->experiment_id
            ]);
        }

        // Vérifier que ces IDs existent réellement
        $existingIds = ExperimentSession::whereIn('id', $this->recordIds)
            ->where('experiment_id', $this->experiment_id)
            ->pluck('id')
            ->toArray();

        if (empty($existingIds)) {
            Notification::make()
                ->warning()
                ->title('Aucune session valide sélectionnée')
                ->send();
            return redirect()->route('filament.admin.resources.experiment-sessions.index', [
                'record' => $this->experiment_id
            ]);
        }

        $count = count($existingIds);
        ExperimentSession::whereIn('id', $existingIds)->delete();

        Notification::make()
            ->title('Succès')
            ->body("{$count} sessions ont été supprimées avec succès !")
            ->success()
            ->send();

        // Nettoyer la session
        session()->forget('selected_sessions');

        return redirect()->route('filament.admin.resources.experiment-sessions.index', [
            'record' => $this->experiment_id
        ]);
    }

    public function getTitle(): string
    {
        $count = count($this->recordIds);
        return "Supprimer {$count} session" . ($count > 1 ? 's' : '');
    }
}
