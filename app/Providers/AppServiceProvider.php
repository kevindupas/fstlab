<?php

namespace App\Providers;

use Filament\Forms\Components\TextInput;
use Illuminate\Support\ServiceProvider;
use RalphJSmit\Filament\MediaLibrary\Facades\MediaLibrary;
use RalphJSmit\Filament\MediaLibrary\Media\Models\MediaLibraryItem;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        MediaLibrary::registerMediaInfoFormFields(function (array $schema, MediaLibraryItem $mediaLibraryItem): array {
            // Récupérer l'instance du modèle Media de Spatie en utilisant 'media_id' ou 'model_id'
            $media = Media::where('model_id', $mediaLibraryItem->id)->first();

            // S'assurer que le média existe avant de continuer
            if (!$media) {
                return $schema;
            }

            // Obtenir le nom du fichier depuis Spatie
            $fileName = pathinfo($media->file_name, PATHINFO_FILENAME);

            return array_merge($schema, [
                TextInput::make('file_name')
                    ->label('Nom du fichier')
                    ->afterStateHydrated(function (TextInput $component) use ($fileName) {
                        $component->state($fileName);
                    })
                    ->afterStateUpdated(function ($state) use ($media) {
                        // Mettre à jour uniquement le modèle Media de Spatie
                        $newFileName = pathinfo($state, PATHINFO_FILENAME) . '.' . pathinfo($media->file_name, PATHINFO_EXTENSION);

                        // Mettre à jour 'file_name' et 'name' dans la table 'media'
                        $media->file_name = $newFileName;
                        $media->name = pathinfo($state, PATHINFO_FILENAME);
                        $media->save();
                    }),
            ]);
        });
    }
}
