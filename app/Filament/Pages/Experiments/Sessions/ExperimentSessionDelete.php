<?php

namespace App\Filament\Pages\Experiments\Sessions;

use App\Models\ExperimentSession;
use App\Traits\HasExperimentAccess;
use Filament\Pages\Page;
use Filament\Notifications\Notification;

class ExperimentSessionDelete extends Page
{
    use HasExperimentAccess;

    protected static bool $shouldRegisterNavigation = false;
    protected static string $view = 'filament.pages.experiments.sessions.experiment-session-delete';

    public ExperimentSession $record;
    public $experiment;

    public function mount(): void
    {
        $recordId = request()->route('record');
        $this->record = ExperimentSession::findOrFail($recordId);
        $this->experiment = request()->route('experiment');
    }

    public function delete()
    {
        $experimentId = $this->record->experiment_id;
        $this->record->delete();

        Notification::make()
            ->success()
            ->title(__('filament.pages.experiments_sessions.notifications.delete_success'))
            ->send();

        return redirect()->route('filament.admin.resources.experiment-sessions.index', [
            'record' => $experimentId
        ]);
    }

    public function getTitle(): string
    {
        if (!$this->record) {
            return 'Suppression de session';
        }

        return __('filament.pages.experiments_sessions.delete.title', [
            'number' => $this->record->participant_number
        ]);
    }
}
