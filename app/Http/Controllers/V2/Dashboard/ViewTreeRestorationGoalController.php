<?php

namespace App\Http\Controllers\V2\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Resources\DelayedJobResource;
use App\Jobs\Dashboard\RunTreeRestorationGoalJob;
use App\Models\Traits\HasCacheParameter;
use App\Models\DelayedJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class ViewTreeRestorationGoalController extends Controller
{
    use HasCacheParameter;
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $cacheParameter = $this->getParametersFromRequest($request);
            $cacheValue = Redis::get('tree-restoration-goal-'.$cacheParameter);

            if (! $cacheValue) {
                $frameworks = data_get($request, 'filter.programmes', []);
                $landscapes = data_get($request, 'filter.landscapes', []);
                $organisations = data_get($request, 'filter.organisations.type', []);
                $country = data_get($request, 'filter.country', '');

                $delayedJob = DelayedJob::create();
                $job = new RunTreeRestorationGoalJob(
                    $delayedJob->id,
                    $frameworks,
                    $landscapes,
                    $organisations,
                    $country,
                    $cacheParameter
                );
                dispatch($job);

                return (new DelayedJobResource($delayedJob))->additional(['message' => 'Data for total-section-header is being processed']);
            } else {
                return response()->json(json_decode($cacheValue));
            }
        } catch (\Exception $e) {
            Log::error('Error calculating tree restoration goal: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while calculating tree restoration goal'], 500);
        }
    }
}
