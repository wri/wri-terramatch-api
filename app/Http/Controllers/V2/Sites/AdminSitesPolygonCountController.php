<?php

namespace App\Http\Controllers\V2\Sites;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Models\V2\Sites\SitePolygon;

class AdminSitesPolygonCountController extends Controller
{
    public function __invoke($siteUuid): JsonResponse
    {
        try {
            $countSitePolygons = SitePolygon::active()->where('site_id', $siteUuid)->count();

            return response()->json([
                'count' => $countSitePolygons
            ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return response()->json(['error' => 'An error occurred while fetching site polygons'], 500);
        }
    }
}
