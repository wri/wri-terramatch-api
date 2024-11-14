<?php

namespace App\Http\Controllers\V2\Sites;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Models\V2\Sites\SitePolygon;

class AdminSitesPolygonController extends Controller
{
    public function __invoke($siteUuid): JsonResponse
    {
        try {
            $offset = request()->get('offset', 0);
            $limit = request()->get('limit', 10);

            $sitePolygons = SitePolygon::active()->where('site_id', $siteUuid)
            ->offset($offset)
            ->limit($limit)
            ->get();

            return response()->json($sitePolygons);
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return response()->json(['error' => 'An error occurred while fetching site polygons'], 500);
        }
    }
}
