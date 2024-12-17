<?php

namespace App\Http\Controllers\V2\MonitoredData;

use App\Http\Controllers\Controller;
use App\Http\Resources\DelayedJobResource;
use App\Jobs\RunIndicatorAnalysisJob;
use App\Models\DelayedJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RunIndicatorAnalysisController extends Controller
{
    public function __invoke(Request $request, string $slug)
    {
        try {
            $requestData = $request->all();
            $delayedJob = DelayedJob::create();
            $job = new RunIndicatorAnalysisJob(
                $delayedJob->id,
                $requestData,
                $slug
            );
            dispatch($job);

            return (new DelayedJobResource($delayedJob))->additional(['message' => 'Analysis for '.$slug.' is being processed']);
        } catch (\Exception $e) {
            Log::error('Error during analysis for ' . $slug . ' : ' . $e->getMessage());

            return response()->json(['error' => 'An error occurred during analysis for ' . $slug], 500);
        }
    }
}
