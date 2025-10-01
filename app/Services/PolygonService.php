<?php

namespace App\Services;

use App\Constants\PolygonFields;
use App\Helpers\CreateVersionPolygonGeometryHelper;
use App\Helpers\GeometryHelper;
use App\Helpers\PolygonGeometryHelper;
use App\Models\DelayedJobProgress;
use App\Models\Traits\IndicatorUpdateTrait;
use App\Models\V2\PointGeometry;
use App\Models\V2\PolygonGeometry;
use App\Models\V2\ProjectPitch;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectPolygon;
use App\Models\V2\Sites\CriteriaSite;
use App\Models\V2\Sites\CriteriaSiteHistoric;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SitePolygon;
use App\Models\V2\Sites\SitePolygonData;
use App\Models\V2\User;
use App\StateMachines\SiteStatusStateMachine;
use App\Validators\SitePolygonValidator;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use InvalidArgumentException;

class PolygonService
{
    use IndicatorUpdateTrait;
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
    public const PLANT_START_DATE_CRITERIA_ID = 15;

    public const UPLOADED_SOURCE = 'uploaded';
    public const TERRAMACH_SOURCE = 'terramatch';
    public const GREENHOUSE_SOURCE = 'greenhouse';

    public const EXCLUDED_VALIDATION_CRITERIA = [
      self::ESTIMATED_AREA_CRITERIA_ID,
      self::WITHIN_COUNTRY_CRITERIA_ID,
      self::PLANT_START_DATE_CRITERIA_ID,
    ];

    // TODO: Remove this const and its usages when the point transformation ticket is complete.
    public const TEMP_FAKE_POLYGON_UUID = 'temp_fake_polygon_uuid';

    protected const POINT_PROPERTIES = PolygonFields::POINT_PROPERTIES;

    private const VALID_PRACTICES = [
        'tree-planting',
        'direct-seeding',
        'assisted-natural-regeneration',
    ];

    private const VALID_SYSTEMS = [
        'agroforest',
        'grassland',
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

    public function createGeojsonModelsBulk(array $geojson, array $sitePolygonProperties = [], ?string $primary_uuid = null, ?bool $submit_polygon_loaded = false): array
    {
        return $this->processGeojsonModels($geojson, $sitePolygonProperties, $primary_uuid, $submit_polygon_loaded, 'bulk');
    }

    /**
     * Create geojson models with versioning support for uploads
     * This function handles both new polygons and versioning while maintaining bulk performance
     */
    public function createGeojsonModelsBulkWithVersioning(array $geojson, array $sitePolygonProperties = [], ?string $primary_uuid = null, ?bool $submit_polygon_loaded = false): array
    {
        return $this->processGeojsonModels($geojson, $sitePolygonProperties, $primary_uuid, $submit_polygon_loaded, 'bulk_with_versioning');
    }

    private function shouldCreateVersion(array $feature, ?bool $submit_polygon_loaded, ?string $primary_uuid): bool
    {
        if ($submit_polygon_loaded && isset($feature['properties']['uuid'])) {
            return true;
        }

        if ($primary_uuid != null) {
            return true;
        }

        return false;
    }

    private function processVersioningPolygons(array $features, array $sitePolygonProperties, ?bool $submit_polygon_loaded, ?string $primary_uuid = null): array
    {
        $uuids = [];

        DB::beginTransaction();

        try {
            foreach ($features as $feature) {
                if ($feature['geometry']['type'] === 'Polygon') {
                    $data = $this->insertSinglePolygon($feature['geometry']);
                    $sitePolygonProperties['area'] = $data['area'];
                    $this->attemptPolygonInsert(
                        $data['uuid'],
                        $sitePolygonProperties,
                        $feature,
                        $primary_uuid,
                        $submit_polygon_loaded
                    );
                    $uuids[] = $data['uuid'];
                } elseif ($feature['geometry']['type'] === 'MultiPolygon') {
                    foreach ($feature['geometry']['coordinates'] as $polygon) {
                        $singlePolygon = ['type' => 'Polygon', 'coordinates' => $polygon];
                        $data = $this->insertSinglePolygon($singlePolygon);
                        $sitePolygonProperties['area'] = $data['area'];
                        $this->attemptPolygonInsert(
                            $data['uuid'],
                            $sitePolygonProperties,
                            $feature,
                            $primary_uuid,
                            $submit_polygon_loaded
                        );
                        $uuids[] = $data['uuid'];
                    }
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing versioning polygons', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }

        return $uuids;
    }

    private function processNewPolygonsBulk(array $features, array $sitePolygonProperties): array
    {
        $polygonData = [];
        $polygonInserts = [];
        $uuids = [];
        $geometriesToProcess = [];
        $geometryIndexMap = [];

        $geometryIndex = 0;
        foreach ($features as $feature) {
            if ($feature['geometry']['type'] === 'Polygon') {
                $uuid = Str::uuid()->toString();
                $geometriesToProcess[] = $feature['geometry'];
                $geometryIndexMap[$geometryIndex] = [
                    'uuid' => $uuid,
                    'feature' => $feature,
                    'sitePolygonProperties' => $sitePolygonProperties,
                ];
                $uuids[] = $uuid;
                $geometryIndex++;

            } elseif ($feature['geometry']['type'] === 'MultiPolygon') {
                foreach ($feature['geometry']['coordinates'] as $polygon) {
                    $singlePolygon = ['type' => 'Polygon', 'coordinates' => $polygon];
                    $uuid = Str::uuid()->toString();
                    $geometriesToProcess[] = $singlePolygon;
                    $geometryIndexMap[$geometryIndex] = [
                        'uuid' => $uuid,
                        'feature' => $feature,
                        'sitePolygonProperties' => $sitePolygonProperties,
                    ];
                    $uuids[] = $uuid;
                    $geometryIndex++;
                }
            }
        }

        $areaCalculationService = app(AreaCalculationService::class);
        $geometryResults = $areaCalculationService->batchGetGeomsAndAreas($geometriesToProcess);

        foreach ($geometryResults as $index => $geometryAndArea) {
            $mapping = $geometryIndexMap[$index];

            $polygonInserts[] = [
                'uuid' => $mapping['uuid'],
                'geom' => $geometryAndArea['geom'],
                'created_by' => Auth::user()?->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $polygonData[] = [
                'uuid' => $mapping['uuid'],
                'area' => $geometryAndArea['area'],
                'feature' => $mapping['feature'],
                'sitePolygonProperties' => $mapping['sitePolygonProperties'],
            ];
        }

        DB::beginTransaction();

        try {
            $chunkSize = 500;
            foreach (array_chunk($polygonInserts, $chunkSize) as $chunk) {
                DB::table('polygon_geometry')->insert($chunk);
            }

            $this->bulkInsertSitePolygons($polygonData, $chunkSize);

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error with bulk polygon insertion for new polygons, rolled back all transactions', [
                'error' => $e->getMessage(),
                'polygons_count' => count($polygonData),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }

        return $uuids;
    }

    public function createGeojsonModels(array $geojson, array $sitePolygonProperties = [], ?string $primary_uuid = null, ?bool $submit_polygon_loaded = false): array
    {
        return $this->processGeojsonModels($geojson, $sitePolygonProperties, $primary_uuid, $submit_polygon_loaded, 'individual');
    }

    /**
     * Unified method to process GeoJSON models with different strategies
     *
     * @param array $geojson The GeoJSON data to process
     * @param array $sitePolygonProperties Properties for site polygons
     * @param string|null $primary_uuid Primary UUID for versioning
     * @param bool|null $submit_polygon_loaded Whether polygon is loaded for submission
     * @param string $strategy Processing strategy: 'bulk', 'bulk_with_versioning', or 'individual'
     * @return array Array of created polygon UUIDs
     */
    private function processGeojsonModels(array $geojson, array $sitePolygonProperties = [], ?string $primary_uuid = null, ?bool $submit_polygon_loaded = false, string $strategy = 'bulk'): array
    {
        try {
            if (data_get($geojson, 'features.0.geometry.type') == 'Point') {
                /** @var array $result */
                $result = $this->transformAndStorePoints($geojson, $sitePolygonProperties);

                return $result;
            }

            switch ($strategy) {
                case 'bulk':
                    return $this->processBulkStrategy($geojson, $sitePolygonProperties, $primary_uuid, $submit_polygon_loaded);
                case 'bulk_with_versioning':
                    return $this->processBulkWithVersioningStrategy($geojson, $sitePolygonProperties, $primary_uuid, $submit_polygon_loaded);
                case 'individual':
                    return $this->processIndividualStrategy($geojson, $sitePolygonProperties, $primary_uuid, $submit_polygon_loaded);
                default:
                    throw new \InvalidArgumentException("Invalid strategy: {$strategy}");
            }

        } catch (\Exception $e) {
            Log::error("Error at process geojson models with strategy: {$strategy}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new \RuntimeException("Error creating geojson models with strategy {$strategy}: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Process GeoJSON using bulk strategy for maximum performance
     */
    private function processBulkStrategy(array $geojson, array $sitePolygonProperties, ?string $primary_uuid, ?bool $submit_polygon_loaded): array
    {
        $processedData = $this->prepareGeometryData($geojson, $sitePolygonProperties, $primary_uuid, $submit_polygon_loaded);

        return $this->executeBulkInsert($processedData);
    }

    /**
     * Process GeoJSON using bulk strategy with versioning support
     */
    private function processBulkWithVersioningStrategy(array $geojson, array $sitePolygonProperties, ?string $primary_uuid, ?bool $submit_polygon_loaded): array
    {
        $versioningPolygons = [];
        $newPolygons = [];
        $allUuids = [];

        foreach ($geojson['features'] as $feature) {
            if ($this->shouldCreateVersion($feature, $submit_polygon_loaded, $primary_uuid)) {
                $versioningPolygons[] = $feature;
            } else {
                $newPolygons[] = $feature;
            }
        }

        if (! empty($versioningPolygons)) {
            $versionUuids = $this->processVersioningPolygons($versioningPolygons, $sitePolygonProperties, $submit_polygon_loaded, $primary_uuid);
            $allUuids = array_merge($allUuids, $versionUuids);
        }

        if (! empty($newPolygons)) {
            $newUuids = $this->processNewPolygonsBulk($newPolygons, $sitePolygonProperties);
            $allUuids = array_merge($allUuids, $newUuids);
        }

        return $allUuids;
    }

    /**
     * Process GeoJSON using individual strategy for single polygon processing
     */
    private function processIndividualStrategy(array $geojson, array $sitePolygonProperties, ?string $primary_uuid, ?bool $submit_polygon_loaded): array
    {
        $uuids = [];
        $polygonData = [];

        foreach ($geojson['features'] as $feature) {
            if ($feature['geometry']['type'] === 'Polygon') {
                $data = $this->insertSinglePolygon($feature['geometry']);
                $polygonData[] = [
                    'uuid' => $data['uuid'],
                    'area' => $data['area'],
                    'feature' => $feature,
                    'sitePolygonProperties' => $sitePolygonProperties,
                    'primary_uuid' => $primary_uuid,
                    'submit_polygon_loaded' => $submit_polygon_loaded,
                ];
                $uuids[] = $data['uuid'];
            } elseif ($feature['geometry']['type'] === 'MultiPolygon') {
                foreach ($feature['geometry']['coordinates'] as $polygon) {
                    $singlePolygon = ['type' => 'Polygon', 'coordinates' => $polygon];
                    $data = $this->insertSinglePolygon($singlePolygon);
                    $polygonData[] = [
                        'uuid' => $data['uuid'],
                        'area' => $data['area'],
                        'feature' => $feature,
                        'sitePolygonProperties' => $sitePolygonProperties,
                        'primary_uuid' => $primary_uuid,
                        'submit_polygon_loaded' => $submit_polygon_loaded,
                    ];
                    $uuids[] = $data['uuid'];
                }
            }
        }

        DB::beginTransaction();

        try {
            foreach ($polygonData as $polygon) {
                $sitePolygonProps = $polygon['sitePolygonProperties'];
                $sitePolygonProps['area'] = $polygon['area'];
                $this->attemptPolygonInsert(
                    $polygon['uuid'],
                    $sitePolygonProps,
                    $polygon['feature'],
                    $polygon['primary_uuid'],
                    $polygon['submit_polygon_loaded']
                );
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error with batch polygon insertion, rolled back all transactions', [
                'error' => $e->getMessage(),
                'polygons_count' => count($polygonData),
            ]);

            throw $e;
        }

        return $uuids;
    }

    /**
     * Prepare geometry data for bulk processing
     */
    private function prepareGeometryData(array $geojson, array $sitePolygonProperties, ?string $primary_uuid, ?bool $submit_polygon_loaded): array
    {
        $polygonData = [];
        $polygonInserts = [];
        $uuids = [];
        $geometriesToProcess = [];
        $geometryIndexMap = [];

        $geometryIndex = 0;
        foreach ($geojson['features'] as $feature) {
            $featureData = $this->processFeatureGeometry($feature, $sitePolygonProperties, $primary_uuid, $submit_polygon_loaded);

            foreach ($featureData['geometries'] as $geometry) {
                $geometriesToProcess[] = $geometry['geometry'];
                $geometryIndexMap[$geometryIndex] = [
                    'uuid' => $geometry['uuid'],
                    'feature' => $feature,
                    'sitePolygonProperties' => $sitePolygonProperties,
                    'primary_uuid' => $primary_uuid,
                    'submit_polygon_loaded' => $submit_polygon_loaded,
                ];
                $uuids[] = $geometry['uuid'];
                $geometryIndex++;
            }
        }

        $areaCalculationService = app(AreaCalculationService::class);
        $geometryResults = $areaCalculationService->batchGetGeomsAndAreas($geometriesToProcess);

        foreach ($geometryResults as $index => $geometryAndArea) {
            $mapping = $geometryIndexMap[$index];

            $polygonInserts[] = [
                'uuid' => $mapping['uuid'],
                'geom' => $geometryAndArea['geom'],
                'created_by' => Auth::user()?->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $polygonData[] = [
                'uuid' => $mapping['uuid'],
                'area' => $geometryAndArea['area'],
                'feature' => $mapping['feature'],
                'sitePolygonProperties' => $mapping['sitePolygonProperties'],
                'primary_uuid' => $mapping['primary_uuid'],
                'submit_polygon_loaded' => $mapping['submit_polygon_loaded'],
            ];
        }

        return [
            'polygonInserts' => $polygonInserts,
            'polygonData' => $polygonData,
            'uuids' => $uuids,
        ];
    }

    /**
     * Process a single feature's geometry, handling both Polygon and MultiPolygon types
     */
    private function processFeatureGeometry(array $feature, array $sitePolygonProperties, ?string $primary_uuid, ?bool $submit_polygon_loaded): array
    {
        $uuids = [];
        $polygonData = [];
        $geometries = [];

        if ($feature['geometry']['type'] === 'Polygon') {
            $uuid = Str::uuid()->toString();
            $geometries[] = [
                'uuid' => $uuid,
                'geometry' => $feature['geometry'],
            ];
            $uuids[] = $uuid;
        } elseif ($feature['geometry']['type'] === 'MultiPolygon') {
            foreach ($feature['geometry']['coordinates'] as $polygon) {
                $singlePolygon = ['type' => 'Polygon', 'coordinates' => $polygon];
                $uuid = Str::uuid()->toString();
                $geometries[] = [
                    'uuid' => $uuid,
                    'geometry' => $singlePolygon,
                ];
                $uuids[] = $uuid;
            }
        }

        return [
            'uuids' => $uuids,
            'polygonData' => $polygonData,
            'geometries' => $geometries,
        ];
    }

    /**
     * Execute bulk insert operation with transaction handling
     */
    private function executeBulkInsert(array $processedData): array
    {
        DB::beginTransaction();

        try {
            $chunkSize = 500;
            foreach (array_chunk($processedData['polygonInserts'], $chunkSize) as $chunk) {
                DB::table('polygon_geometry')->insert($chunk);
            }

            $this->bulkInsertSitePolygons($processedData['polygonData'], $chunkSize);

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error with bulk polygon insertion, rolled back all transactions', [
                'error' => $e->getMessage(),
                'polygons_count' => count($processedData['polygonData']),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }

        return $processedData['uuids'];
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
            if ($primary_uuid) {
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

    protected function bulkInsertSitePolygons(array $polygonData, int $chunkSize = 500): void
    {
        $sitePolygonInserts = [];
        $sitePolygonDataInserts = [];
        $polygonUuidsForCentroidUpdate = [];

        foreach ($polygonData as $polygon) {
            $sitePolygonProps = $polygon['sitePolygonProperties'];
            $sitePolygonProps['area'] = $polygon['area'];
            $feature = $polygon['feature'];
            if (isset($sitePolygonProps['site_id']) && $sitePolygonProps['site_id'] !== null) {
                $feature['properties']['site_id'] = $sitePolygonProps['site_id'];
            }

            $allProperties = array_merge($sitePolygonProps, $feature['properties']);
            $validatedProperties = $this->validateSitePolygonProperties($polygon['uuid'], $allProperties);
            $extraProperties = array_diff_key($allProperties, $validatedProperties);
            $columnsToRemove = ['area', 'uuid'];
            $extraDataToStore = array_diff_key($extraProperties, array_flip($columnsToRemove));

            $sitePolygonUuid = Str::uuid()->toString();

            $sitePolygonInserts[] = array_merge(
                $validatedProperties,
                [
                    'uuid' => $sitePolygonUuid,
                    'primary_uuid' => $sitePolygonUuid,
                    'poly_id' => $polygon['uuid'],
                    'created_by' => Auth::user()?->id,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            // Collect polygon UUIDs for bulk centroid update
            $polygonUuidsForCentroidUpdate[] = $polygon['uuid'];

            if (! empty($extraDataToStore)) {
                $sitePolygonDataInserts[] = [
                    'site_polygon_uuid' => $sitePolygonUuid,
                    'data' => json_encode($extraDataToStore),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        foreach (array_chunk($sitePolygonInserts, $chunkSize) as $chunk) {
            DB::table('site_polygon')->insert($chunk);
        }

        // Bulk update centroids for all inserted site polygons
        $this->bulkUpdateSitePolygonCentroids($polygonUuidsForCentroidUpdate);

        if (! empty($sitePolygonDataInserts)) {
            foreach (array_chunk($sitePolygonDataInserts, $chunkSize) as $chunk) {
                DB::table('site_polygon_data')->insert($chunk);
            }
        }

        $this->deferSiteRestorationUpdates($sitePolygonInserts);
    }

    protected function deferSiteRestorationUpdates(array $sitePolygonInserts): void
    {
        $siteIds = array_unique(array_filter(array_column($sitePolygonInserts, 'site_id')));

        if (! empty($siteIds)) {
            DB::table('v2_sites')
              ->whereIn('uuid', $siteIds)
              ->whereNotIn('status', [SiteStatusStateMachine::RESTORATION_IN_PROGRESS])
              ->update([
                  'status' => SiteStatusStateMachine::RESTORATION_IN_PROGRESS,
                  'updated_at' => now(),
              ]);
        }
    }

    public function batchUpdateIndicators(array $polygonUuids): void
    {
        foreach ($polygonUuids as $uuid) {
            try {
                $this->updateIndicatorsForPolygon($uuid);
            } catch (\Exception $e) {
                Log::warning('Failed to update indicators for polygon', [
                    'uuid' => $uuid,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    public function createCriteriaSite($polygonId, $criteriaId, $valid, $extraInfo = null): bool|string
    {
        try {
            $existingCriteriaSite = CriteriaSite::where('polygon_id', $polygonId)
                                                ->where('criteria_id', $criteriaId)
                                                ->first();

            if ($existingCriteriaSite) {
                CriteriaSiteHistoric::create([
                    'polygon_id' => $existingCriteriaSite->polygon_id,
                    'criteria_id' => $existingCriteriaSite->criteria_id,
                    'valid' => $existingCriteriaSite->valid,
                    'extra_info' => $existingCriteriaSite->extra_info,
                    'created_at' => $existingCriteriaSite->created_at,
                    'updated_at' => $existingCriteriaSite->updated_at,
                ]);

                $existingCriteriaSite->delete();
            }

            $criteriaSite = new CriteriaSite();
            $criteriaSite->polygon_id = $polygonId;
            $criteriaSite->criteria_id = $criteriaId;
            $criteriaSite->valid = $valid;
            $criteriaSite->extra_info = $extraInfo ? json_encode($extraInfo) : null;
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
        $dbGeometry = $this->getGeomAndArea(data_get($geometry, 'features.0.geometry'));
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
        $areaCalculationService = app(AreaCalculationService::class);

        return $areaCalculationService->getGeomAndArea($geometry);
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

            $validatedProperties = $this->validateSitePolygonProperties($polygonUuid, $properties);
            $extraProperties = array_diff_key($properties, $validatedProperties);
            $columnsToRemove = ['area', 'uuid'];
            $extraDataToStore = array_diff_key($extraProperties, array_flip($columnsToRemove));

            $sitePolygon = SitePolygon::create(array_merge(
                $validatedProperties,
                [
                    'poly_id' => $polygonUuid ?? null,
                    'created_by' => Auth::user()?->id,
                  'is_active' => true,
                ],
            ));

            if (! empty($extraDataToStore)) {
                $sitePolygonData = SitePolygonData::create([
                    'site_polygon_uuid' => $sitePolygon->uuid,
                    'data' => $extraDataToStore,
                ]);
                $sitePolygonData->save();
            }

            $site->restorationInProgress();
            $project = $sitePolygon->project()->first();
            $geometryHelper = new GeometryHelper();
            $geometryHelper->updateProjectCentroid($project->uuid);

            return null;
        } catch (\Exception $e) {
            Log::error('Error inserting site polygon', [
                'polygon_uuid' => $polygonUuid,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new \RuntimeException('Error inserting site polygon: ' . $e->getMessage(), 0, $e);
        }
    }

    protected function bulkUpdateSitePolygonCentroids(array $polygonUuids): void
    {
        if (empty($polygonUuids)) {
            return;
        }

        DB::statement("
            UPDATE site_polygon sp
            JOIN polygon_geometry pg ON sp.poly_id = pg.uuid
            SET 
                sp.lat = ST_Y(ST_Centroid(pg.geom)),
                sp.long = ST_X(ST_Centroid(pg.geom))
            WHERE sp.poly_id IN ('" . implode("','", $polygonUuids) . "')
        ");
    }

    protected function insertSitePolygonVersion(string $polygonUuid, string $primary_uuid, ?bool $submit_polygon_loaded = false, ?array $properties)
    {
        try {
            $sitePolygon = SitePolygon::isUuid($primary_uuid)->active()->first();
            if (! $sitePolygon) {
                Log::warning('No active polygon found for primary_uuid', ['primary_uuid' => $primary_uuid]);

                return false;
            }

            $user = Auth::check() ? Auth::user() : null;

            if ($user) {
                $user = User::isUuid($user->uuid)->first();
            } else {
                $user = User::find(1);
            }

            $validatedProperties = $this->validateSitePolygonProperties($polygonUuid, $properties);
            $extraProperties = array_diff_key($properties, $validatedProperties);
            $columnsToRemove = ['area', 'uuid'];
            $extraDataToStore = array_diff_key($extraProperties, array_flip($columnsToRemove));

            $newSitePolygon = $sitePolygon->createCopy(
                $user,
                $polygonUuid,
                $submit_polygon_loaded,
                $validatedProperties
            );
            if (! $newSitePolygon) {
                return false;
            }

            if (! empty($extraDataToStore)) {
                $sitePolygonData = SitePolygonData::create([
                    'site_polygon_uuid' => $newSitePolygon->uuid,
                    'data' => $extraDataToStore,
                ]);
                $sitePolygonData->save();
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
            Log::error('Error inserting site polygon version', [
                'polygon_uuid' => $polygonUuid,
                'primary_uuid' => $primary_uuid,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new \RuntimeException('Error inserting site polygon version: ' . $e->getMessage(), 0, $e);
        }
    }

    protected function orderCommaSeparatedPropertiesAlphabetically(string $commaSeparatedProperties, array $validValues)
    {
        $properties = explode(',', $commaSeparatedProperties);
        $properties = array_map('trim', $properties);
        sort($properties);
        $properties = array_filter($properties, function ($value) use ($validValues) {
            return in_array($value, $validValues);
        });
        if (empty($properties)) {
            return null;
        }

        return implode(',', $properties);
    }

    protected function validateTargetSys(string $targetSys): ?string
    {
        $validValues = [
            'agroforest',
            'grassland',
            'mangrove',
            'natural-forest',
            'peatland',
            'riparian-area-or-wetland',
            'silvopasture',
            'urban-forest',
            'woodlot-or-plantation',
        ];

        $targetSys = trim($targetSys);

        return in_array($targetSys, $validValues, true) ? $targetSys : null;
    }

    public function validateSitePolygonProperties(string $polygonUuid, array $properties)
    {
        // Avoid trying to store an invalid date string or int in the DB, as that will throw an exception and prevent
        // the site polygon from storing. With an invalid date, this will end up reporting schema invalid and data
        // invalid, which isn't necessarily correct for the payload given, but it does reflect the status in the DB
        try {
            $properties['plantstart'] = empty($properties['plantstart']) ? null : Carbon::parse($properties['plantstart']);
        } catch (\Exception $e) {
            $properties['plantstart'] = null;
        }

        $properties['num_trees'] = is_int($properties['num_trees'] ?? null) ? $properties['num_trees'] : null;

        $distributionsValidValues = ['full', 'partial', 'single-line'];
        $properties['distr'] = $this->orderCommaSeparatedPropertiesAlphabetically($properties['distr'] ?? '', $distributionsValidValues);

        $practicesValidValues = ['assisted-natural-regeneration', 'direct-seeding','tree-planting'];
        $properties['practice'] = $this->orderCommaSeparatedPropertiesAlphabetically($properties['practice'] ?? '', $practicesValidValues);
        $properties['target_sys'] = $this->validateTargetSys($properties['target_sys'] ?? '');

        return [
            'poly_name' => $properties['poly_name'] ?? null,
            'site_id' => $properties['site_id'] ?? null,
            'plantstart' => $properties['plantstart'],
            'practice' => $properties['practice'],
            'target_sys' => $properties['target_sys'],
            'distr' => $properties['distr'],
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

    /**
      * @throws ValidationException
    */
    public function insertGeojsonToDB(string $geojsonFilename, ?string $entity_uuid = null, ?string $entity_type = null, ?string $primary_uuid = null, ?bool $submit_polygon_loaded = false)
    {
        try {
            $tempDir = sys_get_temp_dir();
            $geojsonPath = $tempDir . DIRECTORY_SEPARATOR . $geojsonFilename;
            $geojsonData = file_get_contents($geojsonPath);

            if ($entity_type === 'project' || $entity_type === 'project-pitch') {
                $entity = $this->getEntity($entity_type, $entity_uuid);

                $hasBeenDeleted = GeometryHelper::deletePolygonWithRelated($entity);

                if ($entity && $hasBeenDeleted) {
                    return $this->createProjectPolygon($entity, $geojsonData);
                } else {
                    return ['error' => 'Entity not found'];
                }
            } else {
                $geojson = json_decode($geojsonData, true);

                SitePolygonValidator::validate('FEATURE_BOUNDS', $geojson, false);
                SitePolygonValidator::validate('GEOMETRY_TYPE', $geojson, false);

                return $this->createGeojsonModels($geojson, ['site_id' => $entity_uuid, 'source' => PolygonService::UPLOADED_SOURCE], $primary_uuid, $submit_polygon_loaded);

            }

        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            $decodedErrorMessage = json_decode($errorMessage, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return ['error' => $decodedErrorMessage];
            } else {
                Log::error('Error inserting geojson to DB', ['error' => $errorMessage]);

                return ['error' => $errorMessage];
            }
        }
    }

    /**
    * @throws ValidationException
    */
    public function insertGeojsonToDBFromContent(string $geojsonData, ?string $entity_uuid = null, ?string $entity_type = null, ?string $primary_uuid = null, ?bool $submit_polygon_loaded = false)
    {
        try {
            $geojson = json_decode($geojsonData, true);
            SitePolygonValidator::validate('FEATURE_BOUNDS', $geojson, false);
            SitePolygonValidator::validate('GEOMETRY_TYPE', $geojson, false);

            return $this->createGeojsonModelsBulkWithVersioning($geojson, ['site_id' => $entity_uuid, 'source' => PolygonService::UPLOADED_SOURCE], $primary_uuid, $submit_polygon_loaded);

        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            $decodedError = json_decode($errorMessage, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                Log::error('Validation error', ['error' => $decodedError]);

                return [
                    'error' => json_encode($decodedError),
                ];
            } else {
                Log::error('Validation error', ['error' => $errorMessage]);

                return [
                    'error' => $errorMessage,
                ];
            }
        }
    }

    public function processClippedPolygons(array $polygonUuids, $delayed_job_id = null)
    {
        $geojson = GeometryHelper::getPolygonsGeojson($polygonUuids);

        $clippedPolygons = App::make(PythonService::class)->clipPolygons($geojson);
        $uuids = [];
        $delayedJob = null;
        if ($delayed_job_id) {
            $delayedJob = DelayedJobProgress::findOrFail($delayed_job_id);
        }
        if (isset($clippedPolygons['type']) && $clippedPolygons['type'] === 'FeatureCollection' && isset($clippedPolygons['features'])) {
            foreach ($clippedPolygons['features'] as $feature) {
                if (isset($feature['properties']['poly_id'])) {
                    $poly_id = $feature['properties']['poly_id'];
                    $result = CreateVersionPolygonGeometryHelper::createVersionPolygonGeometry($poly_id, json_encode(['geometry' => $feature]));

                    if (isset($result->original['uuid'])) {
                        $uuids[] = $result->original['uuid'];
                    }

                    if (($key = array_search($poly_id, $polygonUuids)) !== false) {
                        unset($polygonUuids[$key]);
                    }
                }
            }
            $polygonUuids = array_values($polygonUuids);
            $newPolygonUuids = array_merge($uuids, $polygonUuids);
        } else {
            throw new \Exception('Error processing polygons', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Only update delayed job if it exists and there are processed polygons
        if ($delayedJob && ! empty($uuids)) {
            $delayedJob->total_content = count($newPolygonUuids);
            $delayedJob->save();
            foreach ($newPolygonUuids as $polygonUuid) {
                App::make(PolygonValidationService::class)->runValidationPolygon($polygonUuid);
                $delayedJob->increment('processed_content');
                $delayedJob->processMessage();
                $delayedJob->save();
            }
        }

        $updatedPolygons = PolygonGeometryHelper::getPolygonsProjection($uuids, ['poly_id', 'poly_name']);

        return $updatedPolygons;
    }

    public function getSitePolygonsWithFiltersAndSorts($sitePolygonsQuery, Request $request)
    {
        if ($request->has('status') && $request->input('status')) {
            $statusValues = explode(',', $request->input('status'));
            $sitePolygonsQuery->whereIn('site_polygon.status', $statusValues);
        }
        if ($request->has('valid') && $request->input('valid')) {
            if ($request->input('valid') === 'not_checked') {
                $sitePolygonsQuery->whereNull('site_polygon.validation_status');
            } else {
                $sitePolygonsQuery->where('site_polygon.validation_status', $request->input('valid'));
            }
        }

        $sortFields = $request->input('sort', []);
        foreach ($sortFields as $field => $direction) {
            if ($field === 'status') {
                $sitePolygonsQuery->orderByRaw('FIELD(site_polygon.status, "draft", "submitted", "needs-more-information", "approved") ' . $direction);
            } elseif ($field === 'poly_name') {
                $sitePolygonsQuery->orderByRaw('site_polygon.poly_name IS NULL, site_polygon.poly_name ' . $direction);
            } else {
                $sitePolygonsQuery->orderBy('site_polygon.' . $field, $direction);
            }
        }

        return $sitePolygonsQuery;
    }

    public function getPolygonData(string $uuid)
    {
        $sitePolygon = SitePolygon::where('poly_id', $uuid)->first();

        if (! $sitePolygon) {
            return ['error' => 'Polygon not found'];
        }

        $project = $sitePolygon->project()->first();

        if (! $project) {
            Log::error("Project not found for site polygon with ID: $sitePolygon->id");
        }

        $site = $sitePolygon->site()->first();

        if (! $site) {
            Log::error("Site not found for site polygon with ID: $sitePolygon->id");
        }

        return [
            'data' => [
                ['key' => 'poly_name', 'title' => 'title', 'value' => $sitePolygon->poly_name ?? null],
                ['key' => 'project_name', 'title' => 'Project', 'value' => $project->name ?? null],
                ['key' => 'site_name', 'title' => 'Site', 'value' => $site?->name ?? null],
                ['key' => 'num_trees', 'title' => 'Number of trees', 'value' => $sitePolygon->num_trees ?? null],
                ['key' => 'plantstart', 'title' => 'Plant Start Date', 'value' => $sitePolygon->plantstart ?? null],
                ['key' => 'status', 'title' => 'Status', 'value' => $sitePolygon->status ?? null],
            ],
        ];
    }

    public function updateSitePolygonValidity(string $polygonUuid): void
    {
        $sitePolygon = SitePolygon::forPolygonGeometry($polygonUuid)->first();
        if (! $sitePolygon) {
            return;
        }

        $allCriteria = CriteriaSite::where('polygon_id', $polygonUuid)->get();

        if ($allCriteria->isEmpty()) {
            $sitePolygon->validation_status = null; // not checked
            $sitePolygon->save();

            return;
        }

        $hasAnyFailing = $allCriteria->contains(function ($c) {
            return $c->valid === 0 || $c->valid === false;
        });

        if (! $hasAnyFailing) {
            $newIsValid = 'passed';
        } else {
            $dynamicExcludedCriteria = $this->getDynamicExcludedCriteria($polygonUuid, $allCriteria);

            $excludedCriteria = $allCriteria->filter(function ($c) use ($dynamicExcludedCriteria) {
                return in_array($c->criteria_id, $dynamicExcludedCriteria);
            });

            $nonExcludedCriteria = $allCriteria->filter(function ($c) use ($dynamicExcludedCriteria) {
                return ! in_array($c->criteria_id, $dynamicExcludedCriteria);
            });

            $hasFailingNonExcluded = $nonExcludedCriteria->contains(function ($c) {
                return $c->valid === 0 || $c->valid === false;
            });

            if ($hasFailingNonExcluded) {
                $newIsValid = 'failed';
            } else {
                $newIsValid = 'partial';
            }
        }

        $sitePolygon->validation_status = $newIsValid;
        $sitePolygon->save();
    }

    private function getDynamicExcludedCriteria(string $polygonUuid, $allCriteria): array
    {
        $baseExcludedCriteria = self::EXCLUDED_VALIDATION_CRITERIA;

        $dataCriteria = $allCriteria->firstWhere('criteria_id', self::DATA_CRITERIA_ID);

        if ($dataCriteria != null && $dataCriteria->valid == 0) {
            $sitePolygon = SitePolygon::forPolygonGeometry($polygonUuid)->first();
            if ($sitePolygon != null) {
                $fieldsToCheck = array_diff(PolygonFields::VALIDATION_FIELDS, ['num_trees']);
                $onlyNumTreesMissing = true;

                foreach ($fieldsToCheck as $field) {
                    $value = $sitePolygon->$field;
                    if ($this->isInvalidField($field, $value)) {
                        $onlyNumTreesMissing = false;

                        break;
                    }
                }

                $numTreesMissing = is_null($sitePolygon->num_trees) || $sitePolygon->num_trees === '' || $sitePolygon->num_trees === 0;

                if ($onlyNumTreesMissing && $numTreesMissing) {
                    $baseExcludedCriteria[] = self::DATA_CRITERIA_ID;
                }
            }
        }

        return $baseExcludedCriteria;
    }
}
