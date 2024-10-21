<?php

namespace App\Http\Controllers;

use App\Models\Experiment;
use App\Services\ExperimentExportHandler;
use Illuminate\Http\Request;

class ExperimentController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function download($experimentId, Request $request)
    {
        $experiment = Experiment::findOrFail($experimentId);
        $handler = new ExperimentExportHandler($experiment);

        return $handler->downloadExperiment($experimentId, $request);
    }
}
