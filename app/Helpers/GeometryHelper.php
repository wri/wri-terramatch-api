<?php

namespace App\Helpers;

use App\Models\V2\PolygonGeometry;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectPolygon;
use App\Models\V2\Sites\CriteriaSite;
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
        } catch (\Exception $e) {
            Log::error("Error updating centroid for projectUuid: $projectUuid");

            return response()->json([
              'message' => 'Error updating centroid',
              'error' => $e->getMessage(),
            ], 500);
        }

    }

    public static function getPolygonsBbox($polygonsIds)
    {
        if (count($polygonsIds) === 0) {
            return null;
        }
        $envelopes = PolygonGeometry::whereIn('uuid', $polygonsIds)
          ->selectRaw('ST_ASGEOJSON(ST_Envelope(geom)) as envelope')
          ->get();
        $maxX = $maxY = PHP_INT_MIN;
        $minX = $minY = PHP_INT_MAX;
        if ($envelopes->isEmpty()) {
            return null;
        }
        foreach ($envelopes as $envelope) {
            $geojson = json_decode($envelope->envelope);
            $coordinates = $geojson->coordinates[0];

            foreach ($coordinates as $point) {
                $x = $point[0];
                $y = $point[1];
                $maxX = max($maxX, $x);
                $minX = min($minX, $x);
                $maxY = max($maxY, $y);
                $minY = min($minY, $y);
            }
        }

        return [$minX, $minY, $maxX, $maxY];
    }

    public static function getCriteriaDataForPolygonGeometry($polygonGeometry)
    {
        // testing if removing the latest query improve the query
        $latestIds = $polygonGeometry->criteriaSite()
        ->groupBy('criteria_id')
        ->pluck('id');
        // ->pluck(DB::raw('max(id) as latest_id'));
        // to delete on DB this might be the query to use
        // which is a never ending query 
        // DELETE cs
        // FROM criteria_site cs
        // JOIN (
        //     SELECT polygon_id, criteria_id, MAX(created_at) AS latest_created
        //     FROM criteria_site
        //     GROUP BY polygon_id, criteria_id
        // ) latest
        // ON cs.polygon_id = latest.polygon_id
        // AND cs.criteria_id = latest.criteria_id
        // AND cs.created_at < latest.latest_created;

        return CriteriaSite::whereIn('id', $latestIds)->get([
            'criteria_id',
            'valid',
            'created_at as latest_created_at',
            'extra_info',
        ]);
        // return $polygonGeometry->latestCriteriaSites()
        // ->select(['criteria_id', 'valid', 'created_at as latest_created_at', 'extra_info'])
        // ->get();
        // QUERY TO see polygons counts by sites:
        //         SELECT 
        //     vs.uuid AS site_uuid,
        //     vs.name AS site_name,
        //     COUNT(sp.poly_id) AS polygon_count
        // FROM 
        //     polygon_geometry pg
        // JOIN 
        //     site_polygon sp ON sp.poly_id = pg.uuid
        // JOIN 
        //     v2_sites vs ON vs.uuid = sp.site_id
        // WHERE 
        //     sp.is_active = 1 AND sp.deleted_at IS NULL
        // GROUP BY 
        //     vs.uuid, vs.name
        // ORDER BY 
        //     polygon_count DESC
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
}
