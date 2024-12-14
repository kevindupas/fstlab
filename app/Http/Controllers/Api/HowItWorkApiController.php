<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Experiment;
use App\Models\User;
use Illuminate\Http\Request;

class HowItWorkApiController extends Controller
{
    public function getTestExperiments()
    {
        $experiments = Experiment::query()
            ->with('creator')
            ->whereHas('creator', function ($query) {
                $query->role('supervisor');
            })
            ->where('status', 'test')
            ->where('howitwork_page', true)
            ->get()
            ->map(function ($experiment) {
                return [
                    'id' => $experiment->id,
                    'name' => $experiment->name,
                    'description' => $experiment->description,
                    'creator_name' => $experiment->creator ? $experiment->creator->name : null,
                    'type' => $experiment->type,
                    'link' => url("/experiment/" . $experiment->link),
                ];
            });

        return response()->json($experiments);
    }
}
