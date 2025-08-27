<?php

namespace App\Services;

use App\Helpers\GeometryHelper;
use App\Models\V2\MonitoredData\IndicatorHectares;
use App\Models\V2\MonitoredData\IndicatorTreeCoverLoss;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IndicatorUpdateService
{
    protected $slugMappings = [
        'restorationByStrategy' => [
            'indicator' => 'restoration_practice',
            'model' => IndicatorHectares::class,
            'table_name' => 'indicator_output_hectares',
        ],
        'restorationByLandUse' => [
            'indicator' => 'target_system',
            'model' => IndicatorHectares::class,
            'table_name' => 'indicator_output_hectares',
        ],
    ];

    /**
     * Update indicator values for a specific polygon
     *
     * @param string $polygonUuid The UUID of the polygon
     * @return array Results of the update operation
     */
    public function updateIndicatorsForPolygon(string $polygonUuid)
    {
        $results = [];

        $polygonGeometry = $this->getGeometry($polygonUuid);
        if (! $polygonGeometry) {
            Log::warning("Could not retrieve geometry for polygon UUID: $polygonUuid");

            return [
                'error' => 'Invalid polygon geometry',
                'status' => 'error',
            ];
        }

        foreach ($this->slugMappings as $slug => $slugMapping) {
            $results[$slug] = [
                'status' => 'skipped',
                'message' => 'No processing needed',
            ];

            try {
                $existingRecord = $this->getExistingRecord($slug, $polygonGeometry['site_polygon_id']);
                if ($existingRecord) {
                    $results[$slug] = [
                        'status' => 'skipped',
                        'message' => 'Record already exists for current year',
                    ];

                    continue;
                }

                $indicatorModel = $this->getOrCreateIndicatorRecord($slug, $polygonGeometry['site_polygon_id']);

                if (! $indicatorModel) {
                    $results[$slug] = [
                        'status' => 'error',
                        'message' => 'Failed to create indicator record',
                    ];

                    continue;
                }

                if (str_contains($slug, 'restorationBy')) {
                    $result = $this->processRestorationIndicator($slug, $polygonUuid, $polygonGeometry['site_polygon_id']);
                    $results[$slug] = $result;

                    continue;
                }
            } catch (\Exception $e) {
                Log::error('Error updating indicator for polygon: ' . $polygonUuid . ' - ' . $e->getMessage());
                $results[$slug] = [
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Update indicator values for a batch of polygons with optimized connection management
     *
     * @param array $polygonUuids Array of polygon UUIDs to process
     * @return array Results of the batch update operation
     */
    public function updateIndicatorsForPolygonBatch(array $polygonUuids): array
    {
        $batchResults = [];
        
        try {
            // Pre-load all geometries in a single query to reduce DB calls
            $geometries = $this->preloadGeometries($polygonUuids);
            
            foreach ($polygonUuids as $polygonUuid) {
                if (!isset($geometries[$polygonUuid])) {
                    $batchResults[$polygonUuid] = [
                        'error' => [
                            'status' => 'error',
                            'message' => 'Geometry not found for polygon: ' . $polygonUuid,
                        ],
                    ];
                    continue;
                }

                $polygonGeometry = $geometries[$polygonUuid];
                $results = [];

                foreach ($this->slugMappings as $slug => $slugMapping) {
                    $results[$slug] = [
                        'status' => 'skipped',
                        'message' => 'No processing needed',
                    ];

                    try {
                        $existingRecord = $this->getExistingRecord($slug, $polygonGeometry['site_polygon_id']);
                        if ($existingRecord) {
                            $results[$slug] = [
                                'status' => 'skipped',
                                'message' => 'Record already exists for current year',
                            ];
                            continue;
                        }

                        $indicatorModel = $this->getOrCreateIndicatorRecord($slug, $polygonGeometry['site_polygon_id']);

                        if (!$indicatorModel) {
                            $results[$slug] = [
                                'status' => 'error',
                                'message' => 'Failed to create indicator record',
                            ];
                            continue;
                        }

                        if (str_contains($slug, 'restorationBy')) {
                            DB::disconnect();
                            
                            try {
                                $result = $this->processRestorationIndicatorOptimized($slug, $polygonUuid, $polygonGeometry['site_polygon_id']);
                                $results[$slug] = $result;
                            } finally {
                                DB::reconnect();
                            }
                        }

                    } catch (\Exception $e) {
                        Log::error('Error updating indicator for polygon: ' . $polygonUuid . ' - ' . $e->getMessage());
                        $results[$slug] = [
                            'status' => 'error',
                            'message' => $e->getMessage(),
                        ];
                    }
                }

                $batchResults[$polygonUuid] = $results;
            }

        } catch (\Exception $e) {
            Log::error('Error in batch processing: ' . $e->getMessage());
            
            foreach ($polygonUuids as $polygonUuid) {
                if (!isset($batchResults[$polygonUuid])) {
                    $batchResults[$polygonUuid] = $this->updateIndicatorsForPolygon($polygonUuid);
                }
            }
        }

        return $batchResults;
    }

    protected function preloadGeometries(array $polygonUuids): array
    {
        $geometries = [];
        
        try {
            $polygonData = DB::table('polygon_geometry as pg')
                ->join('site_polygon as sp', 'pg.uuid', '=', 'sp.poly_id')
                ->whereIn('pg.uuid', $polygonUuids)
                ->select('pg.uuid', 'sp.uuid as site_polygon_id', 'pg.geom')
                ->get();

            foreach ($polygonData as $data) {
                $geometries[$data->uuid] = [
                    'site_polygon_id' => $data->site_polygon_id,
                    'geo' => json_decode(DB::selectOne('SELECT ST_AsGeoJSON(?) as geojson', [$data->geom])->geojson, true),
                ];
            }

        } catch (\Exception $e) {
            Log::error('Error preloading geometries: ' . $e->getMessage());
        }

        return $geometries;
    }

    protected function processRestorationIndicatorOptimized($slug, $polygonUuid, $sitePolygonId)
    {
        try {
            $geojson = GeometryHelper::getPolygonGeojson($polygonUuid);
            
            $indicatorResponse = App::make(PythonService::class)->IndicatorPolygon(
                $geojson,
                $this->slugMappings[$slug]['indicator'],
                getenv('GFW_SECRET_KEY')
            );

            if (empty($indicatorResponse) || !isset($indicatorResponse['area'][$this->slugMappings[$slug]['indicator']])) {
                return [
                    'status' => 'error',
                    'message' => 'Invalid response from Python service',
                ];
            }

            if ($slug == 'restorationByEcoRegion') {
                $value = json_encode($indicatorResponse['area'][$this->slugMappings[$slug]['indicator']]);
            } else {
                $value = $this->formatKeysValues($indicatorResponse['area'][$this->slugMappings[$slug]['indicator']]);
            }

            $data = ['value' => $value];

            $this->slugMappings[$slug]['model']::where('site_polygon_id', $sitePolygonId)
                ->where('indicator_slug', $slug)
                ->where('year_of_analysis', Carbon::now()->year)
                ->update($data);

            return [
                'status' => 'success',
                'message' => 'Restoration indicator updated successfully',
            ];

        } catch (\Exception $e) {
            Log::error('Error in processRestorationIndicatorOptimized: ' . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Failed to process restoration indicator: ' . $e->getMessage(),
            ];
        }
    }

    protected function getExistingRecord($slug, $sitePolygonId)
    {
        $modelClass = $this->slugMappings[$slug]['model'];
        $yearOfAnalysis = Carbon::now()->year;

        return $modelClass::where('site_polygon_id', $sitePolygonId)
            ->where('indicator_slug', $slug)
            ->where('year_of_analysis', $yearOfAnalysis)
            ->first();
    }

    /**
     * Get or create an indicator record in the database
     */
    protected function getOrCreateIndicatorRecord($slug, $sitePolygonId)
    {
        $modelClass = $this->slugMappings[$slug]['model'];
        $tableName = $this->slugMappings[$slug]['table_name'];
        $yearOfAnalysis = Carbon::now()->year;

        $record = DB::table($tableName)
            ->where('site_polygon_id', $sitePolygonId)
            ->where('indicator_slug', $slug)
            ->where('year_of_analysis', $yearOfAnalysis)
            ->first();

        if ($record) {
            return $modelClass::find($record->id);
        }

        return $modelClass::create([
            'site_polygon_id' => $sitePolygonId,
            'indicator_slug' => $slug,
            'year_of_analysis' => $yearOfAnalysis,
            'value' => json_encode([]), // Empty value initially
        ]);
    }

    /**
     * Process restoration indicator
     */
    protected function processRestorationIndicator($slug, $polygonUuid, $sitePolygonId)
    {
        $geojson = GeometryHelper::getPolygonGeojson($polygonUuid);
        $indicatorResponse = App::make(PythonService::class)->IndicatorPolygon(
            $geojson,
            $this->slugMappings[$slug]['indicator'],
            getenv('GFW_SECRET_KEY')
        );

        if (empty($indicatorResponse) || ! isset($indicatorResponse['area'][$this->slugMappings[$slug]['indicator']])) {
            return [
                'status' => 'error',
                'message' => 'Invalid response from Python service',
            ];
        }

        if ($slug == 'restorationByEcoRegion') {
            $value = json_encode($indicatorResponse['area'][$this->slugMappings[$slug]['indicator']]);
        } else {
            $value = $this->formatKeysValues($indicatorResponse['area'][$this->slugMappings[$slug]['indicator']]);
        }

        $data = ['value' => $value];

        $this->slugMappings[$slug]['model']::where('site_polygon_id', $sitePolygonId)
            ->where('indicator_slug', $slug)
            ->where('year_of_analysis', Carbon::now()->year)
            ->update($data);

        return [
            'status' => 'success',
            'message' => 'Restoration indicator updated successfully',
        ];
    }



    /**
     * Get geometry data for a polygon
     */
    public function getGeometry($polygonUuid)
    {
        $geojson = GeometryHelper::getMonitoredPolygonsGeojson($polygonUuid);
        $geoJsonObject = json_decode($geojson['geometry']->geojsonGeometry, true);

        return [
            'geo' => [
                'type' => 'Polygon',
                'coordinates' => $geoJsonObject['coordinates'],
            ],
            'site_polygon_id' => $geojson['site_polygon_id'],
        ];
    }

    /**
     * Send API request to indicator service
     */
    public function sendApiRequestIndicator($secret_key, $query_url, $query_sql, $geometry)
    {
        return Http::withHeaders([
            'content-type' => 'application/json',
            'x-api-key' => $secret_key,
        ])->post('https://data-api.globalforestwatch.org' . $query_url, [
            'sql' => $query_sql,
            'geometry' => $geometry,
        ]);
    }

    /**
     * Process tree cover loss value
     */
    public function processTreeCoverLossValue($data, $indicator)
    {
        $processedTreeCoverLossValue = [];
        foreach ($data as $i) {
            $processedTreeCoverLossValue[$i[$indicator . '__year']] = $i['area__ha'];
        }

        return $processedTreeCoverLossValue;
    }

    /**
     * Generate tree cover loss data
     */
    public function generateTreeCoverLossData($processedTreeCoverLossValue)
    {
        $yearsOfAnalysis = [2015, 2016, 2017, 2018, 2019, 2020, 2021, 2022, 2023, 2024];
        $responseData = [];
        foreach ($yearsOfAnalysis as $year) {
            if (isset($processedTreeCoverLossValue[$year])) {
                $responseData[$year] = $processedTreeCoverLossValue[$year];
            } else {
                $responseData[$year] = 0.0;
            }
        }

        return [
            'value' => json_encode($responseData),
        ];
    }

    /**
     * Format keys and values
     */
    public function formatKeysValues($data)
    {
        $formattedData = [];
        foreach ($data as $key => $value) {
            $formattedKey = strtolower(str_replace(' ', '-', $key));
            $formattedData[$formattedKey] = $value;
        }

        return json_encode($formattedData);
    }
}
