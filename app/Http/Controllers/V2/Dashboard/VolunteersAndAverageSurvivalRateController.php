<?php

namespace App\Http\Controllers\V2\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Resources\DelayedJobResource;
use App\Jobs\Dashboard\RunVolunteersAverageJob;
use App\Models\DelayedJob;
use App\Models\Traits\HasCacheParameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class VolunteersAndAverageSurvivalRateController extends Controller
{
    use HasCacheParameter;

    public function __invoke(Request $request)
    {
        try {
            $cacheParameter = $this->getParametersFromRequest($request);
            $cacheValue = Redis::get('dashboard:volunteers-survival-rate|'.$cacheParameter);

            if (! $cacheValue) {
                $frameworks = data_get($request, 'filter.programmes', []);
                $landscapes = data_get($request, 'filter.landscapes', []);
                $organisations = data_get($request, 'filter.organisationType', []);
                $country = data_get($request, 'filter.country', '');
                $uuid = data_get($request, 'filter.projectUuid', '');

                $delayedJob = DelayedJob::create();
                $job = new RunVolunteersAverageJob(
                    $delayedJob->id,
                    $frameworks,
                    $landscapes,
                    $organisations,
                    $country,
                    $uuid,
                    $cacheParameter
                );
                dispatch($job);

                return (new DelayedJobResource($delayedJob))->additional(['message' => 'Data for volunteers survival rate is being processed']);
            } else {
                return response()->json(json_decode($cacheValue));
            }
        } catch (\Exception $e) {
            Log::error('Error during volunteers-survival-rate : ' . $e->getMessage());

            return response()->json(['error' => 'An error occurred during volunteers-survival-rate'. $e->getMessage()], 500);
        }
    }
}
