<?php

namespace App\Livewire;

use App\Models\Experiment;
use Livewire\Component;
use App\Models\ExperimentSession as ModelsExperimentSession;

class ExperimentSession extends Component
{
    public $experiment;
    public $media;
    public $showGroupModal = false;
    public $participant_name = '';
    public $participant_email = '';
    public $step = 1;
    public $groups = [];
    public $actionsLog = [];
    public $startTime;
    public $elapsedTime = 0;
    public $showModal = true;
    public $emailError = '';

    protected $rules = [
        'participant_name' => 'required|string|max:255',
        'participant_email' => 'required|email|max:255',
    ];

    public function mount($token)
    {
        $experiment = Experiment::where('link', $token)->first();

        if (!$experiment) {
            return redirect('/')->withErrors(['message' => 'No experiment associated with this token.']);
        }

        $mediaArray = is_string($experiment->media) ? json_decode($experiment->media, true) : $experiment->media;

        $this->media = collect($mediaArray)->map(function ($item) use ($experiment) {
            return (object) [
                'id' => $item,
                'url' => asset('storage/' . $item),
                'type' => $experiment->type,
                'button_size' => $experiment->button_size ?? '100',
                'button_color' => $experiment->button_color ?? '#0000FF',
                'x' => rand(0, 800),
                'y' => rand(0, 600)
            ];
        });

        $this->experiment = $experiment;
    }

    public function validateParticipant()
    {
        $this->validate();

        // Check if the email is already used for this experiment
        $existingSession = ModelsExperimentSession::where('experiment_id', $this->experiment->id)
            ->where('participant_email', $this->participant_email)
            ->first();

        if ($existingSession) {
            $this->emailError = 'This email has already been used for this experiment.';
            return false;
        }

        $this->emailError = '';
        return true;
    }

    public function saveParticipant()
    {
        if ($this->validateParticipant()) {
            ModelsExperimentSession::create([
                'experiment_id' => $this->experiment->id,
                'participant_name' => $this->participant_name,
                'participant_email' => $this->participant_email,
            ]);

            $this->showModal = false;
            $this->startExperiment();
        }
    }

    public function saveExperimentData($groupsData, $actionsLog, $elapsedTime)
    {
        ModelsExperimentSession::updateOrCreate(
            [
                'experiment_id' => $this->experiment->id,
                'participant_email' => $this->participant_email,
            ],
            [
                'participant_name' => $this->participant_name,
                'group_data' => json_encode($groupsData),
                'actions_log' => json_encode($actionsLog),
                'duration' => $elapsedTime,
            ]
        );

        $this->showGroupModal = false;
        $this->showModal = false;
    }

    public function toggleGroupModal($state)
    {
        $this->showGroupModal = $state;
    }

    public function render()
    {
        return view('livewire.experiment-session');
    }

    public function startExperiment()
    {
        $this->startTime = now();
    }

    public function endExperiment()
    {
        $this->elapsedTime = now()->diffInSeconds($this->startTime);
        $this->toggleGroupModal(true);
    }
}
