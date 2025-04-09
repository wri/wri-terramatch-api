<?php

namespace App\Http\Controllers\V2\Sites;

use App\Http\Controllers\Controller;
use App\Http\Resources\SitePolygonLightResource;
use App\Models\V2\Projects\Project;
use App\Models\V2\Sites\SitePolygon;
use App\Services\PolygonService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class AdminSitesPolygonController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $uuid = $request->input('uuid');
            $type = $request->input('type');
            $offset = $request->input('offset', 0);
            $limit = $request->input('limit', 10);
            $lightResource = $request->input('lightResource', false);
            $request = request();


            if ($type === 'projects') {
                $project = Project::where('uuid', $uuid)->firstOrFail();
                $finalEntityQuery = App::make(PolygonService::class)->getSitePolygonsWithFiltersAndSorts($project->sitePolygons(), $request);
            } elseif ($type === 'sites') {
                $sitePolygonsQuery = SitePolygon::active()->where('site_id', $uuid);
                $finalEntityQuery = App::make(PolygonService::class)->getSitePolygonsWithFiltersAndSorts($sitePolygonsQuery, $request);
            }
            $sitePolygons = $finalEntityQuery
                ->offset($offset)
                ->limit($limit)
                ->get();

            if ($lightResource) {
                return response()->json(SitePolygonLightResource::collection($sitePolygons));
            }

            return response()->json($sitePolygons);
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return response()->json(['error' => 'An error occurred while fetching site polygons'], 500);
        }
    }
}
