<?php

namespace Database\Seeders;

use App\Models\Experiment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SupervisorExperimentSeeder extends Seeder
{
    private $imageFiles;
    private $soundFiles;

    public function __construct()
    {
        $this->imageFiles = array_filter(
            glob(base_path('images/*')),
            fn($file) => in_array(pathinfo($file, PATHINFO_EXTENSION), ['jpg', 'png'])
        );

        $this->soundFiles = array_filter(
            glob(base_path('sounds/*')),
            fn($file) => pathinfo($file, PATHINFO_EXTENSION) === 'wav'
        );
    }

    public function run()
    {
        if (empty($this->imageFiles) || empty($this->soundFiles)) {
            $this->command->error('Les fichiers médias sont manquants !');
            return;
        }

        $supervisor = User::role('supervisor')->first();

        if (!$supervisor) {
            $this->command->error('Aucun superviseur trouvé !');
            return;
        }

        $types = [
            'image' => 'Démonstration - Images',
            'sound' => 'Démonstration - Sons',
            'image_sound' => 'Démonstration - Images et Sons'
        ];

        foreach ($types as $type => $name) {
            $experiment = Experiment::create([
                'name' => $name,
                'description' => "Cette expérience de démonstration vous permet de tester la catégorie $type de notre plateforme.",
                'instruction' => "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed non risus. Suspendisse lectus tortor, dignissim sit amet, adipiscing nec, ultricies sed, dolor. Cras elementum ultrices diam. Maecenas ligula massa, varius a, semper congue, euismod non, mi. Proin porttitor, orci nec nonummy molestie, enim est eleifend mi, non fermentum diam nisl sit amet erat.",
                'type' => $type,
                'button_size' => 60,
                'button_color' => '#ff1414',
                'created_by' => $supervisor->id,
                'status' => 'test',
                'link' => Str::random(6),
                'media' => $this->getMediaForType($type),
                'documents' => null,
                'doi' => "10.1234/demo.$type." . Str::random(2) . date('Y'),
                'howitwork_page' => true
            ]);

            $this->command->info("Expérience de démonstration créée : $name");
        }
    }

    private function getMediaForType(string $type): array
    {
        $media = [];

        switch ($type) {
            case 'image':
                // Sélection de 4 images
                $selectedImages = array_rand($this->imageFiles, min(13, count($this->imageFiles)));
                foreach ((array)$selectedImages as $index) {
                    $path = $this->imageFiles[$index];
                    $filename = basename($path);
                    Storage::disk('public')->put("media/{$filename}", file_get_contents($path));
                    $media[] = "media/{$filename}";
                }
                break;

            case 'sound':
                // Sélection de 4 sons
                $selectedSounds = array_rand($this->soundFiles, min(9, count($this->soundFiles)));
                foreach ((array)$selectedSounds as $index) {
                    $path = $this->soundFiles[$index];
                    $filename = basename($path);
                    Storage::disk('public')->put("media/{$filename}", file_get_contents($path));
                    $media[] = "media/{$filename}";
                }
                break;

            case 'image_sound':
                // 2 images et 2 sons
                $selectedImages = array_rand($this->imageFiles, min(13, count($this->imageFiles)));
                $selectedSounds = array_rand($this->soundFiles, min(9, count($this->soundFiles)));

                foreach ((array)$selectedImages as $index) {
                    $path = $this->imageFiles[$index];
                    $filename = basename($path);
                    Storage::disk('public')->put("media/{$filename}", file_get_contents($path));
                    $media[] = "media/{$filename}";
                }

                foreach ((array)$selectedSounds as $index) {
                    $path = $this->soundFiles[$index];
                    $filename = basename($path);
                    Storage::disk('public')->put("media/{$filename}", file_get_contents($path));
                    $media[] = "media/{$filename}";
                }
                break;
        }

        return $media;
    }
}
