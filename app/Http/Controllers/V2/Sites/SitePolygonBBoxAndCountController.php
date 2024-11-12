<?php

namespace App\Http\Controllers\V2\Sites;

use App\Helpers\GeometryHelper;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\V2\Sites\Site;
use Exception;

class SitePolygonBBoxAndCountController extends Controller
{
    public function __invoke(Request $request, $uuid)
    {
        try {
            $site = Site::where('uuid', $uuid)->firstOrFail();
            $sitePolygons = $this->getSitePolygonsWithFilters($site->sitePolygons()->active(), $request);
            $polygonsUuids = $sitePolygons->pluck('poly_id');
            $bboxCoordinates = GeometryHelper::getPolygonsBbox($polygonsUuids);

            return response()->json([
                'count' => count($sitePolygons),
                'bbox' => $bboxCoordinates,
            ]);
        } catch (Exception $e) {
            Log::error($e);

            return response()->json([
                'error' => 'An error occurred while retrieving the site.',
            ], 500);
        }
    }

   private function getSitePolygonsWithFilters($sitePolygonsQuery, Request $request)
    {
        if ($request->has('status') && $request->input('status')) {
            $statusValues = explode(',', $request->input('status'));
            $sitePolygonsQuery->whereIn('site_polygon.status', $statusValues);
        }
        return $sitePolygonsQuery->select('site_polygon.poly_id', 'site_polygon.status')->get();
    }
}
