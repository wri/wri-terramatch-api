<?php

namespace App\Http\Controllers\V2\Applications;

use App\Http\Controllers\Controller;
use App\Http\Resources\DelayedJobResource;
use App\Jobs\RunGetApplicationJob;
use App\Models\DelayedJob;
use App\Models\V2\Forms\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminViewApplicationController extends Controller
{
    public function __invoke(Request $request, Application $application)
    {
        try {
            $this->authorize('readAll', Application::class);

            $delayedJob = DelayedJob::create();
            $job = new RunGetApplicationJob(
                $delayedJob->id,
                $application
            );
            dispatch($job);

            return (new DelayedJobResource($delayedJob))->additional(['message' => 'Application is being processed']);
        } catch (\Exception $e) {
            Log::error('Error during application : ' . $e->getMessage());

            return response()->json(['error' => 'An error occurred during application'], 500);
        }
    }
}
