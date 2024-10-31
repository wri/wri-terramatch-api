<?php

namespace App\Http\Controllers\V2\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Resources\DelayedJobResource;
use App\Jobs\Dashboard\RunTotalHeaderJob;
use App\Models\DelayedJob;
use App\Models\Traits\HasCacheParameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class TotalTerrafundHeaderDashboardController extends Controller
{
    use HasCacheParameter;

    public function __invoke(Request $request)
    {
        try {
            $cacheParameter = $this->getParametersFromRequest($request);
            $cacheValue = Redis::get('total-section-header-'.$cacheParameter);

            if (! $cacheValue) {
                $frameworks = data_get($request, 'filter.programmes', []);
                $landscapes = data_get($request, 'filter.landscapes', []);
                $organisations = data_get($request, 'filter.organisations.type', []);
                $country = data_get($request, 'filter.country', '');

                $delayedJob = DelayedJob::create();
                $job = new RunTotalHeaderJob(
                    $delayedJob->id,
                    $frameworks,
                    $landscapes,
                    $organisations,
                    $country,
                    $cacheParameter
                );
                dispatch($job);

                return (new DelayedJobResource($delayedJob))->additional(['message' => 'Validation completed for all site polygons']);
            } else {
                return response()->json(json_decode($cacheValue));
            }
        } catch (\Exception $e) {
            Log::error('Error during total-header : ' . $e->getMessage());

            return response()->json(['error' => 'An error occurred during total-header'], 500);
        }
    }
}
