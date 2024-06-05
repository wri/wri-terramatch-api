<?php

namespace App\Http\Controllers\V2\Entities;

use App\Helpers\GeometryHelper;
use App\Http\Controllers\Controller;
use App\Models\V2\Projects\Project;
use App\Models\V2\Sites\Site;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EntityTypeController extends Controller
{
    public function __invoke(Request $request)
    {
        try {
            $uuid = $request->input('uuid');
            $type = $this->getEntityType($uuid);

            if ($type === 'project') {
                return $this->handleProjectEntity($uuid, $request);
            } elseif ($type === 'site') {
                return $this->handleSiteEntity($uuid, $request);
            }

            return response()->json([
                'type' => 'unknown',
                'uuid' => $uuid,
            ]);
        } catch (Exception $e) {
            Log::error($e);

            return response()->json([
                'error' => 'An error occurred while processing your request.',
            ], 500);
        }
    }

    private function getEntityType($uuid)
    {
        $project = Project::where('uuid', $uuid)->first();
        if ($project) {
            return 'project';
        }

        $site = Site::where('uuid', $uuid)->first();
        if ($site) {
            return 'site';
        }

        return 'unknown';
    }

    private function handleProjectEntity($uuid, Request $request)
    {
        $project = Project::where('uuid', $uuid)->first();
        $sitePolygons = $this->getSitePolygonsWithFiltersAndSorts($project->sitePolygons(), $request);
        $polygonsUuids = $sitePolygons->pluck('poly_id');
        $bboxCoordinates = GeometryHelper::getPolygonsBbox($polygonsUuids);

        return response()->json([
            'type' => 'project',
            'uuid' => $uuid,
            'polygonsData' => $sitePolygons,
            'bbox' => $bboxCoordinates,
        ]);
    }

    private function handleSiteEntity($uuid, Request $request)
    {
        $site = Site::where('uuid', $uuid)->first();
        $sitePolygons = $this->getSitePolygonsWithFiltersAndSorts($site->sitePolygons(), $request);
        $polygonsUuids = $sitePolygons->pluck('poly_id');
        $bboxCoordinates = GeometryHelper::getPolygonsBbox($polygonsUuids);

        return response()->json([
            'type' => 'site',
            'uuid' => $uuid,
            'polygonsData' => $sitePolygons,
            'bbox' => $bboxCoordinates,
        ]);
    }

    private function getSitePolygonsWithFiltersAndSorts($sitePolygonsQuery, Request $request)
    {
        if ($request->has('status')) {
            $statusValues = explode(',', $request->input('status'));
            $sitePolygonsQuery->whereIn('site_polygon.status', $statusValues);
        }

        $sortFields = $request->input('sort', []);
        foreach ($sortFields as $field => $direction) {
            if ($field === 'status') {
                $sitePolygonsQuery->orderByRaw('FIELD(site_polygon.status, "draft", "submitted", "needs-more-information", "approved") ' . $direction);
            } else {
                $sitePolygonsQuery->orderBy($field, $direction);
            }
        }

        return $sitePolygonsQuery->get();
    }
}