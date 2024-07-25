<?php

namespace App\Http\Controllers\V2\Sites;

use App\Http\Controllers\Controller;
use App\Models\V2\Sites\SitePolygon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ShowSitePolygonController extends Controller
{
    public function __invoke(string $uuid): JsonResponse
    {
        Log::info('Fetching site polygons', ['uuid' => $uuid]);

        try {
            $sitePolygon = SitePolygon::where('uuid', $uuid)->first();

            return response()->json($sitePolygon);
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return response()->json(['error' => 'An error occurred while fetching site polygons'], 500);
        }
    }
}
