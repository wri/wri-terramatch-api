<?php

namespace App\Http\Controllers\Terrafund;

use App\Helpers\JsonResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\V2\BaselineMonitoring\SiteMetricResource;
use App\Models\Terrafund\TerrafundProgramme;
use App\Resources\Terrafund\TerrafundSiteResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TerrafundProgrammeSitesController extends Controller
{
    public function readAllProgrammeSites(Request $request, TerrafundProgramme $terrafundProgramme): JsonResponse
    {
        $this->authorize('read', $terrafundProgramme);

        $sites = $terrafundProgramme->terrafundSites()->paginate(5);
        $resources = [];
        foreach ($sites as $site) {
            $resources[] = new TerrafundSiteResource($site);
        }

        $meta = (object)[
            'first' => $sites->firstItem(),
            'current' => $sites->currentPage(),
            'last' => $sites->lastPage(),
            'total' => $sites->total(),
        ];

        return JsonResponseHelper::success($resources, 200, $meta);
    }

    public function readAllNonPaginatedProgrammeSites(Request $request, TerrafundProgramme $terrafundProgramme): JsonResponse
    {
        $this->authorize('read', $terrafundProgramme);

        $sites = $terrafundProgramme->terrafundSites;
        $resources = [];
        foreach ($sites as $site) {
            $resources[] = new TerrafundSiteResource($site);
        }

        return JsonResponseHelper::success($resources, 200);
    }

    public function readAllProgrammeSiteMetrics(Request $request, TerrafundProgramme $terrafundProgramme): JsonResponse
    {
        $this->authorize('read', $terrafundProgramme);
        $resources = [];
        foreach ($terrafundProgramme->terrafundSites as $site) {
            $metric = $site->baselineMonitoring()->first();
            if (! empty($metric)) {
                $resources[] = new SiteMetricResource($metric);
            }
        }

        return JsonResponseHelper::success($resources, 200);
    }

    public function checkHasProgrammeSites(Request $request, TerrafundProgramme $terrafundProgramme): JsonResponse
    {
        $this->authorize('read', $terrafundProgramme);

        $sitesCount = $terrafundProgramme->terrafundSites()->count();

        $response = [
            'programme_id' => $terrafundProgramme->id,
            'has_sites' => $sitesCount > 0,
        ];

        return JsonResponseHelper::success($response, 200);
    }
}
