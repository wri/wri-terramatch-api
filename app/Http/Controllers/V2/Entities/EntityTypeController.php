<?php

namespace App\Http\Controllers\V2\Entities;

use App\Helpers\GeometryHelper;
use App\Http\Controllers\Controller;
use App\Models\V2\Projects\Project;
use App\Models\V2\Sites\Site;
use App\Services\PolygonService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class EntityTypeController extends Controller
{
    public function __invoke(Request $request)
    {
        try {
            $uuid = $request->input('uuid');
            $type = $request->input('type');

            if ($type === 'projects') {
                return $this->handleProjectEntity($uuid, $request);
            } elseif ($type === 'sites') {
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

    private function handleProjectEntity($uuid, Request $request)
    {
        try {
            $project = Project::where('uuid', $uuid)->firstOrFail();
            $sitePolygons = $this->getSitePolygonsWithFiltersAndSorts($project->sitePolygons(), $request);
            $polygonsUuids = $sitePolygons->pluck('poly_id');
            $bboxCoordinates = GeometryHelper::getPolygonsBbox($polygonsUuids);

            return response()->json([
                'type' => 'project',
                'uuid' => $uuid,
                'polygonsData' => $sitePolygons,
                'bbox' => $bboxCoordinates,
            ]);
        } catch (ModelNotFoundException $e) {
            Log::error($e);

            return response()->json([
                'error' => 'The requested project was not found.',
            ], 404);
        } catch (Exception $e) {
            Log::error($e);

            return response()->json([
                'error' => 'An error occurred while retrieving the project.',
            ], 500);
        }
    }

    private function handleSiteEntity($uuid, Request $request)
    {
        try {
            $site = Site::where('uuid', $uuid)->firstOrFail();
            $sitePolygons = $this->getSitePolygonsWithFiltersAndSorts($site->sitePolygons()->active(), $request);
            $polygonsUuids = $sitePolygons->pluck('poly_id');
            
            if ($sitePolygons->isEmpty() && $site) {
              $project = $site->project;

              if ($project && $project->country) {
                  $countryBbox = App::make(PolygonService::class)->getCountryBbox($project->country);
                  if ($countryBbox) {
                      $bboxCoordinates = $countryBbox[1];
                  }
              }
            } else {
              $bboxCoordinates = GeometryHelper::getPolygonsBbox($polygonsUuids);
            }
            return response()->json([
                'type' => 'site',
                'uuid' => $uuid,
                'polygonsData' => $sitePolygons,
                'bbox' => $bboxCoordinates,
            ]);
        } catch (ModelNotFoundException $e) {
            Log::error($e);

            return response()->json([
                'error' => 'The requested site was not found.',
            ], 404);
        } catch (Exception $e) {
            Log::error($e);

            return response()->json([
                'error' => 'An error occurred while retrieving the site.',
            ], 500);
        }
    }

    private function getSitePolygonsWithFiltersAndSorts($sitePolygonsQuery, Request $request)
    {
        if ($request->has('status') && $request->input('status')) {
            $statusValues = explode(',', $request->input('status'));
            $sitePolygonsQuery->whereIn('site_polygon.status', $statusValues);
        }

        $sortFields = $request->input('sort', []);
        foreach ($sortFields as $field => $direction) {
            if ($field === 'status') {
                $sitePolygonsQuery->orderByRaw('FIELD(site_polygon.status, "draft", "submitted", "needs-more-information", "approved") ' . $direction);
            } elseif ($field === 'poly_name') {
                $sitePolygonsQuery->orderByRaw('site_polygon.poly_name IS NULL, site_polygon.poly_name ' . $direction);
            } else {
                $sitePolygonsQuery->orderBy($field, $direction);
            }
        }

        return $sitePolygonsQuery->get();
    }
}
