<?php

namespace App\Services;

use App\Helpers\GeometryHelper;
use App\Models\V2\PointGeometry;
use App\Models\V2\PolygonGeometry;
use App\Models\V2\ProjectPitch;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectPolygon;
use App\Models\V2\Sites\CriteriaSite;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SitePolygon;
use App\Models\V2\User;
use App\Validators\SitePolygonValidator;
use DateTime;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

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

    public const UPLOADED_SOURCE = 'uploaded';
    public const TERRAMACH_SOURCE = 'terramatch';
    public const GREENHOUSE_SOURCE = 'greenhouse';

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

    private const VALID_PRACTICES = [
        'tree-planting',
        'direct-seeding',
        'assisted-natural-regeneration',
    ];

    private const VALID_SYSTEMS = [
        'agroforest',
        'natural-forest',
        'mangrove',
        'peatland',
        'riparian-area-or-wetland',
        'silvopasture',
        'woodlot-or-plantation',
        'urban-forest',
    ];

    private const VALID_DISTRIBUTIONS = [
        'single-line',
        'partial',
        'full',
    ];

    public function createProjectPolygon($entity, $currentGeojson)
    {
        if (GeometryHelper::isFeatureCollectionEmpty($currentGeojson)) {
            return;
        }

        $needsVoronoi = GeometryHelper::isOneOrTwoPointFeatures($currentGeojson);
        if ($needsVoronoi) {
            $pointWithEstArea = GeometryHelper::addEstAreaToPointFeatures($currentGeojson);
            $currentGeojson = App::make(PythonService::class)->voronoiTransformation(json_decode($pointWithEstArea));
        }

        $convexHull = GeometryHelper::getConvexHull($currentGeojson);
        if ($convexHull) {
            $polygonGeometry = new PolygonGeometry();
            $polygonGeometry->geom = DB::raw("ST_GeomFromText('" . $convexHull . "')");
            $polygonGeometry->save();

            ProjectPolygon::create([
                'poly_uuid' => $polygonGeometry->uuid,
                'entity_type' => get_class($entity),
                'entity_id' => $entity->id,
                'last_modified_by' => Auth::user() ? Auth::user()?->id : 'system',
                'created_by' => Auth::user() ? Auth::user()?->id : 'system',
            ]);

            return $polygonGeometry->uuid;
        }
    }

    public function getEntity($entity_type, $entity_uuid)
    {
        switch ($entity_type) {
            case 'project':
                return Project::isUuid($entity_uuid)->first();
            case 'project-pitch':
                return ProjectPitch::isUuid($entity_uuid)->first();
            default:
                throw new InvalidArgumentException("Invalid entity type: $entity_type");
        }
    }

    public function processEntity($entity)
    {
        $geojsonField = $entity instanceof ProjectPitch ? 'proj_boundary' : 'boundary_geojson';
        $currentGeojson = $entity->$geojsonField;

        if ($currentGeojson) {
            $this->createProjectPolygon($entity, $currentGeojson);
        }
    }

    public function createGeojsonModels($geojson, $sitePolygonProperties = [], ?string $primary_uuid = null, ?bool $submit_polygon_loaded = false): array
    {
        try {
            if (data_get($geojson, 'features.0.geometry.type') == 'Point') {
                return $this->transformAndStorePoints($geojson, $sitePolygonProperties);
            }
            $uuids = [];
            foreach ($geojson['features'] as $feature) {
                DB::beginTransaction();

                try {
                    if ($feature['geometry']['type'] === 'Polygon') {
                        $data = $this->insertSinglePolygon($feature['geometry']);
                        $sitePolygonProperties['area'] = $data['area'];
                        $this->attemptPolygonInsert($data['uuid'], $sitePolygonProperties, $feature, $primary_uuid, $submit_polygon_loaded);
                        DB::commit();
                        $uuids[] = $data['uuid'];

                    } elseif ($feature['geometry']['type'] === 'MultiPolygon') {
                        foreach ($feature['geometry']['coordinates'] as $polygon) {
                            $singlePolygon = ['type' => 'Polygon', 'coordinates' => $polygon];
                            $data = $this->insertSinglePolygon($singlePolygon);
                            $sitePolygonProperties['area'] = $data['area'];
                            $this->attemptPolygonInsert($data['uuid'], $sitePolygonProperties, $feature, $primary_uuid, $submit_polygon_loaded);
                            DB::commit();
                            $uuids[] = $data['uuid'];
                        }
                    }
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('Error with polygon, rolled back current transaction', [
                        'uuid' => $feature['properties']['uuid'] ?? 'unknown',
                        'error' => $e->getMessage(),
                    ]);

                    continue;
                }
            }

            return $uuids;

        } catch (\Exception $e) {
            return response()->json(['error at create geojson models' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function attemptPolygonInsert($uuid, $sitePolygonProperties, $feature, ?string $primary_uuid, ?bool $submit_polygon_loaded)
    {
        if ($submit_polygon_loaded && isset($feature['properties']['uuid'])) {
            $this->insertPolygon($uuid, $sitePolygonProperties, $feature['properties'], $feature['properties']['uuid'], $submit_polygon_loaded);
        } else {
            $this->insertPolygon($uuid, $sitePolygonProperties, $feature['properties'], $primary_uuid);
        }
    }

    private function insertPolygon($uuid, $sitePolygonProperties, $featureProperties, ?string $primary_uuid, ?bool $submit_polygon_loaded = false)
    {
        try {

            if (isset($sitePolygonProperties['site_id']) && $sitePolygonProperties['site_id'] !== null) {
                $featureProperties['site_id'] = $sitePolygonProperties['site_id'];
            }
            if($primary_uuid) {
                $result = $this->insertSitePolygonVersion($uuid, $primary_uuid, $submit_polygon_loaded, $featureProperties);
                if ($result === false) {
                    $this->insertSitePolygon(
                        $uuid,
                        array_merge($sitePolygonProperties, $featureProperties)
                    );
                }
            } else {
                $this->insertSitePolygon(
                    $uuid,
                    array_merge($sitePolygonProperties, $featureProperties),
                );
            }
        } catch (\Exception $e) {
            Log::error('Error inserting polygon', [
              'uuid' => $uuid,
              'primary_uuid' => $primary_uuid,
              'submit_polygon_loaded' => $submit_polygon_loaded,
              'error' => $e->getMessage(),
            ]);

            throw new \Exception('Error inserting polygon: ' . $e->getMessage());
        }
    }

    public function createCriteriaSite($polygonId, $criteriaId, $valid, $extraInfo = null): bool|string
    {
        $criteriaSite = new CriteriaSite();
        $criteriaSite->polygon_id = $polygonId;
        $criteriaSite->criteria_id = $criteriaId;
        $criteriaSite->valid = $valid;
        $criteriaSite->extra_info = $extraInfo ? json_encode($extraInfo) : null;

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
        $project = $sitePolygon->project()->first();
        $geometryHelper = new GeometryHelper();
        $geometryHelper->updateProjectCentroid($project->uuid);
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
            $site = Site::isUuid($properties['site_id'])->first();
            if (! $site) {
                throw new \Exception('SitePolygon not found for site_id: ' . $properties['site_id']);
            }
            $sitePolygon = SitePolygon::create(array_merge(
                $this->validateSitePolygonProperties($polygonUuid, $properties),
                [
                    'poly_id' => $polygonUuid ?? null,
                    'created_by' => Auth::user()?->id,
                    'is_active' => true,
                ],
            ));
            $site = $sitePolygon->site()->first();
            if (! $site) {
                Log::error('Site not found', ['site polygon uuid' => $sitePolygon->uuid, 'site id' => $sitePolygon->site_id]);

                return response()->json(['error' => 'Site not found'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            $site->restorationInProgress();
            $project = $sitePolygon->project()->first();
            $geometryHelper = new GeometryHelper();
            $geometryHelper->updateProjectCentroid($project->uuid);

            return null;
        } catch (\Exception $e) {
            if (! $sitePolygon) {
                throw new \Exception('SitePolygon not found for site_id: ' . $properties['site_id']);
            }
        }
    }

    protected function insertSitePolygonVersion(string $polygonUuid, string $primary_uuid, ?bool $submit_polygon_loaded = false, ?array $properties)
    {
        try {
            $sitePolygon = SitePolygon::isUuid($primary_uuid)->active()->first();
            if (! $sitePolygon) {
                return false;
            }
            $user = Auth::check() ? Auth::user() : null;

            if ($user) {
                $user = User::isUuid($user->uuid)->first();
            } else {
                $user = User::find(1);
            }
            $newSitePolygon = $sitePolygon->createCopy($user, $polygonUuid, $submit_polygon_loaded, $this->validateSitePolygonProperties($polygonUuid, $properties));
            if (! $newSitePolygon) {
                return false;
            }
            $site = $newSitePolygon->site()->first();
            if (! $site) {
                Log::error('Site not found', ['site polygon uuid' => $newSitePolygon->uuid, 'site id' => $newSitePolygon->site_id]);

                return false;

            }
            $site->restorationInProgress();
            $project = $newSitePolygon->project()->first();
            $geometryHelper = new GeometryHelper();
            $geometryHelper->updateProjectCentroid($project->uuid);

            return true;
        } catch (\Exception $e) {
            Log::error('Error inserting site polygon version', ['polygon uuid' => $polygonUuid, 'error' => $e->getMessage()]);

            return response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
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
            'status' => 'draft',
            'point_id' => $properties['point_id'] ?? null,
            'source' => $properties['source'] ?? null,
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

        $polygonsGeojson = App::make(PythonService::class)->voronoiTransformation($geojson);

        if (is_null($polygonsGeojson)) {
            throw new \Exception('Voronoi transformation returned null');
        }

        return $this->createGeojsonModels($polygonsGeojson, $sitePolygonProperties);
    }

    public function isInvalidField($field, $value)
    {
        if (is_null($value) || $value === '') {
            return true;
        }

        switch ($field) {
            case 'plantstart':
                return ! $this->isValidDate($value);
            case 'plantend':
                return ! $this->isValidDate($value);
            case 'practice':
                return ! $this->areValidItems($value, self::VALID_PRACTICES);
            case 'target_sys':
                return ! in_array($value, self::VALID_SYSTEMS);
            case 'distr':
                return ! $this->areValidItems($value, self::VALID_DISTRIBUTIONS);
            case 'num_trees':
                return ! filter_var($value, FILTER_VALIDATE_INT);
            default:
                return false;
        }
    }

    private function areValidItems($value, $validItems)
    {
        $items = explode(',', $value);
        foreach ($items as $item) {
            if (! in_array(trim($item), $validItems)) {
                return false;
            }
        }

        return true;
    }

    private function isValidDate($date)
    {
        try {
            $d = DateTime::createFromFormat('Y-m-d', $date);

            return $d && $d->format('Y-m-d') === $date;
        } catch (Exception $e) {
            return false;
        }
    }
}
