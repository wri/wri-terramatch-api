<?php

namespace App\Services;

use App\Models\V2\PointGeometry;
use App\Models\V2\PolygonGeometry;
use App\Models\V2\Sites\CriteriaSite;
use App\Models\V2\Sites\SitePolygon;
use App\Validators\SitePolygonValidator;
use App\Services\PythonService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PolygonService
{
    public const OVERLAPPING_CRITERIA_ID = 3;
    public const SELF_CRITERIA_ID = 4;
    public const COORDINATE_SYSTEM_CRITERIA_ID = 5;
    public const SIZE_CRITERIA_ID = 6;
    public const WITHIN_COUNTRY_CRITERIA_ID = 7;
    public const SPIKE_CRITERIA_ID = 8;
    public const GEOMETRY_TYPE_CRITERIA_ID = 10;
    public const ESTIMATED_AREA_CRITERIA_ID = 12;
    public const SCHEMA_CRITERIA_ID = 13;
    public const DATA_CRITERIA_ID = 14;

    // TODO: Remove this const and its usages when the point transformation ticket is complete.
    public const TEMP_FAKE_POLYGON_UUID = 'temp_fake_polygon_uuid';

    protected const POINT_PROPERTIES = [
        'site_id',
        'poly_name',
        'plantstart',
        'plantend',
        'practice',
        'target_sys',
        'distr',
        'num_trees',
    ];

    public function createGeojsonModels($geojson, $sitePolygonProperties = []): array
    {
        if (data_get($geojson, 'features.0.geometry.type') == 'Point') {
            return $this->transformAndStorePoints($geojson, $sitePolygonProperties);
        }

        $uuids = [];
        foreach ($geojson['features'] as $feature) {
            if ($feature['geometry']['type'] === 'Polygon') {
                $data = $this->insertSinglePolygon($feature['geometry']);
                $uuids[] = $data['uuid'];
                $sitePolygonProperties['area'] = $data['area'];
                $returnSite = $this->insertSitePolygon(
                    $data['uuid'],
                    array_merge($sitePolygonProperties, $feature['properties']),
                );
                if ($returnSite) {
                    Log::info($returnSite);
                }
            } elseif ($feature['geometry']['type'] === 'MultiPolygon') {
                foreach ($feature['geometry']['coordinates'] as $polygon) {
                    $singlePolygon = ['type' => 'Polygon', 'coordinates' => $polygon];
                    $data = $this->insertSinglePolygon($singlePolygon);
                    $uuids[] = $data['uuid'];
                    $returnSite = $this->insertSitePolygon(
                        $data['uuid'],
                        array_merge($sitePolygonProperties, $feature['properties']),
                    );
                    if ($returnSite) {
                        Log::info($returnSite);
                    }
                }
            }
        }

        return $uuids;
    }

    public function createCriteriaSite($polygonId, $criteriaId, $valid): bool|string
    {
        $criteriaSite = new CriteriaSite();
        $criteriaSite->polygon_id = $polygonId;
        $criteriaSite->criteria_id = $criteriaId;
        $criteriaSite->valid = $valid;

        try {
            $criteriaSite->save();

            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Note: At this time, this method assumes that the geometry is a single polygon.
     */
    public function updateGeojsonModels(PolygonGeometry $polygonGeometry, array $geometry)
    {
        $dbGeometry = $this->getGeomAndArea(data_get($geometry, 'features.0'));
        $polygonGeometry->update(['geom' => $dbGeometry['geom']]);

        $sitePolygon = $polygonGeometry->sitePolygon()->first();
        $sitePolygon->update($this->validateSitePolygonProperties(
            $polygonGeometry->uuid,
            array_merge(['area' => $dbGeometry['area']], data_get($geometry, 'features.0.properties', []))
        ));
    }

    protected function getGeom(array $geometry)
    {
        // Convert geometry to GeoJSON string
        $geojson = json_encode(['type' => 'Feature', 'geometry' => $geometry, 'crs' => ['type' => 'name', 'properties' => ['name' => 'EPSG:4326']]]);

        // get GeoJSON data in the database
        return DB::raw("ST_GeomFromGeoJSON('$geojson')");
    }

    protected function getGeomAndArea(array $geometry): array
    {
        // Convert geometry to GeoJSON string
        $geojson = json_encode(['type' => 'Feature', 'geometry' => $geometry, 'crs' => ['type' => 'name', 'properties' => ['name' => 'EPSG:4326']]]);

        // Get GeoJSON data in the database
        $geom = DB::raw("ST_GeomFromGeoJSON('$geojson')");
        $areaSqDegrees = DB::selectOne("SELECT ST_Area(ST_GeomFromGeoJSON('$geojson')) AS area")->area;
        $latitude = DB::selectOne("SELECT ST_Y(ST_Centroid(ST_GeomFromGeoJSON('$geojson'))) AS latitude")->latitude;
        // 111320 is the length of one degree of latitude in meters at the equator
        $unitLatitude = 111320;
        $areaSqMeters = $areaSqDegrees * pow($unitLatitude * cos(deg2rad($latitude)), 2);

        $areaHectares = $areaSqMeters / 10000;

        return ['geom' => $geom, 'area' => $areaHectares];
    }

    protected function insertSinglePolygon(array $geometry): array
    {
        $dbGeometry = $this->getGeomAndArea($geometry);

        $polygonGeometry = PolygonGeometry::create([
            'geom' => $dbGeometry['geom'],
            'created_by' => Auth::user()?->id,
        ]);

        return ['uuid' => $polygonGeometry->uuid, 'area' => $dbGeometry['area']];
    }

    protected function insertSinglePoint(array $feature): string
    {
        return PointGeometry::create([
            'geom' => $this->getGeom($feature['geometry']),
            'est_area' => data_get($feature, 'properties.est_area'),
            'created_by' => Auth::user()?->id,
            'last_modified_by' => Auth::user()?->id,
        ])->uuid;
    }

    protected function insertSitePolygon(string $polygonUuid, array $properties)
    {
        try {
            SitePolygon::create(array_merge(
                $this->validateSitePolygonProperties($polygonUuid, $properties),
                [
                    'poly_id' => $polygonUuid ?? null,
                    'created_by' => Auth::user()?->id,
                ],
            ));

            return null;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    protected function validateSitePolygonProperties(string $polygonUuid, array $properties)
    {
        // Avoid trying to store an invalid date string or int in the DB, as that will throw an exception and prevent
        // the site polygon from storing. With an invalid date, this will end up reporting schema invalid and data
        // invalid, which isn't necessarily correct for the payload given, but it does reflect the status in the DB
        try {
            $properties['plantstart'] = empty($properties['plantstart']) ? null : Carbon::parse($properties['plantstart']);
        } catch (\Exception $e) {
            $properties['plantstart'] = null;
        }

        try {
            $properties['plantend'] = empty($properties['plantend']) ? null : Carbon::parse($properties['plantend']);
        } catch (\Exception $e) {
            $properties['plantend'] = null;
        }
        $properties['num_trees'] = is_int($properties['num_trees'] ?? null) ? $properties['num_trees'] : null;

        $validationGeojson = ['features' => [
            'feature' => ['properties' => $properties],
        ]];
        $validSchema = SitePolygonValidator::isValid('SCHEMA', $validationGeojson);
        $validData = SitePolygonValidator::isValid('DATA', $validationGeojson);
        $this->createCriteriaSite($polygonUuid, self::SCHEMA_CRITERIA_ID, $validSchema);
        $this->createCriteriaSite($polygonUuid, self::DATA_CRITERIA_ID, $validData);

        return [
            'poly_name' => $properties['poly_name'] ?? null,
            'site_id' => $properties['site_id'] ?? null,
            'plantstart' => $properties['plantstart'],
            'plantend' => $properties['plantend'],
            'practice' => $properties['practice'] ?? null,
            'target_sys' => $properties['target_sys'] ?? null,
            'distr' => $properties['distr'] ?? null,
            'num_trees' => $properties['num_trees'],
            'calc_area' => $properties['area'] ?? null,
            'status' => 'submitted',
            'point_id' => $properties['point_id'] ?? null
        ];
    }

    /**
     * Each Point must have an est_area property, and at least one of them must have a site_id as well as
     * all of the properties listed in SitePolygonValidator::SCHEMA for the resulting polygon to pass validation.
     *
     * @return string UUID of resulting PolygonGeometry
     */
    protected function transformAndStorePoints($geojson, $sitePolygonProperties): array
    {
        foreach ($geojson['features'] as &$feature) {
            $currentPointUUID = $this->insertSinglePoint($feature);
            $feature['properties']['point_id'] = $currentPointUUID;
        }

        $properties = $sitePolygonProperties;
        $mainSiteID = '';
        foreach (self::POINT_PROPERTIES as $property) {
            $properties[$property] = collect(data_get($geojson, "features.*.properties.$property"))->filter()->first();
            if ($property === 'site_id') {
                $mainSiteID = $properties[$property];
            }
        }

        // TODO:
        //  * transform points into a polygon DONE
        //  * Insert the polygon into PolygonGeometry DONE
        //  * Create the SitePolygon using the data  in $properties (including $properties['site_id'] to identify the site)
        //  * Return the PolygonGeometry's real UUID instead of this fake return

        $polygonsGeojson = App::make(PythonService::class)->voronoiTransformation($geojson);
        $polygonsUuids = $this->createGeojsonModels($polygonsGeojson, ['site_id' => $mainSiteID]);
        Log::info('Polygons UUIDs: ' . json_encode($polygonsUuids));
        return $polygonsUuids;
    }
}
