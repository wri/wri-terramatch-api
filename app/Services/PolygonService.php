<?php

namespace App\Services;

use App\Models\V2\PolygonGeometry;
use App\Models\V2\Sites\CriteriaSite;
use App\Models\V2\Sites\SitePolygon;
use App\Validators\SitePolygonValidator;
use Illuminate\Support\Carbon;
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

    public function createGeojsonModels($geojson, $sitePolygonProperties = [], ?string $site_id = null): array
    {
        $uuids = [];
        foreach ($geojson['features'] as $feature) {
            if ($site_id !== null){
                $feature['properties']['site_id'] = $site_id;
            }
            if ($feature['geometry']['type'] === 'Polygon') {
                $data = $this->insertSinglePolygon($feature['geometry']);
                $uuids[] = $data['uuid'];
                $sitePolygonProperties['area'] = $data['area'];
                $returnSite = $this->insertSitePolygon(
                    $data['uuid'],
                    array_merge($sitePolygonProperties, $feature['properties']),
                    $data['area']
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
                        $data['area']
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
        $criteriaSite->created_by = Auth::user()?->id;

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

    protected function getGeomAndArea(array $geometry): array
    {
        // Convert geometry to GeoJSON string
        $geojson = json_encode(['type' => 'Feature', 'geometry' => $geometry, 'crs' => ['type' => 'name', 'properties' => ['name' => 'EPSG:4326']]]);

        // Update GeoJSON data in the database
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
        // $validSchema = SitePolygonValidator::isValid('SCHEMA', $validationGeojson);
        // $validData = SitePolygonValidator::isValid('DATA', $validationGeojson);
        // $this->createCriteriaSite($polygonUuid, self::SCHEMA_CRITERIA_ID, $validSchema);
        // $this->createCriteriaSite($polygonUuid, self::DATA_CRITERIA_ID, $validData);

        return [
            // 'project_id' => $properties['project_id'] ?? null,
            // 'proj_name' => $properties['proj_name'] ?? null,
            // 'org_name' => $properties['org_name'] ?? null,
            // 'country' => $properties['country'] ?? null,
            'poly_name' => $properties['poly_name'] ?? null,
            'site_id' => $properties['site_id'] ?? null,
            // 'site_name' => $properties['site_name'] ?? null,
            // 'poly_label' => $properties['poly_label'] ?? null,
            'plantstart' => $properties['plantstart'],
            'plantend' => $properties['plantend'],
            'practice' => $properties['practice'] ?? null,
            'target_sys' => $properties['target_sys'] ?? null,
            'distr' => $properties['distr'] ?? null,
            'num_trees' => $properties['num_trees'],
            'calc_area' => $properties['area'] ?? null,
            'status' => "submitted",
        ];
    }
}