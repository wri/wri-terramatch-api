<?php

namespace App\Http\Controllers\V2\Dashboard;

use App\Helpers\TerrafundDashboardQueryHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\DelayedJobResource;
use App\Jobs\Dashboard\RunTotalHeaderJob;
use App\Models\DelayedJob;
use App\Models\Traits\HasCacheParameter;
use App\Models\V2\WorldCountryGeneralized;
use App\Services\Dashboard\RunTotalHeaderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class TotalTerrafundHeaderDashboardController extends Controller
{
    use HasCacheParameter;

    public function __invoke(Request $request)
    {
        try {
            $cacheParameter = $this->getParametersFromRequest($request);
            $cacheValue = Redis::get('dashboard:total-section-header|'.$cacheParameter);
            $timestampKey = 'dashboard:total-section-header|' . $cacheParameter . ':timestamp';
            $lastUpdatedAt = Redis::get($timestampKey);

            if (! $cacheValue) {
                $frameworks = data_get($request, 'filter.programmes', []);
                $landscapes = data_get($request, 'filter.landscapes', []);
                $organisations = data_get($request, 'filter.organisationType', []);
                $country = data_get($request, 'filter.country', '');
                $cohort = data_get($request, 'filter.cohort', '');
                $uuid = data_get($request, 'filter.projectUuid', '');

                $delayedJob = DelayedJob::create();
                $job = new RunTotalHeaderJob(
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

                return (new DelayedJobResource($delayedJob))->additional(['message' => 'Data for total-section-header is being processed']);
            } else {
                $data = json_decode($cacheValue);
                $data->last_updated_at = $lastUpdatedAt;

                return response()->json($data);
            }
        } catch (\Exception $e) {
            Log::error('Error during total-header : ' . $e->getMessage());

            return response()->json(['error' => 'An error occurred during total-header'], 500);
        }
    }

    public function getTotalDataForCountry(Request $request)
    {
        $projects = TerrafundDashboardQueryHelper::buildQueryFromRequest($request)->get();
        $countryName = '';
        if ($country = data_get($request, 'filter.country')) {
            $countryName = WorldCountryGeneralized::where('iso', $country)->first()->country;
        }
        $response = (object)[
            'total_non_profit_count' => App::make(RunTotalHeaderService::class)->getTotalNonProfitCount($projects),
            'total_enterprise_count' => App::make(RunTotalHeaderService::class)->getTotalEnterpriseCount($projects),
            'total_entries' => App::make(RunTotalHeaderService::class)->getTotalJobsCreatedSum($projects),
            'total_hectares_restored' => round(App::make(RunTotalHeaderService::class)->getTotalHectaresSum($projects)),
            'total_trees_restored' => App::make(RunTotalHeaderService::class)->getTotalTreesRestoredSum($projects),
            'country_name' => $countryName,
        ];

        return response()->json($response);
    }
}
