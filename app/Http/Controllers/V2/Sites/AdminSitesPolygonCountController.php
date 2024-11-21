<?php

namespace App\Http\Controllers\V2\Sites;

use App\Http\Controllers\Controller;
use App\Models\V2\Projects\Project;
use App\Models\V2\Sites\SitePolygon;
use App\Services\PolygonService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class AdminSitesPolygonCountController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $uuid = $request->input('uuid');
            $type = $request->input('type');
            $request = request();
            if ($type === 'projects') {
                $project = Project::where('uuid', $uuid)->firstOrFail();
                $countSitePolygons = App::make(PolygonService::class)->getSitePolygonsWithFiltersAndSorts($project->sitePolygons(), $request);
            } elseif ($type === 'sites') {
                $sitePolygonsQuery = SitePolygon::active()->where('site_id', $uuid);
                $countSitePolygons = App::make(PolygonService::class)->getSitePolygonsWithFiltersAndSorts($sitePolygonsQuery, $request);
            }

            $totalCount = $countSitePolygons->count();

            return response()->json([
                'count' => $totalCount,
            ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return response()->json(['error' => 'An error occurred while fetching site polygons'], 500);
        }
    }
}
