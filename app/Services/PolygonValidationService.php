<?php

namespace App\Services;

use App\Models\V2\PolygonGeometry;
use App\Models\V2\Sites\SitePolygon;
use App\Validators\Extensions\Polygons\EstimatedArea;
use App\Validators\Extensions\Polygons\FeatureBounds;
use App\Validators\Extensions\Polygons\GeometryType;
use App\Validators\Extensions\Polygons\NotOverlapping;
use App\Validators\Extensions\Polygons\PolygonSize;
use App\Validators\Extensions\Polygons\SelfIntersection;
use App\Validators\Extensions\Polygons\Spikes;
use App\Validators\Extensions\Polygons\WithinCountry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PolygonValidationService
{
    public function validateOverlapping(Request $request)
    {
        $uuid = $request->input('uuid');

        return $this->handlePolygonValidation(
            $uuid,
            NotOverlapping::getIntersectionData($uuid),
            PolygonService::OVERLAPPING_CRITERIA_ID
        );
    }

    public function checkSelfIntersection(Request $request)
    {
        $uuid = $request->query('uuid');
        $geometry = PolygonGeometry::where('uuid', $uuid)->first();

        if (! $geometry) {
            return ['error' => 'Geometry not found', 'status' => 404];
        }

        $isSimple = SelfIntersection::uuidValid($uuid);
        $message = $isSimple ? 'The geometry is valid' : 'The geometry has self-intersections';
        $insertionSuccess = App::make(PolygonService::class)
            ->createCriteriaSite($uuid, PolygonService::SELF_CRITERIA_ID, $isSimple);

        return [
            'selfintersects' => $message,
            'geometry_id' => $geometry->id,
            'insertion_success' => $insertionSuccess,
            'valid' => $isSimple,
            'status' => 200,
        ];
    }

    public function validateCoordinateSystem(Request $request)
    {
        $uuid = $request->input('uuid');

        return $this->handlePolygonValidation(
            $uuid,
            ['valid' => FeatureBounds::uuidValid($uuid)],
            PolygonService::COORDINATE_SYSTEM_CRITERIA_ID
        );
    }

    public function validatePolygonSize(Request $request)
    {
        $uuid = $request->query('uuid');
        $geometry = PolygonGeometry::isUuid($uuid)->first();

        if (! $geometry) {
            return ['error' => 'Geometry not found', 'status' => 404];
        }

        $areaSqMeters = PolygonSize::calculateSqMeters($geometry->db_geometry);
        $valid = $areaSqMeters <= PolygonSize::SIZE_LIMIT;
        $insertionSuccess = App::make(PolygonService::class)
            ->createCriteriaSite($uuid, PolygonService::SIZE_CRITERIA_ID, $valid);

        return [
            'area_hectares' => $areaSqMeters / 10000, // Convert to hectares
            'area_sqmeters' => $areaSqMeters,
            'geometry_id' => $geometry->id,
            'insertion_success' => $insertionSuccess,
            'valid' => $valid,
            'status' => 200,
        ];
    }

    public function checkWithinCountry(Request $request)
    {
        $polygonUuid = $request->input('uuid');

        return $this->handlePolygonValidation(
            $polygonUuid,
            WithinCountry::getIntersectionData($polygonUuid),
            PolygonService::WITHIN_COUNTRY_CRITERIA_ID
        );
    }

    public function checkBoundarySegments(Request $request)
    {
        $uuid = $request->query('uuid');
        $geometry = PolygonGeometry::isUuid($uuid)->first();

        if (! $geometry) {
            return ['error' => 'Geometry not found', 'status' => 404];
        }
        $spikes = Spikes::detectSpikes($geometry->geo_json);
        $valid = count($spikes) === 0;
        $insertionSuccess = App::make(PolygonService::class)
            ->createCriteriaSite($uuid, PolygonService::SPIKE_CRITERIA_ID, $valid);

        return [
            'spikes' => $spikes,
            'geometry_id' => $uuid,
            'insertion_success' => $insertionSuccess,
            'valid' => $valid,
            'status' => 200,
        ];
    }

    public function getGeometryType(Request $request)
    {
        $uuid = $request->input('uuid');

        $geometryType = PolygonGeometry::getGeometryType($uuid);
        if ($geometryType) {
            $valid = $geometryType === GeometryType::VALID_TYPE_MULTIPOLYGON || $geometryType === GeometryType::VALID_TYPE_POLYGON;
            $insertionSuccess = App::make(PolygonService::class)
                ->createCriteriaSite($uuid, PolygonService::GEOMETRY_TYPE_CRITERIA_ID, $valid);

            return [
                'uuid' => $uuid,
                'geometry_type' => $geometryType,
                'valid' => $valid,
                'insertion_success' => $insertionSuccess,
                'status' => 200,
            ];
        } else {
            return ['error' => 'Geometry not found for the given UUID', 'status' => 404];
        }
    }

    public function validateEstimatedArea(Request $request)
    {
        $uuid = $request->input('uuid');

        return $this->handlePolygonValidation(
            $uuid,
            EstimatedArea::getAreaData($uuid),
            PolygonService::ESTIMATED_AREA_CRITERIA_ID
        );
    }

    public function validateDataInDB(Request $request)
    {
        $polygonUuid = $request->input('uuid');
        $fieldsToValidate = ['poly_name', 'plantstart', 'plantend', 'practice', 'target_sys', 'distr', 'num_trees'];

        $sitePolygon = SitePolygon::forPolygonGeometry($polygonUuid)->first();
        if (! $sitePolygon) {
            return ['valid' => false, 'message' => 'No site polygon found with the specified UUID.', 'status' => 404];
        }

        $validationErrors = [];
        $polygonService = App::make(PolygonService::class);
        foreach ($fieldsToValidate as $field) {
            $value = $sitePolygon->$field;
            if ($polygonService->isInvalidField($field, $value)) {
                $validationErrors[] = [
                    'field' => $field,
                    'error' => $value,
                    'exists' => ! is_null($value) && $value !== '',
                ];
            }
        }

        $isValid = empty($validationErrors);
        $responseData = ['valid' => $isValid];
        if (! $isValid) {
            $responseData['message'] = 'Some attributes of the site polygon are invalid.';
        }

        $polygonService->createCriteriaSite($polygonUuid, PolygonService::DATA_CRITERIA_ID, $isValid, $validationErrors);

        return array_merge($responseData, ['status' => 200]);
    }

    protected function handlePolygonValidation($polygonUuid, $response, $criteriaId)
    {
        if (isset($response['error']) && $response['error'] != null) {
            $status = $response['status'];
            unset($response['valid']);
            unset($response['status']);

            return $response + ['status' => $status];
        }
        $extraInfo = $response['extra_info'] ?? null;
        $response['insertion_success'] = App::make(PolygonService::class)
            ->createCriteriaSite($polygonUuid, $criteriaId, $response['valid'], $extraInfo);

        return $response;
    }

    public function runValidationPolygon(string $uuid)
    {
        $request = new Request(['uuid' => $uuid]);

        $this->validateOverlapping($request);
        $this->checkSelfIntersection($request);
        $this->validateCoordinateSystem($request);
        $this->validatePolygonSize($request);
        $this->checkWithinCountry($request);
        $this->checkBoundarySegments($request);
        $this->getGeometryType($request);
        $this->validateEstimatedArea($request);
        $this->validateDataInDB($request);
    }
   
    /**
     * Consolidated method to run all validations with minimal DB queries
     * 
     * @param string $uuid The polygon UUID to validate
     * @return array Results of all validations
     */
    public function runConsolidatedValidation(string $uuid): array
    {
        $results = [];
        $polygonService = App::make(PolygonService::class);
        
        try {
            // 1. Fetch the polygon and site data once
            $geometry = PolygonGeometry::where('uuid', $uuid)->first();
            if (!$geometry) {
                Log::warning("Polygon not found: $uuid");
                return ['error' => 'Polygon not found', 'uuid' => $uuid];
            }
            
            $sitePolygon = SitePolygon::forPolygonGeometry($uuid)->first();
            if (!$sitePolygon) {
                Log::warning("Site polygon not found for: $uuid");
                return ['error' => 'Site polygon not found', 'uuid' => $uuid];
            }
            
            $site = $sitePolygon->site()->first();
            $project = $sitePolygon->project()->first();
            
            // Preload geometry data that will be used by multiple validations
            $geometryData = [
                'uuid' => $uuid,
                'geometry' => $geometry,
                'site_polygon' => $sitePolygon,
                'site' => $site,
                'project' => $project,
                'geo_json' => $geometry->geo_json,
                'db_geometry' => $geometry->db_geometry
            ];
            
            // 2. Run all validations in a single DB transaction
            DB::transaction(function() use ($uuid, $geometryData, $polygonService, &$results) {
                // Validation 1: Overlapping
                $overlapResult = $this->validateOverlappingConsolidated($geometryData);
                $results['overlapping'] = $overlapResult;
                $polygonService->createCriteriaSite(
                    $uuid, 
                    PolygonService::OVERLAPPING_CRITERIA_ID, 
                    $overlapResult['valid'],
                    $overlapResult['extra_info'] ?? null
                );
                
                // Validation 2: Self-intersection
                $selfIntersectResult = $this->checkSelfIntersectionConsolidated($geometryData);
                $results['self_intersection'] = $selfIntersectResult;
                $polygonService->createCriteriaSite(
                    $uuid, 
                    PolygonService::SELF_CRITERIA_ID, 
                    $selfIntersectResult['valid']
                );
                
                // Validation 3: Coordinate System
                $coordSystemResult = $this->validateCoordinateSystemConsolidated($geometryData);
                $results['coordinate_system'] = $coordSystemResult;
                $polygonService->createCriteriaSite(
                    $uuid, 
                    PolygonService::COORDINATE_SYSTEM_CRITERIA_ID, 
                    $coordSystemResult['valid']
                );
                
                // Validation 4: Polygon Size
                $sizeResult = $this->validatePolygonSizeConsolidated($geometryData);
                $results['size'] = $sizeResult;
                $polygonService->createCriteriaSite(
                    $uuid, 
                    PolygonService::SIZE_CRITERIA_ID, 
                    $sizeResult['valid']
                );
                
                // Validation 5: Within Country
                $countryResult = $this->checkWithinCountryConsolidated($geometryData);
                $results['within_country'] = $countryResult;
                $polygonService->createCriteriaSite(
                    $uuid, 
                    PolygonService::WITHIN_COUNTRY_CRITERIA_ID, 
                    $countryResult['valid'],
                    $countryResult['extra_info'] ?? null
                );
                
                // Validation 6: Boundary Segments (Spikes)
                $boundaryResult = $this->checkBoundarySegmentsConsolidated($geometryData);
                $results['boundary_segments'] = $boundaryResult;
                $polygonService->createCriteriaSite(
                    $uuid, 
                    PolygonService::SPIKE_CRITERIA_ID, 
                    $boundaryResult['valid']
                );
                
                // Validation 7: Geometry Type
                $geometryTypeResult = $this->getGeometryTypeConsolidated($geometryData);
                $results['geometry_type'] = $geometryTypeResult;
                $polygonService->createCriteriaSite(
                    $uuid, 
                    PolygonService::GEOMETRY_TYPE_CRITERIA_ID, 
                    $geometryTypeResult['valid']
                );
                
                // Validation 8: Estimated Area
                $areaResult = $this->validateEstimatedAreaConsolidated($geometryData);
                $results['estimated_area'] = $areaResult;
                $polygonService->createCriteriaSite(
                    $uuid, 
                    PolygonService::ESTIMATED_AREA_CRITERIA_ID, 
                    $areaResult['valid'],
                    $areaResult['extra_info'] ?? null
                );
                
                // Validation 9: Data in DB
                $dataResult = $this->validateDataInDBConsolidated($geometryData);
                $results['data'] = $dataResult;
                $polygonService->createCriteriaSite(
                    $uuid, 
                    PolygonService::DATA_CRITERIA_ID, 
                    $dataResult['valid'],
                    $dataResult['validation_errors'] ?? null
                );
            });
            
            $results['success'] = true;
            return $results;
            
        } catch (\Exception $e) {
            Log::error("Error in consolidated validation for polygon $uuid: " . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'uuid' => $uuid
            ];
        }
    }
    
    /**
     * Consolidated version of validateOverlapping that uses preloaded geometry data
     */
    protected function validateOverlappingConsolidated(array $geometryData): array
    {
        $uuid = $geometryData['uuid'];
        $sitePolygon = $geometryData['site_polygon'];
        $project = $geometryData['project'];
        
        // Core logic from NotOverlapping::getIntersectionData but optimized
        $relatedPolyIds = $project->sitePolygons()
            ->where('poly_id', '!=', $uuid)
            ->pluck('poly_id');
            
        // Use a more efficient query to find potential intersections using bounding box first
        $bboxFilteredPolyIds = PolygonGeometry::join('site_polygon', 'polygon_geometry.uuid', '=', 'site_polygon.poly_id')
            ->whereIn('polygon_geometry.uuid', $relatedPolyIds)
            ->whereRaw('ST_Intersects(ST_Envelope(polygon_geometry.geom), (SELECT ST_Envelope(geom) FROM polygon_geometry WHERE uuid = ?))', [$uuid])
            ->pluck('polygon_geometry.uuid');
        
        // Now only check exact intersections for polygons that passed the bounding box filter
        $intersects = PolygonGeometry::join('site_polygon', 'polygon_geometry.uuid', '=', 'site_polygon.poly_id')
            ->whereIn('polygon_geometry.uuid', $bboxFilteredPolyIds)
            ->select([
                'polygon_geometry.uuid',
                'site_polygon.poly_name',
                DB::raw('ST_Intersects(polygon_geometry.geom, (SELECT geom FROM polygon_geometry WHERE uuid = ?)) as intersects'),
                DB::raw('ST_Area(ST_Intersection(polygon_geometry.geom, (SELECT geom FROM polygon_geometry WHERE uuid = ?))) as intersection_area'),
                DB::raw('ST_Area(polygon_geometry.geom) as area'),
            ])
            ->addBinding($uuid, 'select')
            ->addBinding($uuid, 'select')
            ->get();

        $mainPolygonArea = $geometryData['geometry']->db_geometry->area;
        $extra_info = $intersects
            ->filter(function ($intersect) {
                return $intersect->intersects && $intersect->intersection_area > 1e-10;
            })
            ->map(function ($intersect) use ($mainPolygonArea) {
                $minArea = min($mainPolygonArea, $intersect->area);
                $percentage = $minArea > 0
                  ? round(($intersect->intersection_area / $minArea) * 100, 2)
                  : 100;

                return [
                    'poly_uuid' => $intersect->uuid,
                    'poly_name' => $intersect->poly_name,
                    'percentage' => $percentage,
                    'intersectSmaller' => ($intersect->area < $mainPolygonArea),
                ];
            })
            ->values()
            ->toArray();

        return [
            'valid' => empty($extra_info),
            'uuid' => $uuid,
            'project_id' => $sitePolygon->project_id,
            'extra_info' => $extra_info,
        ];
    }
    
    /**
     * Consolidated version of checkSelfIntersection that uses preloaded geometry data
     */
    protected function checkSelfIntersectionConsolidated(array $geometryData): array
    {
        $uuid = $geometryData['uuid'];
        $geometry = $geometryData['geometry'];
        
        // Use already loaded geometry instead of querying again
        $isSimple = DB::selectOne(
            'SELECT ST_IsSimple(geom) AS is_simple FROM polygon_geometry WHERE uuid = :uuid',
            ['uuid' => $uuid]
        )->is_simple;
        
        $message = $isSimple ? 'The geometry is valid' : 'The geometry has self-intersections';
        
        return [
            'selfintersects' => $message,
            'geometry_id' => $geometry->id,
            'valid' => $isSimple,
            'status' => 200,
        ];
    }
    
    /**
     * Consolidated version of validateCoordinateSystem that uses preloaded geometry data
     */
    protected function validateCoordinateSystemConsolidated(array $geometryData): array
    {
        $uuid = $geometryData['uuid'];
        $geoJson = $geometryData['geo_json'];
        
        // Direct implementation of FeatureBounds logic without going to the DB again
        $valid = FeatureBounds::geoJsonValid($geoJson);
        
        return [
            'valid' => $valid,
            'uuid' => $uuid,
            'status' => 200,
        ];
    }
    
    /**
     * Consolidated version of validatePolygonSize that uses preloaded geometry data
     */
    protected function validatePolygonSizeConsolidated(array $geometryData): array
    {
        $uuid = $geometryData['uuid'];
        $geometry = $geometryData['geometry'];
        $dbGeometry = $geometryData['db_geometry'];
        
        // Use already fetched geometry data
        $areaSqMeters = PolygonSize::calculateSqMeters($dbGeometry);
        $valid = $areaSqMeters <= PolygonSize::SIZE_LIMIT;
        
        return [
            'area_hectares' => $areaSqMeters / 10000, // Convert to hectares
            'area_sqmeters' => $areaSqMeters,
            'geometry_id' => $geometry->id,
            'valid' => $valid,
            'status' => 200,
        ];
    }
    
    /**
     * Consolidated version of checkWithinCountry that uses preloaded geometry data
     */
    protected function checkWithinCountryConsolidated(array $geometryData): array
    {
        $uuid = $geometryData['uuid'];
        $geometry = $geometryData['geometry'];
        $sitePolygon = $geometryData['site_polygon'];
        $project = $geometryData['project'];
        
        $countryIso = $project->country;
        if ($countryIso == null) {
            return ['valid' => false, 'status' => 404, 'error' => 'Country ISO not found for the specified project'];
        }
        
        $intersectionData = DB::table('world_countries_generalized')
            ->where('iso', $countryIso)
            ->selectRaw(
                'world_countries_generalized.country AS country, 
                ST_Area(
                    ST_Intersection(
                        world_countries_generalized.geometry, 
                        (SELECT geom FROM polygon_geometry WHERE uuid = ?)
                    )
                ) AS area',
                [$uuid]
            )
            ->first();
        
        if (!$intersectionData) {
            return ['valid' => false, 'status' => 404, 'error' => 'Country data not found'];
        }

        $totalArea = $geometryData['db_geometry']->area;
        $insidePercentage = $intersectionData->area / $totalArea * 100;

        return [
            'valid' => $insidePercentage >= WithinCountry::THRESHOLD_PERCENTAGE,
            'geometry_id' => $geometry->id,
            'inside_percentage' => $insidePercentage,
            'country_name' => $intersectionData->country,
            'extra_info' => [
              'country_name' => $intersectionData->country,
            ],
        ];
    }
    
    /**
     * Consolidated version of checkBoundarySegments that uses preloaded geometry data
     */
    protected function checkBoundarySegmentsConsolidated(array $geometryData): array
    {
        $uuid = $geometryData['uuid'];
        $geoJson = $geometryData['geo_json'];
        
        // Use the Spikes detector with already loaded GeoJSON
        $spikes = Spikes::detectSpikes($geoJson);
        $valid = count($spikes) === 0;
        
        return [
            'spikes' => $spikes,
            'geometry_id' => $uuid,
            'valid' => $valid,
            'status' => 200,
        ];
    }
    
    /**
     * Consolidated version of getGeometryType that uses preloaded geometry data
     */
    protected function getGeometryTypeConsolidated(array $geometryData): array
    {
        $uuid = $geometryData['uuid'];
        
        // Get geometry type from DB without re-fetching the polygon
        $geometryType = DB::selectOne(
            'SELECT ST_GeometryType(geom) AS geometry_type FROM polygon_geometry WHERE uuid = ?',
            [$uuid]
        )->geometry_type;
        
        $valid = $geometryType === GeometryType::VALID_TYPE_MULTIPOLYGON || 
                 $geometryType === GeometryType::VALID_TYPE_POLYGON;
        
        return [
            'uuid' => $uuid,
            'geometry_type' => $geometryType,
            'valid' => $valid,
            'status' => 200,
        ];
    }
    
    /**
     * Consolidated version of validateEstimatedArea that uses preloaded geometry data
     */
    protected function validateEstimatedAreaConsolidated(array $geometryData): array
    {
        $uuid = $geometryData['uuid'];
        $sitePolygon = $geometryData['site_polygon'];
        $site = $geometryData['site'];
        $project = $geometryData['project'];
        
        // Generate site data
        $sumEstAreaSite = $site->sitePolygons()->sum('calc_area');
        $lowerBoundSite = EstimatedArea::LOWER_BOUND_MULTIPLIER * $site->hectares_to_restore_goal;
        $upperBoundSite = EstimatedArea::UPPER_BOUND_MULTIPLIER * $site->hectares_to_restore_goal;
        $validSite = $sumEstAreaSite >= $lowerBoundSite && $sumEstAreaSite <= $upperBoundSite;
        
        if (!$site->hectares_to_restore_goal) {
            $siteData = [
                'valid' => false,
                'total_area_site' => $site->hectares_to_restore_goal,
                'sum_area_site' => null,
                'percentage_site' => null,
            ];
        } else {
            $percentageSite = $site->hectares_to_restore_goal > 0
                ? ($sumEstAreaSite / $site->hectares_to_restore_goal) * 100
                : 0;
            
            $sumEstAreaSite = round($sumEstAreaSite);
            $percentageSite = round($percentageSite);
            
            $siteData = [
                'valid' => $validSite,
                'sum_area_site' => $sumEstAreaSite,
                'total_area_site' => $site->hectares_to_restore_goal,
                'percentage_site' => $percentageSite,
            ];
        }
        
        // Generate project data
        $sumEstAreaProject = $project->sitePolygons()->sum('calc_area');
        $lowerBoundProject = EstimatedArea::LOWER_BOUND_MULTIPLIER * $project->total_hectares_restored_goal;
        $upperBoundProject = EstimatedArea::UPPER_BOUND_MULTIPLIER * $project->total_hectares_restored_goal;
        $validProject = $sumEstAreaProject >= $lowerBoundProject && $sumEstAreaProject <= $upperBoundProject;
        
        if (empty($project->total_hectares_restored_goal) || !$project->total_hectares_restored_goal) {
            $projectData = [
                'valid' => false,
                'total_area_project' => $project->total_hectares_restored_goal,
                'sum_area_project' => null,
                'percentage_project' => null,
            ];
        } else {
            $percentageProject = $project->total_hectares_restored_goal > 0
                ? ($sumEstAreaProject / $project->total_hectares_restored_goal) * 100
                : 0;
            
            $sumEstAreaProject = round($sumEstAreaProject);
            $percentageProject = round($percentageProject);
            
            $projectData = [
                'valid' => $validProject,
                'sum_area_project' => $sumEstAreaProject,
                'total_area_project' => $project->total_hectares_restored_goal,
                'percentage_project' => $percentageProject,
            ];
        }
        
        $valid = $siteData['valid'] || $projectData['valid'];
        
        return [
            'valid' => $valid,
            'total_area_site' => $siteData['total_area_site'] ?? null,
            'total_area_project' => $projectData['total_area_project'] ?? null,
            'extra_info' => [
                'sum_area_site' => $siteData['sum_area_site'] ?? null,
                'sum_area_project' => $projectData['sum_area_project'] ?? null,
                'percentage_site' => $siteData['percentage_site'] ?? null,
                'percentage_project' => $projectData['percentage_project'] ?? null,
                'total_area_site' => $siteData['total_area_site'] ?? null,
                'total_area_project' => $projectData['total_area_project'] ?? null,
            ],
        ];
    }
    
    /**
     * Consolidated version of validateDataInDB that uses preloaded geometry data
     */
    protected function validateDataInDBConsolidated(array $geometryData): array
    {
        $uuid = $geometryData['uuid'];
        $sitePolygon = $geometryData['site_polygon'];
        
        $fieldsToValidate = ['poly_name', 'plantstart', 'plantend', 'practice', 'target_sys', 'distr', 'num_trees'];
        
        $validationErrors = [];
        $polygonService = App::make(PolygonService::class);
        
        foreach ($fieldsToValidate as $field) {
            $value = $sitePolygon->$field;
            if ($polygonService->isInvalidField($field, $value)) {
                $validationErrors[] = [
                    'field' => $field,
                    'error' => $value,
                    'exists' => !is_null($value) && $value !== '',
                ];
            }
        }
        
        $isValid = empty($validationErrors);
        $responseData = ['valid' => $isValid];
        
        if (!$isValid) {
            $responseData['message'] = 'Some attributes of the site polygon are invalid.';
        }
        
        $responseData['validation_errors'] = $validationErrors;
        $responseData['status'] = 200;
        
        return $responseData;
    }
    
    /**
     * Run all validations for a polygon using the consolidated method
     */
    public function runValidationPolygonConsolidated(string $uuid)
    {
        $this->runConsolidatedValidation($uuid);
    }
}
