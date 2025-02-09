<?php

namespace App\Filament\Resources\ExperimentAccessRequestResource\Pages;

use App\Filament\Resources\ExperimentAccessRequestResource;
use App\Models\ExperimentAccessRequest;
use App\Notifications\AccessRequestProcessed;
use App\Notifications\AccessUpgradedNotification;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditExperimentAccessRequest extends EditRecord
{
    protected static string $resource = ExperimentAccessRequestResource::class;

    private const SHOULD_DUPLICATE_DOCUMENTS = false;

    protected function afterSave(): void
    {
        $record = $this->record;

        // Si le statut a changé pour approved ou rejected
        if ($record->wasChanged('status') && in_array($record->status, ['approved', 'rejected'])) {

            if ($record->status === 'approved' && $record->type === 'access') {
                // Vérifier s'il y avait une demande "results" approuvée précédemment
                $existingResultsRequest = ExperimentAccessRequest::where('user_id', $record->user_id)
                    ->where('experiment_id', $record->experiment_id)
                    ->where('type', 'results')
                    ->where('status', 'approved')
                    ->first();

                if ($existingResultsRequest) {
                    // Si oui, envoyer la notification de mise à niveau
                    $record->user->notify(new AccessUpgradedNotification($record));
                    // Supprimer l'ancienne demande
                    $existingResultsRequest->delete();
                } else {
                    // Si non, envoyer la notification standard
                    $record->user->notify(new AccessRequestProcessed($record, true));
                }
            } else {
                // Pour les autres cas, envoyer la notification standard
                $record->user->notify(new AccessRequestProcessed($record, $record->status === 'approved'));
            }

            // Si c'est une demande de duplication approuvée
            if ($record->status === 'approved' && $record->type === 'duplicate') {
                try {
                    $originalExperiment = $record->experiment;

                    // Créer une nouvelle expérience
                    $newExperiment = $originalExperiment->replicate();
                    $newExperiment->created_by = $record->user_id;
                    $newExperiment->original_creator_id = $originalExperiment->created_by;
                    $newExperiment->name = $originalExperiment->name . ' ' . __('filament.resources.experiment-access-request.form.duplicate.copy');
                    $newExperiment->doi = null;
                    $newExperiment->responsible_institution = null;
                    $newExperiment->save();

                    // Dupliquer les médias
                    if ($originalExperiment->media) {
                        $newMedia = [];
                        foreach ($originalExperiment->media as $mediaPath) {
                            $filename = basename($mediaPath);
                            $newTimestamp = now()->format('Y-m-d-His');
                            $newName = $newTimestamp . '-' . substr($filename, 20);

                            $newPath = 'experiments/' . ($originalExperiment->type === 'image' ? 'images/' : ($originalExperiment->type === 'sound' ? 'audio/' : 'mixed/')) . $newName;

                            Storage::disk('public')->copy($mediaPath, $newPath);
                            $newMedia[] = $newPath;
                        }
                        $newExperiment->media = $newMedia;
                        $newExperiment->save();
                    }

                    // Dupliquer les documents si nécessaire
                    if (self::SHOULD_DUPLICATE_DOCUMENTS && $originalExperiment->documents) {
                        $newDocs = [];
                        foreach ($originalExperiment->documents as $docPath) {
                            $filename = basename($docPath);
                            $newTimestamp = now()->format('Y-m-d-His');
                            $newName = $newTimestamp . '-' . substr($filename, 20);

                            $newPath = 'experiments/documents/' . $newName;
                            Storage::disk('public')->copy($docPath, $newPath);
                            $newDocs[] = $newPath;
                        }
                        $newExperiment->documents = $newDocs;
                        $newExperiment->save();
                    }

                    // Envoyer la notification à l'utilisateur
                    $record->user->notify(new AccessRequestProcessed($record, true));

                    // Supprimer la demande
                    $record->delete();

                    Notification::make()
                        ->title(__('filament.resources.experiment-access-request.form.duplicate.success'))
                        ->success()
                        ->send();
                } catch (\Exception $e) {
                    Notification::make()
                        ->title(__('filament.resources.experiment-access-request.form.duplicate.error'))
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            } else {
                // Pour les autres types de demandes, envoyer juste la notification
                $record->user->notify(new AccessRequestProcessed($record, $record->status === 'approved'));
            }

            // Redirection dans tous les cas
            $this->redirect($this->getResource()::getUrl('index'));
        }
    }

    protected function authorizeAccess(): void
    {
        parent::authorizeAccess();

        $record = $this->getRecord();

        // if ($record->status !== 'pending') {
        //     $this->redirect($this->getResource()::getUrl('index'));
        //     return;
        // }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
