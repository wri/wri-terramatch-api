<?php

namespace App\Http\Controllers\V2\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Resources\DelayedJobResource;
use App\Jobs\Dashboard\RunProjectsJob;
use App\Models\DelayedJob;
use App\Models\Traits\HasCacheParameter;
use App\Models\V2\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class GetProjectsController extends Controller
{
    use HasCacheParameter;

    public function __invoke(Request $request)
    {
        try {
            /** @var User $user */
            $user = Auth::user();

            if (is_null($user)) {
                $request = new Request(['filter' => []]);
            } elseif ($user->hasRole('government') && data_get($request, 'filter.projectUuid', '')) {
                $request = new Request(['filter' => []]);
            } else {
                $frameworks = data_get($request, 'filter.programmes', []);
                $landscapes = data_get($request, 'filter.landscapes', []);
                $organisations = data_get($request, 'filter.organisationType', []);
                $country = data_get($request, 'filter.country', '');
                $cohort = data_get($request, 'filter.cohort', '');
                $uuid = data_get($request, 'filter.projectUuid', '');
            }

            $cacheParameter = $this->getParametersFromRequest($request);
            $cacheValue = Redis::get('dashboard:projects|'.$cacheParameter);
            if (! $cacheValue) {
                $frameworks = data_get($request, 'filter.programmes', []);
                $landscapes = data_get($request, 'filter.landscapes', []);
                $organisations = data_get($request, 'filter.organisationType', []);
                $country = data_get($request, 'filter.country', '');
                $cohort = data_get($request, 'filter.cohort', '');
                $uuid = data_get($request, 'filter.projectUuid', '');

                $delayedJob = DelayedJob::create();
                $job = new RunProjectsJob(
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

                return (new DelayedJobResource($delayedJob))->additional(['message' => 'Projects data is being processed']);
            } else {
                return response()->json(json_decode($cacheValue));
            }
        } catch (\Exception $e) {
            Log::error('Error during projects data processing: ' . $e->getMessage());

            return response()->json(['error' => 'An error occurred during projects data processing: '. $e->getMessage()], 500);
        }
    }
}
