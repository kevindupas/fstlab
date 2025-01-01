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
            ->with(['creator', 'links'])
            ->whereHas('creator', function ($query) {
                $query->role('supervisor');
            })
            ->whereHas('links', function ($query) {
                $query->where('status', 'test');
                $query->whereColumn('user_id', 'experiments.created_by'); // Utilisation de whereColumn
            })
            ->where('howitwork_page', true)
            ->get()
            ->map(function ($experiment) {
                $creatorLink = $experiment->links
                    ->where('user_id', $experiment->created_by)
                    ->where('status', 'test')
                    ->first();

                return [
                    'id' => $experiment->id,
                    'name' => $experiment->name,
                    'description' => $experiment->description,
                    'creator_name' => $experiment->creator ? $experiment->creator->name : null,
                    'type' => $experiment->type,
                    'link' => $creatorLink ? url("/experiment/" . $creatorLink->link) : null,
                ];
            });

        return response()->json($experiments);
    }
}
