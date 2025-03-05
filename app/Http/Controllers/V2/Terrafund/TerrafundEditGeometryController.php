<?php

namespace App\Http\Controllers\V2\Terrafund;

use App\Helpers\GeometryHelper;
use App\Helpers\PolygonGeometryHelper;
use App\Http\Controllers\Controller;
use App\Models\V2\PolygonGeometry;
use App\Models\V2\PolygonUpdates;
use App\Models\V2\Projects\ProjectPolygon;
use App\Models\V2\Sites\SitePolygon;
use App\Models\V2\User;
use App\Services\AreaCalculationService;
use App\Services\PolygonService;
use App\Services\SiteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TerrafundEditGeometryController extends Controller
{
    public function getSitePolygonData(string $uuid)
    {
        try {
            $sitePolygon = SitePolygon::where('poly_id', $uuid)->first();

            if (! $sitePolygon) {
                return response()->json(['message' => 'No site polygons found for the given UUID.'], 404);
            }

            $sitePolygonArray = $sitePolygon->toArray();

            if ($sitePolygon->site) {
                $siteName = $sitePolygon->site->name;
                $sitePolygonArray['site_name'] = $siteName;

                unset($sitePolygonArray['site']);
            }

            return response()->json(['site_polygon' => $sitePolygonArray]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Site polygon not found.'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function getProjectPolygonData(Request $request)
    {
        try {
            $entity_uuid = $request->input('uuid');
            $entity_type = $request->input('entityType');
            $entity = App::make(PolygonService::class)->getEntity($entity_type, $entity_uuid);
            $projectPolygon = ProjectPolygon::where('entity_id', $entity->id)->first();

            if (! $projectPolygon) {
                return response()->json(['message' => 'No project polygons found for the given UUID.', 'project_polygon' => null], 206);
            }

            $projectPolygonArray = $projectPolygon->toArray();

            return response()->json(['project_polygon' => $projectPolygonArray]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Project polygon not found.'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function deletePolygonAndSitePolygon(string $uuid)
    {
        try {
            $polygonGeometry = PolygonGeometry::where('uuid', $uuid)->first();
            if (! $polygonGeometry) {
                return response()->json(['message' => 'No polygon geometry found for the given UUID.'], 404);
            }

            $sitePolygon = $polygonGeometry->sitePolygon()->first();
            if (! $sitePolygon) {
                return response()->json(['message' => 'No site polygon found for the given UUID.'], 404);
            }
            if ($sitePolygon->is_active) {
                $sitePolygon->is_active = false;
                $sitePolygon->save();
            }
            $primaryUuid = $sitePolygon->primary_uuid;

            $allSitePolygons = SitePolygon::where('primary_uuid', $primaryUuid)->get();

            foreach ($allSitePolygons as $sitePolygon) {
                $relatedPolygonGeometry = $sitePolygon->polygonGeometry()->first();

                if ($relatedPolygonGeometry) {
                    $relatedPolygonGeometry->deleteWithRelated();
                }
            }

            $project = $sitePolygon->project;
            if ($project) {
                $geometryHelper = new GeometryHelper();
                $geometryHelper->updateProjectCentroid($project->uuid);
            }

            Log::info("All related polygons and site polygons deleted successfully for primary UUID: $primaryUuid");

            return response()->json(['message' => 'All related polygons and site polygons deleted successfully.', 'uuid' => $primaryUuid]);
        } catch (\Exception $e) {
            Log::error('An error occurred at delete function: ' . $e->getMessage());

            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    public function deleteMultiplePolygonsAndSitePolygons(Request $request)
    {
        try {
            $uuids = $request->input('uuids');

            if (empty($uuids)) {
                return response()->json(['message' => 'No UUIDs provided.'], 400);
            }

            $deletedUuids = [];
            $failedUuids = [];

            foreach ($uuids as $uuid) {
                try {
                    $this->deletePolygonAndSitePolygon($uuid);
                    $deletedUuids[] = ['uuid' => $uuid];
                } catch (\Exception $e) {
                    Log::error('An error occurred while deleting polygon and site polygon for UUID: ' . $uuid . '. Error: ' . $e->getMessage());
                    $failedUuids[] = ['uuid' => $uuid, 'error' => $e->getMessage()];
                }
            }

            $response = [
                'message' => 'Polygon geometries and associated site polygons deleted successfully.',
                'deleted' => $deletedUuids,
                'failed' => $failedUuids,
            ];

            return response()->json($response);
        } catch (\Exception $e) {
            Log::error('An error occurred at delete multiple polygons and sites: ' . $e->getMessage());

            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    public function deletePolygonAndProjectPolygon(string $uuid)
    {
        try {
            $polygonGeometry = PolygonGeometry::where('uuid', $uuid)->first();
            if (! $polygonGeometry) {
                return response()->json(['message' => 'No polygon geometry found for the given UUID.'], 404);
            }

            $polygonGeometry->deleteWithRelated();

            Log::info("Polygon geometry and associated project polygon deleted successfully for UUID: $uuid");

            return response()->json(['message' => 'Polygon geometry and associated project polygon deleted successfully.', 'uuid' => $uuid]);
        } catch (\Exception $e) {
            Log::error('An error occurred: ' . $e->getMessage());

            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    public function updateGeometry(string $uuid, Request $request)
    {
        try {
            Log::info("Updating geometry for polygon with UUID: $uuid");

            $polygonGeometry = PolygonGeometry::where('uuid', $uuid)->first();
            if (! $polygonGeometry) {
                return response()->json(['message' => 'No polygon geometry found for the given UUID.'], 404);
            }
            $geometry = json_decode($request->input('geometry'));
            $geom = DB::raw("ST_GeomFromGeoJSON('" . json_encode($geometry) . "')");

            $sitePolygon = SitePolygon::where('poly_id', $polygonGeometry->uuid)->first();
            $polygonGeometry->geom = $geom;
            $polygonGeometry->save();
            if ($sitePolygon) {
                PolygonGeometryHelper::updateEstAreainSitePolygon($polygonGeometry, $geometry);
                PolygonGeometryHelper::updateProjectCentroidFromPolygon($polygonGeometry);
                $sitePolygon->changeStatusOnEdit();
            }

            return response()->json(['message' => 'Geometry updated successfully.', 'geometry' => $geometry, 'uuid' => $uuid]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred at update geometry: ' . $e->getMessage()], 500);
        }
    }

    public function getPolygonGeojson(string $uuid)
    {
        $geometryQuery = PolygonGeometry::isUuid($uuid);
        if (! $geometryQuery->exists()) {
            return response()->json(['message' => 'No polygon geometry found for the given UUID.'], 404);
        }

        return response()->json([
            'geojson' => $geometryQuery->first()->geojson,
        ]);
    }

    public function updateSitePolygon(string $uuid, Request $request)
    {
        try {
            $sitePolygon = SitePolygon::where('uuid', $uuid)->first();
            if (! $sitePolygon) {
                return response()->json(['message' => 'No site polygons found for the given UUID.'], 404);
            }
            $validatedData = $request->validate([
              'poly_name' => 'nullable|string',
              'plantstart' => 'nullable|date',
              'plantend' => 'nullable|date',
              'practice' => 'nullable|string',
              'distr' => 'nullable|string',
              'num_trees' => 'nullable|integer',
              'calc_area' => 'nullable|numeric',
              'target_sys' => 'nullable|string',
            ]);

            $sitePolygon->update($validatedData);
            $sitePolygon->changeStatusOnEdit();

            return response()->json(['message' => 'Site polygon updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred at update site polygons: ' . $e->getMessage()], 500);
        }
    }

    public function createSitePolygonNewVersion(string $uuid, Request $request)
    {
        try {
            $sitePolygon = SitePolygon::where('uuid', $uuid)->first();
            if (! $sitePolygon) {
                return response()->json(['message' => 'No site polygons found for the given UUID.'], 404);
            }
            $validatedData = $request->validate([
              'poly_name' => 'nullable|string',
              'plantstart' => 'nullable|date',
              'plantend' => 'nullable|date',
              'practice' => 'nullable|string',
              'distr' => 'nullable|string',
              'num_trees' => 'nullable|integer',
              'calc_area' => 'nullable|numeric',
              'target_sys' => 'nullable|string',
            ]);

            $validatedData = App::make(PolygonService::class)->validateSitePolygonProperties($uuid, $validatedData);

            $user = Auth::user();
            $newPolygonVersion = $sitePolygon->createCopy($user, null, false, $validatedData);

            $diff = $this->getDiff($sitePolygon, $newPolygonVersion);
            PolygonUpdates::create([
                'site_polygon_uuid' => $sitePolygon->uuid,
                'version_name' => $newPolygonVersion->version_name,
                'change' => implode(', ', $diff),
                'updated_by_id' => $user->id,
                'comment' => 'Polygon Updated',
                'type' => 'update',
            ]);

            if (! $newPolygonVersion) {
                return response()->json(['error' => 'An error occurred while creating a new version of the site polygon'], 500);
            }
            $newPolygonVersion->changeStatusOnEdit();

            return response()->json(['message' => 'Site polygon version created successfully'], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred at creating version: ' . $e->getMessage()], 500);
        }
    }

    public function createProjectPolygon(string $uuid, string $entity_uuid, string $entity_type)
    {
        try {
            $entity = App::make(PolygonService::class)->getEntity($entity_type, $entity_uuid);
            if (! $entity) {
                return response()->json(['message' => 'No entity found for the given UUID.'], 404);
            }
            $hasBeenDeleted = GeometryHelper::deletePolygonWithRelated($entity);
            if ($hasBeenDeleted) {
                $polygonGeometry = PolygonGeometry::where('uuid', $uuid)->first();
                if (! $polygonGeometry) {
                    return response()->json(['message' => 'No polygon geometry found for the given UUID.'], 404);
                }
                $projectPolygon = new ProjectPolygon([
                    'entity_id' => $entity->id,
                    'entity_type' => get_class($entity),
                    'poly_uuid' => $uuid,
                    'created_by' => Auth::user()?->id,
                    'last_modified_by' => Auth::user()?->id,
                ]);
                if ($projectPolygon->save()) {
                    return response()->json(['message' => 'Project polygon created successfully', 'uuid' => $projectPolygon->uuid], 201);
                } else {
                    return response()->json(['error' => 'An error ocurred at creating'], 500);
                }
            } else {
                throw new \Exception('Error deleting polygon');
            }

        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    public function createSitePolygon(string $uuid, string $siteUuid, Request $request)
    {
        try {
            if ($request->getContent() === '{}') {
                $validatedData = [
                  'poly_name' => null,
                  'plantstart' => null,
                  'plantend' => null,
                  'practice' => null,
                  'distr' => null,
                  'num_trees' => null,
                  'target_sys' => null,
                ];
            } else {
                $validatedData = $request->validate([
                  'poly_name' => 'nullable|string',
                  'plantstart' => 'nullable|date',
                  'plantend' => 'nullable|date',
                  'practice' => 'nullable|string',
                  'distr' => 'nullable|string',
                  'num_trees' => 'nullable|integer',
                  'target_sys' => 'nullable|string',
                ]);
            }

            $polygonGeometry = PolygonGeometry::where('uuid', $uuid)->first();
            if (! $polygonGeometry) {
                return response()->json(['message' => 'No polygon geometry found for the given UUID.'], 404);
            }
            $polygonGeom = PolygonGeometry::where('uuid', $uuid)
            ->select('uuid', DB::raw('ST_AsGeoJSON(geom) AS geojsonGeometry'))
            ->first();
            $geometry = json_decode($polygonGeom->geojsonGeometry, true);
            $areaCalculationService = app(AreaCalculationService::class);
            $areaHectares = $areaCalculationService->getArea($geometry);
            $sitePolygon = new SitePolygon([
                'poly_name' => $validatedData['poly_name'],
                'plantstart' => $validatedData['plantstart'],
                'plantend' => $validatedData['plantend'],
                'practice' => $validatedData['practice'],
                'distr' => $validatedData['distr'],
                'num_trees' => $validatedData['num_trees'],
                'calc_area' => $areaHectares,
                'target_sys' => $validatedData['target_sys'],
                'poly_id' => $uuid,
                'created_by' => Auth::user()?->id,
                'status' => 'draft',
                'source' => PolygonService::TERRAMACH_SOURCE,
                'site_id' => $siteUuid,
            ]);
            $sitePolygon->save();

            $user = User::isUuid(Auth::user()->uuid)->first();
            $sitePolygon->primary_uuid = $sitePolygon->uuid;
            $sitePolygon->poly_name = now()->format('j_F_Y_H_i_s').'_'.$user->full_name;
            $sitePolygon->is_active = true;
            $sitePolygon->save();

            App::make(SiteService::class)->setSiteToRestorationInProgress($siteUuid);
            PolygonGeometryHelper::updateProjectCentroidFromPolygon($polygonGeometry);

            return response()->json(['message' => 'Site polygon created successfully', 'uuid' => $sitePolygon, 'area' => $areaHectares], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred at create site polygon: ' . $e->getMessage()], 500);
        }
    }

    public function getPolygonBbox(string $uuid)
    {
        try {
            $bboxCoordinates = GeometryHelper::getPolygonsBbox([$uuid]);

            return response()->json(['bbox' => $bboxCoordinates]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return response()->json(['error' => 'An error occurred while fetching the bounding box coordinates'], 404);
        }
    }

    private function getDiff(SitePolygon $sitePolygon, SitePolygon $newSitePolygon): array
    {
        $diff = [];
        $keys = ['poly_name','plantstart','plantend','practice','target_sys','distr','num_trees','site_id'];
        foreach ($keys as $key) {
            if ($newSitePolygon[$key] !== $sitePolygon[$key]) {
                $diff[] = "$key => from $sitePolygon[$key] to {$newSitePolygon[$key]}";
            }
        }

        return $diff;
    }
}
