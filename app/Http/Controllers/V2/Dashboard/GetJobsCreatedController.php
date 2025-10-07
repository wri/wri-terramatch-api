<?php

namespace App\Http\Controllers\V2\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Resources\DelayedJobResource;
use App\Jobs\Dashboard\RunJobsCreatedJob;
use App\Models\DelayedJob;
use App\Models\Traits\HasCacheParameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class GetJobsCreatedController extends Controller
{
    use HasCacheParameter;

    public function __invoke(Request $request)
    {
        try {
            $cacheParameter = $this->getParametersFromRequest($request);
            $cacheValue = Redis::get('dashboard:jobs-created|'.$cacheParameter);

            if (! $cacheValue) {
                $frameworks = data_get($request, 'filter.programmes', []);
                $landscapes = data_get($request, 'filter.landscapes', []);
                $organisations = data_get($request, 'filter.organisationType', []);
                $country = data_get($request, 'filter.country', '');
                $cohort = data_get($request, 'filter.cohort', '');
                if (is_array($cohort)) {
                    $cohort = implode(',', $cohort);
                }
                $uuid = data_get($request, 'filter.projectUuid', '');

                $delayedJob = DelayedJob::create();
                $job = new RunJobsCreatedJob(
                    $delayedJob->id,
                    $frameworks,
                    $landscapes,
                    $organisations,
                    $country,
                    $cohort,
                    $uuid,
                    $cacheParameter
                );
                dispatch($job);

                return (new DelayedJobResource($delayedJob))->additional(['message' => 'Jobs created data is being processed']);
            } else {
                return response()->json(json_decode($cacheValue));
            }
        } catch (\Exception $e) {
            Log::error('Error during jobs-created calculation: ' . $e->getMessage());

            return response()->json(['error' => 'An error occurred during jobs-created calculation'], 500);
        }
    }
}
