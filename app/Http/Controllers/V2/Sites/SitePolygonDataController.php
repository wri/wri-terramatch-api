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
}
