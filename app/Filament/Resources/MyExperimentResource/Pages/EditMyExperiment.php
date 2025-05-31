<?php

namespace App\Filament\Resources\MyExperimentResource\Pages;

use App\Filament\Resources\MyExperimentResource;
use App\Models\ExperimentLink;
use Filament\Actions;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class EditMyExperiment extends EditRecord
{
    protected static string $resource = MyExperimentResource::class;

    public function getTitle(): string
    {
        return __('actions.edit_experiment');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label(__('actions.delete_experiment'))
                ->modalHeading(__('actions.delete_experiment'))
                ->requiresConfirmation(false)
                ->modalDescription(
                    fn($record) =>
                    $record->access_requests_count()->count() > 0 || $record->shared_links_count()->count() > 0
                        ? __('filament.resources.my_experiment.actions.delete.desc_issues_delete')
                        : __('filament.resources.my_experiment.actions.delete.confirm_delete')
                )
                ->modalSubmitActionLabel(__('actions.delete_experiment'))
                ->hidden(fn($record) => $record->access_requests_count()->count() > 0 || $record->shared_links_count()->count() > 0)
                ->form([
                    TextInput::make('confirmation_code')
                        ->label(__('filament.resources.my_experiment.actions.delete.code_confirm'))
                        ->required()
                        ->helperText(function () {
                            $code = Str::random(6);
                            session(['delete_code' => $code]);
                            return __('filament.resources.my_experiment.actions.delete.code') . ' : ' . $code;
                        })
                        ->rules(['required', 'string'])
                ])
                ->before(function (array $data) {
                    if ($data['confirmation_code'] !== session('delete_code')) {
                        Notification::make()
                            ->title(__('filament.resources.my_experiment.actions.delete.code_fail'))
                            ->danger()
                            ->send();

                        $this->halt();
                    }
                })
        ];
    }

    protected function afterSave(): void
    {
        // Récupérer le lien existant
        $experimentLink = ExperimentLink::where('experiment_id', $this->record->id)
            ->where('user_id', Auth::id())
            ->first();

        if ($experimentLink) {
            // Mettre à jour le lien si un nouveau a été généré
            if (isset($this->data['temp_link'])) {
                $experimentLink->update([
                    'link' => $this->data['temp_link'],
                    'status' => $this->data['status']
                ]);
            } else {
                $experimentLink->update([
                    'status' => $this->data['status']
                ]);
            }
        }
    }
}
