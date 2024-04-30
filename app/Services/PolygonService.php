<?php

namespace App\Services;

use App\Models\V2\PolygonGeometry;
use App\Models\V2\Sites\CriteriaSite;
use App\Models\V2\Sites\SitePolygon;
use App\Validators\SitePolygonValidator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class PolygonService
{
    public const OVERLAPPING_CRITERIA_ID = 3;
    public const SELF_CRITERIA_ID = 4;
    public const SIZE_CRITERIA_ID = 6;
    public const WITHIN_COUNTRY_CRITERIA_ID = 7;
    public const SPIKE_CRITERIA_ID = 8;
    public const GEOMETRY_TYPE_CRITERIA_ID = 10;
    public const ESTIMATED_AREA_CRITERIA_ID = 12;
    public const SCHEMA_CRITERIA_ID = 13;
    public const DATA_CRITERIA_ID = 14;

    /**
     * @throws ValidationException
     */
    public function createGeojsonModels($geojson, $sitePolygonProperties = []): array
    {
        SitePolygonValidator::validate('FEATURE_BOUNDS', $geojson);

        $uuids = [];
        foreach ($geojson['features'] as $feature) {
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

        try {
            $criteriaSite->save();

            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    private function insertSinglePolygon(array $geometry): array
    {
        // Convert geometry to GeoJSON string
        $geojson = json_encode(['type' => 'Feature', 'geometry' => $geometry, 'crs' => ['type' => 'name', 'properties' => ['name' => 'EPSG:4326']]]);

        // Insert GeoJSON data into the database
        $geom = DB::raw("ST_GeomFromGeoJSON('$geojson')");
        $areaSqDegrees = DB::selectOne("SELECT ST_Area(ST_GeomFromGeoJSON('$geojson')) AS area")->area;
        $latitude = DB::selectOne("SELECT ST_Y(ST_Centroid(ST_GeomFromGeoJSON('$geojson'))) AS latitude")->latitude;
        // 111320 is the length of one degree of latitude in meters at the equator
        $unitLatitude = 111320;
        $areaSqMeters = $areaSqDegrees * pow($unitLatitude * cos(deg2rad($latitude)), 2);

        $areaHectares = $areaSqMeters / 10000;

        $polygonGeometry = PolygonGeometry::create([
            'geom' => $geom,
        ]);

        return ['uuid' => $polygonGeometry->uuid, 'area' => $areaHectares];
    }

    private function insertSitePolygon(string $polygonUuid, array $properties, float $area)
    {
        try {
            $validSchema = SitePolygonValidator::isValid('SCHEMA', $properties);
            $validData = SitePolygonValidator::isValid('DATA', $properties);
            $this->createCriteriaSite($polygonUuid, self::SCHEMA_CRITERIA_ID, $validSchema);
            $this->createCriteriaSite($polygonUuid, self::DATA_CRITERIA_ID, $validData);

            $sitePolygon = new SitePolygon();
            $sitePolygon->project_id = $properties['project_id'] ?? null;
            $sitePolygon->proj_name = $properties['proj_name'] ?? null;
            $sitePolygon->org_name = $properties['org_name'] ?? null;
            $sitePolygon->country = $properties['country'] ?? null;
            $sitePolygon->poly_id = $polygonUuid ?? null;
            $sitePolygon->poly_name = $properties['poly_name'] ?? null;
            $sitePolygon->site_id = $properties['site_id'] ?? null;
            $sitePolygon->site_name = $properties['site_name'] ?? null;
            $sitePolygon->poly_label = $properties['poly_label'] ?? null;
            $sitePolygon->plantstart = ! empty($properties['plantstart']) ? $properties['plantstart'] : null;
            $sitePolygon->plantend = ! empty($properties['plantend']) ? $properties['plantend'] : null;
            $sitePolygon->practice = $properties['practice'] ?? null;
            $sitePolygon->target_sys = $properties['target_sys'] ?? null;
            $sitePolygon->distr = $properties['distr'] ?? null;
            $sitePolygon->num_trees = $properties['num_trees'] ?? null;
            $sitePolygon->est_area = $area ?? null;
            $sitePolygon->save();

            return null;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
