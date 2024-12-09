<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;

class TranslationController extends Controller
{
    public function getTranslations($locale)
    {
        $path = resource_path("lang/{$locale}/react.json");

        if (!File::exists($path)) {
            return response()->json(['error' => 'Translations not found'], 404);
        }

        return response()->json(
            json_decode(file_get_contents($path)),
            200,
            ['Cache-Control' => 'public, max-age=3600']
        );
    }
}
