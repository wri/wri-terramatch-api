<?php

namespace App\Http\Controllers\V2\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\DelayedJobResource;
use App\Jobs\RunIndexMyActionsJob;
use App\Models\DelayedJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IndexMyActionsController extends Controller
{
    public function __invoke(Request $request)
    {
        try {
            $user = Auth::user();

            $delayedJob = DelayedJob::create();
            $job = new RunIndexMyActionsJob(
                $delayedJob->id,
                $user
            );
            dispatch($job);

            return (new DelayedJobResource($delayedJob))->additional(['message' => 'My actions are being processed']);
        } catch (\Exception $e) {
            Log::error('Error during my actions : ' . $e->getMessage());

            return response()->json(['error' => 'An error occurred during my actions'], 500);
        }
    }
}
