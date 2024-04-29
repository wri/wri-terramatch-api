<?php

namespace App\Http\Controllers\V2\Sites;

use App\Http\Controllers\Controller;
use App\Models\V2\PolygonGeometry;
use App\Models\V2\Sites\SitePolygon;
use Illuminate\Support\Facades\Log;
use App\Helpers\GeometryHelper;

class SitePolygonDataController extends Controller
{
    public function getSitePolygonData($site)
    {
        $sitePolygons = SitePolygon::where('site_id', $site)->get();
        Log::info(json_encode($sitePolygons));

        return $sitePolygons;
    }

    public function getBboxOfCompleteSite($site)
    {
        try {
            $sitePolygons = SitePolygon::where('site_id', $site)->get();
            $polygonsIds = $sitePolygons->pluck('poly_id');

            $bboxCoordinates = GeometryHelper::getPolygonsBbox($polygonsIds);

            return response()->json(['bbox' => $bboxCoordinates]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return response()->json(['error' => 'An error occurred while fetching the bounding box coordinates'], 404);
        }
    }
};
