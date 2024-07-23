<?php

namespace App\Http\Controllers\V2\Sites;

use App\Http\Controllers\Controller;
use App\Models\V2\Sites\SitePolygon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class IndexSitePolygonVersionsController extends Controller
{
    public function __invoke(string $uuid): JsonResponse
    {
        Log::info('Fetching site polygons', ['uuid' => $uuid]);

        try {
            $sitePolygons = SitePolygon::where('primary_uuid', $uuid)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json($sitePolygons);
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return response()->json(['error' => 'An error occurred while fetching site polygons'], 500);
        }
    }
}
