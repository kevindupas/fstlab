<?php

namespace App\Observers;

use App\Models\Experiment;
use Illuminate\Support\Facades\Storage;

class ExperimentObserver
{
    public function deleting(Experiment $experiment)
    {
        // Supprimer les médias
        if ($experiment->media) {
            foreach ($experiment->media as $mediaPath) {
                Storage::disk('public')->delete($mediaPath);
            }
        }

        // Supprimer les documents
        if ($experiment->documents) {
            foreach ($experiment->documents as $documentPath) {
                Storage::disk('public')->delete($documentPath);
            }
        }

        // Suppression en cascade des relations
        $experiment->links()->delete();
        $experiment->accessRequests()->delete();
        $experiment->sessions()->delete();
    }
}
