<?php

namespace App\Helpers;

use App\Constants\PolygonFields;
use App\Models\V2\PolygonGeometry;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectPolygon;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SitePolygon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GeometryHelper
{
    public function centroidOfProject($projectUuid)
    {
        $project = Project::where('uuid', $projectUuid)->first();

        if (! $project) {
            return null;
        }
        $polyIds = $project->sitePolygons()->pluck('poly_id')->toArray();

        if (empty($polyIds)) {
            return null;
        }

        $centroids = PolygonGeometry::selectRaw('ST_AsGeoJSON(ST_Centroid(geom)) AS centroid')
          ->whereIn('uuid', $polyIds)
          ->get();

        if ($centroids->isEmpty()) {
            return null; // Return null if no centroids are found
        }

        $centroidCount = $centroids->count();
        $totalLatitude = 0;
        $totalLongitude = 0;

        foreach ($centroids as $centroid) {
            $centroidData = json_decode($centroid->centroid, true);
            $totalLatitude += $centroidData['coordinates'][1];
            $totalLongitude += $centroidData['coordinates'][0];
        }

        $averageLatitude = $totalLatitude / $centroidCount;
        $averageLongitude = $totalLongitude / $centroidCount;

        $centroidOfCentroids = json_encode([
          'type' => 'Point',
          'coordinates' => [$averageLongitude, $averageLatitude],
        ]);

        return $centroidOfCentroids;
    }

    public function updateProjectCentroid(string $projectUuid)
    {
        try {
            $centroid = $this->centroidOfProject($projectUuid);
            if ($centroid === null) {
                Log::warning("Invalid centroid for projectUuid: $projectUuid");
            }

            $centroidArray = json_decode($centroid, true);

            $latitude = $centroidArray['coordinates'][1];
            $longitude = $centroidArray['coordinates'][0];


            Project::where('uuid', $projectUuid)
                ->update([
                    'lat' => $latitude,
                    'long' => $longitude,
                ]);

            return response()->json([
              'message' => 'Centroid updated',
              'centroid' => $centroid,
            ], 200);
        } catch (Exception $e) {
            $message = $e->getMessage();
            Log::error("Error updating centroid for projectUuid: $projectUuid with error  $message");

            return response()->json([
              'message' => 'Error updating centroid',
              'error' => $e->getMessage(),
            ], 500);
        }

    }

    public function getPolygonCentroid(string $polyUuid)
    {
        $sitePolygon = SitePolygon::where('poly_id', $polyUuid)->first();

        if (! $sitePolygon) {
            return null;
        }

        $centroid = DB::select('SELECT ST_AsGeoJSON(ST_Centroid(geometry)) as centroid FROM site_polygons WHERE poly_id = ?', [$polyUuid])[0]->centroid;

        return json_decode($centroid);
    }

    public static function getCriteriaDataForPolygonGeometry($polygonGeometry)
    {
        return $polygonGeometry
            ->criteriaSite()
            ->get([
                'criteria_id',
                'valid',
                'created_at as latest_created_at',
                'extra_info',
            ]);
    }

    public static function groupFeaturesBySiteId($geojson)
    {
        if (! isset($geojson['features']) || ! is_array($geojson['features'])) {
            return ['error' => 'Invalid GeoJSON structure'];
        }
        $groupedFeatures = [];
        $noSiteKey = 'no_site';
        foreach ($geojson['features'] as $feature) {

            if (isset($feature['properties']['site_id']) && Str::isUuid($feature['properties']['site_id'])) {
                $siteId = $feature['properties']['site_id'];
                if (! isset($groupedFeatures[$siteId])) {
                    $groupedFeatures[$siteId] = [
                        'type' => 'FeatureCollection',
                        'features' => [],
                    ];
                }
                $groupedFeatures[$siteId]['features'][] = $feature;
            } else {
                if (! isset($groupedFeatures[$noSiteKey])) {
                    $groupedFeatures[$noSiteKey] = [
                        'type' => 'FeatureCollection',
                        'features' => [],
                    ];
                }
                $groupedFeatures[$noSiteKey]['features'][] = $feature;
            }
        }

        return $groupedFeatures;
    }

    public static function groupFeaturesByProjectAndSite($geojson)
    {
        $groupedGeoJson = self::groupFeaturesBySiteId($geojson);
        $projectGroupedFeatures = [];
        $noProjectKey = 'no_project';

        foreach ($groupedGeoJson as $siteId => $featureCollection) {
            if ($siteId === 'no_site') {
                if (! isset($projectGroupedFeatures[$noProjectKey])) {
                    $projectGroupedFeatures[$noProjectKey] = [];
                }
                $projectGroupedFeatures[$noProjectKey][$siteId] = $featureCollection;

                continue;
            }

            $sitePolygon = Site::isUuid($siteId)->first();
            if ($sitePolygon === null || $sitePolygon->project === null) {
                Log::error('Site polygon or project not found for siteId: '.$siteId);
                if (! isset($projectGroupedFeatures[$noProjectKey])) {
                    $projectGroupedFeatures[$noProjectKey] = [];
                }
                $projectGroupedFeatures[$noProjectKey][$siteId] = $featureCollection;

                continue;
            }

            $projectUuid = $sitePolygon->project->uuid;
            if (! isset($projectGroupedFeatures[$projectUuid])) {
                $projectGroupedFeatures[$projectUuid] = [];
            }

            $projectGroupedFeatures[$projectUuid][$siteId] = $featureCollection;
        }

        return $projectGroupedFeatures;
    }

    public static function splitMultiPolygons($featureCollection)
    {
        $features = $featureCollection['features'];
        $resultFeatures = [];

        foreach ($features as $feature) {
            $geometry = $feature['geometry'];
            $properties = $feature['properties'];

            if ($geometry['type'] === 'Polygon') {
                $resultFeatures[] = [
                    'type' => 'Feature',
                    'geometry' => $geometry,
                    'properties' => $properties,
                ];
            } elseif ($geometry['type'] === 'MultiPolygon') {
                $coordinates = $geometry['coordinates'];

                foreach ($coordinates as $index => $polygon) {
                    $newProperties = $properties;
                    $newProperties['poly_name'] = ($properties['poly_name'] ?? 'Unnamed Polygon') . '-polygon ' . ($index + 1);

                    $resultFeatures[] = [
                        'type' => 'Feature',
                        'geometry' => [
                            'type' => 'Polygon',
                            'coordinates' => $polygon,
                        ],
                        'properties' => $newProperties,
                    ];
                }
            }
        }

        return [
            'type' => 'FeatureCollection',
            'features' => $resultFeatures,
        ];
    }

    public static function isOneOrTwoPointFeatures($geojson)
    {
        $data = json_decode($geojson, true);
        if (! isset($data['features']) || ! is_array($data['features'])) {
            return false;
        }
        $totalFeatures = count($data['features']);
        $pointCount = 0;

        foreach ($data['features'] as $feature) {
            if (isset($feature['geometry']) && $feature['geometry']['type'] === 'Point') {
                $pointCount++;
            }
        }

        return ($totalFeatures === 1 || $totalFeatures === 2) && $pointCount === $totalFeatures;
    }

    public static function addEstAreaToPointFeatures($geojson)
    {
        $EST_AREA = 78;
        $data = json_decode($geojson, true);
        if (! isset($data['features']) || ! is_array($data['features'])) {
            return false;
        }
        foreach ($data['features'] as &$feature) {
            if (isset($feature['geometry']) && $feature['geometry']['type'] === 'Point') {
                if (! isset($feature['properties'])) {
                    $feature['properties'] = [];
                }
                $feature['properties']['est_area'] = $EST_AREA;
            }
        }

        return json_encode($data);
    }

    public static function isFeatureCollectionEmpty($geojson)
    {
        $data = json_decode($geojson, true);
        if (isset($data['features']) && is_array($data['features'])) {
            return empty($data['features']);
        }

        return false;
    }

    public static function getConvexHull($geoJson)
    {
        $geoJsonString = is_array($geoJson) ? json_encode($geoJson) : $geoJson;
        $query = 'SELECT ST_AsText(ST_CONVEXHULL(ST_GeomFromGeoJSON(:geojson))) as wkt';
        $result = DB::select($query, ['geojson' => $geoJsonString]);

        return $result[0]->wkt ?? null;
    }

    public static function deletePolygonWithRelated($entity)
    {
        try {
            $entityType = get_class($entity);
            $entityId = $entity->id;

            $projectPolygons = ProjectPolygon::where('entity_id', $entityId)
                                             ->where('entity_type', $entityType)
                                             ->get();

            if ($projectPolygons->isEmpty()) {
                return true;
            }

            foreach ($projectPolygons as $projectPolygon) {
                $polygonGeometry = PolygonGeometry::isUuid($projectPolygon->poly_uuid)->first();
                if ($polygonGeometry) {
                    $polygonGeometry->deleteWithRelated();
                }
            }

            return true;

        } catch (Exception $e) {
            Log::error('An error occurred while deleting related entities: ' . $e->getMessage());

            throw $e;
        }
    }

    public static function getPolygonsGeojson(array $polygonUuids): array
    {
        $features = PolygonGeometry::whereIn('uuid', $polygonUuids)
            ->select('uuid', DB::raw('ST_AsGeoJSON(geom) AS geojsonGeometry'))
            ->get()
            ->map(function ($polygon) {
                return [
                    'type' => 'Feature',
                    'properties' => [
                        'poly_id' => $polygon->uuid,
                    ],
                    'geometry' => json_decode($polygon->geojsonGeometry, true),
                ];
            })
            ->toArray();

        return [
            'type' => 'FeatureCollection',
            'features' => $features,
        ];
    }

    public static function getProjectPolygonsUuids($projectId)
    {
        $project = Project::where('id', $projectId)->firstOrFail();
        $projectPolygonUuids = $project->sitePolygons()->pluck('poly_id')->toArray();

        return $projectPolygonUuids;
    }

    public static function getSitePolygonsUuids($uuid)
    {
        return SitePolygon::where('site_id', $uuid)->where('is_active', true)->get()->pluck('poly_id');
    }

    public static function getSitePolygonsOfPolygons(array $polygonUuids)
    {
        return SitePolygon::whereIn('poly_id', $polygonUuids)->where('is_active', true)->get()->pluck('uuid');
    }

    public static function getMonitoredPolygonsGeojson($polygonUuid)
    {
        $polygonGeometry = PolygonGeometry::where('uuid', $polygonUuid)
            ->select('uuid', DB::raw('ST_AsGeoJSON(geom) AS geojsonGeometry'))
            ->first();

        return [
            'geometry' => $polygonGeometry,
            'site_polygon_id' => $polygonGeometry->sitePolygon->id,
        ];
    }

    public static function getPolygonGeojson($uuid)
    {
        $polygonGeometry = PolygonGeometry::where('uuid', $uuid)
            ->select('uuid', DB::raw('ST_AsGeoJSON(geom) AS geojsonGeometry'))
            ->first();
        $geometry = json_decode($polygonGeometry->geojsonGeometry, true);
        $polygonData = $polygonGeometry->sitePolygon;

        return [
            'type' => 'Feature',
            'properties' => [
                'poly_id' => $polygonData->poly_id,
                'poly_name' => $polygonData->poly_name ?? '',
                'plantstart' => $polygonData->plantstart ?? '',
                'practice' => $polygonData->practice ?? '',
                'target_sys' => $polygonData->target_sys ?? '',
                'distr' => $polygonData->distr ?? '',
                'num_trees' => $polygonData->num_trees ?? '',
                'site_id' => $polygonData->site_id ?? '',
                'planting_status' => $polygonData->planting_status ?? '',
            ],
            'geometry' => $geometry,
        ];
    }

    public static function generateGeoJSON($project = null, $siteUuid = null)
    {
        $query = SitePolygon::query();

        if ($project) {
            $query->whereHas('site', function ($query) use ($project) {
                $query->where('project_id', $project->id);
            });
        }

        if ($siteUuid) {
            $query->where('site_id', $siteUuid);
        }

        $sitePolygons = $query->active()->get();

        $features = [];
        foreach ($sitePolygons as $sitePolygon) {
            $polygonGeometry = PolygonGeometry::where('uuid', $sitePolygon->poly_id)
                ->select(DB::raw('ST_AsGeoJSON(geom) AS geojsonGeom'))
                ->first();

            if (! $polygonGeometry) {
                throw new \Exception('No polygon geometry found for the given UUID.');
            }

            $fieldsToValidate = PolygonFields::EXTENDED_FIELDS;
            $sitePolygonExtraAttributes = $sitePolygon->sitePolygonData;
            $properties = [];
            foreach ($fieldsToValidate as $field) {
                $properties[$field] = $sitePolygon->$field;
            }

            if ($sitePolygonExtraAttributes !== null) {
                $extraData = $sitePolygonExtraAttributes->data;

                if (is_string($extraData)) {
                    $decoded = json_decode($extraData, true);
                    if (is_array($decoded)) {
                        $properties = array_merge($properties, $decoded);
                    }
                } elseif (is_array($extraData)) {
                    $properties = array_merge($properties, $extraData);
                }
            } else {
                Log::info("No related sitePolygonData found for sitePolygon with UUID: {$sitePolygon->uuid}");
            }

            $features[] = [
                'type' => 'Feature',
                'geometry' => json_decode($polygonGeometry->geojsonGeom),
                'properties' => $properties,
            ];
        }

        return [
            'type' => 'FeatureCollection',
            'features' => $features,
        ];
    }

    public static function centroidOfPolygon($polyUUID)
    {
        $centroid = PolygonGeometry::selectRaw('ST_X(ST_Centroid(geom)) AS `long`, ST_Y(ST_Centroid(geom)) AS lat')
        ->where('uuid', $polyUUID)
        ->first();

        if (! $centroid) {
            return [];
        }

        return [$centroid->long, $centroid->lat];
    }

    public static function getCentroidsOfPolygons(array $polygonUuids)
    {
        return PolygonGeometry::whereIn('uuid', $polygonUuids)
            ->select([
                'uuid',
                DB::raw('ST_X(ST_Centroid(geom)) AS centroid_x'),
                DB::raw('ST_Y(ST_Centroid(geom)) AS centroid_y'),
            ])
            ->get()
            ->map(function ($polygon) {
                return [
                    'uuid' => $polygon->uuid,
                    'long' => $polygon->centroid_x,
                    'lat' => $polygon->centroid_y,
                ];
            });
    }

    public static function processDeletionBatch(array $uuidBatch, array &$deletedUuids, array &$failedUuids, array &$affectedProjects)
    {
        $polygonGeometries = PolygonGeometry::whereIn('uuid', $uuidBatch)
            ->with('sitePolygon.project')
            ->get();

        $polygonMap = $polygonGeometries->keyBy('uuid');

        $primaryUuids = [];
        foreach ($polygonGeometries as $polygon) {
            if ($polygon->sitePolygon) {
                $primaryUuids[] = $polygon->sitePolygon->primary_uuid;

                // Track affected projects
                if ($polygon->sitePolygon->project) {
                    $affectedProjects[] = $polygon->sitePolygon->project->uuid;
                }
            }
        }

        $allRelatedSitePolygons = SitePolygon::whereIn('primary_uuid', array_unique($primaryUuids))
            ->with('polygonGeometry')
            ->get();

        $groupedSitePolygons = $allRelatedSitePolygons->groupBy('primary_uuid');

        foreach ($uuidBatch as $uuid) {
            try {
                if (! isset($polygonMap[$uuid])) {
                    $failedUuids[] = ['uuid' => $uuid, 'error' => 'Polygon geometry not found'];

                    continue;
                }

                $polygonGeometry = $polygonMap[$uuid];
                $sitePolygon = $polygonGeometry->sitePolygon;

                if (! $sitePolygon) {
                    $failedUuids[] = ['uuid' => $uuid, 'error' => 'Site polygon not found'];

                    continue;
                }

                $primaryUuid = $sitePolygon->primary_uuid;

                DB::beginTransaction();

                try {
                    if ($sitePolygon->is_active) {
                        $sitePolygon->is_active = false;
                        $sitePolygon->save();
                    }

                    if (isset($groupedSitePolygons[$primaryUuid])) {
                        foreach ($groupedSitePolygons[$primaryUuid] as $relatedSitePolygon) {
                            if ($relatedSitePolygon->polygonGeometry) {
                                $relatedSitePolygon->polygonGeometry->deleteWithRelated();
                            }
                        }
                    }

                    DB::commit();
                    $deletedUuids[] = ['uuid' => $uuid];

                    Log::info("All related polygons and site polygons deleted successfully for primary UUID: $primaryUuid");
                } catch (\Exception $e) {
                    DB::rollBack();

                    throw $e;
                }
            } catch (\Exception $e) {
                Log::error('An error occurred while deleting polygon and site polygon for UUID: ' . $uuid . '. Error: ' . $e->getMessage());
                $failedUuids[] = ['uuid' => $uuid, 'error' => $e->getMessage()];
            }
        }
    }

    public static function updateSitePolygonCentroid(SitePolygon $sitePolygon): bool
    {
        if (! $sitePolygon->poly_id) {
            return false;
        }

        $centroid = PolygonGeometry::selectRaw('ST_X(ST_Centroid(geom)) AS `long`, ST_Y(ST_Centroid(geom)) AS lat')
            ->where('uuid', $sitePolygon->poly_id)
            ->first();

        if (! $centroid) {
            return false;
        }

        DB::table('site_polygon')
            ->where('id', $sitePolygon->id)
            ->update([
                'lat' => $centroid->lat,
                'long' => $centroid->long,
            ]);

        // Update the model instance in memory so it has the latest values
        $sitePolygon->lat = $centroid->lat;
        $sitePolygon->long = $centroid->long;

        return true;
    }
}
