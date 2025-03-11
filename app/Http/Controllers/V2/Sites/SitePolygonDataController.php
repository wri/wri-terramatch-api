<?php

namespace App\Http\Controllers\V2\Sites;

use App\Helpers\GeometryHelper;
use App\Http\Controllers\Controller;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SitePolygon;
use App\Services\PolygonService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class SitePolygonDataController extends Controller
{
    public function getSitePolygonData($site): JsonResponse
    {
        try {
            $sitePolygons = SitePolygon::active()->where('site_id', $site)->get();

            return response()->json($sitePolygons);
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return response()->json(['error' => 'An error occurred while fetching site polygons'], 500);
        }
    }

    public function getBboxOfCompleteSite($site): JsonResponse
    {
        try {
            $sitePolygons = SitePolygon::where('site_id', $site)->get();
            if ($sitePolygons->isEmpty()) {
                $site = Site::isUuid($site)->first();
                $project = $site->project;

                if ($project && $project->country) {
                    $countryBbox = App::make(PolygonService::class)->getCountryBbox($project->country);

                    if ($countryBbox) {
                        return response()->json(['bbox' => $countryBbox[1]]);
                    }

                    return response()->json(['error' => 'Country bounding box could not be retrieved'], 404);
                }

                return response()->json(['error' => 'No polygons found for the site and no country data available'], 404);
            }

            $polygonsIds = $sitePolygons->pluck('poly_id');
            $bboxCoordinates = GeometryHelper::getPolygonsBbox($polygonsIds);

            return response()->json(['bbox' => $bboxCoordinates]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return response()->json(['error' => 'An error occurred while fetching the bounding box coordinates'], 500);
        }
    }
}
