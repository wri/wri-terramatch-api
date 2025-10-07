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
use Symfony\Component\Process\Process;

class RunIndicatorAnalysisService
{
    /**
     * Maximum number of retry attempts for API calls
     */
    protected const MAX_RETRIES = 3;

    /**
     * Default batch size for processing polygons
     * This prevents "too many attempts" errors when processing large datasets
     */
    protected const DEFAULT_BATCH_SIZE = 50;

    /**
     * Maximum batch size to prevent memory issues
     */
    protected const MAX_BATCH_SIZE = 100;

    /**
     * Run the indicator analysis for the given polygons and slug
     *
     * @param array $request
     * @param string $slug
     * @return \Illuminate\Http\JsonResponse
     */
    public function run(array $request, string $slug)
    {
        try {
            $slugMappings = [
                'treeCoverLoss' => [
                    'sql' => 'SELECT umd_tree_cover_loss__year, SUM(area__ha) FROM results GROUP BY umd_tree_cover_loss__year',
                    'query_url' => '/dataset/umd_tree_cover_loss/latest/query',
                    'indicator' => 'umd_tree_cover_loss',
                    'model' => IndicatorTreeCoverLoss::class,
                    'table_name' => 'indicator_output_tree_cover_loss',
                ],
                'treeCoverLossFires' => [
                    'sql' => 'SELECT umd_tree_cover_loss_from_fires__year, SUM(area__ha) FROM results GROUP BY umd_tree_cover_loss_from_fires__year',
                    'query_url' => '/dataset/umd_tree_cover_loss_from_fires/latest/query',
                    'indicator' => 'umd_tree_cover_loss_from_fires',
                    'model' => IndicatorTreeCoverLoss::class,
                    'table_name' => 'indicator_output_tree_cover_loss',
                ],
                'restorationByEcoRegion' => [
                    'indicator' => 'wwf_terrestrial_ecoregions',
                    'model' => IndicatorHectares::class,
                    'table_name' => 'indicator_output_hectares',
                ],
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

            if (! isset($slugMappings[$slug])) {
                return response()->json(['message' => 'Slug Not Found'], 400);
            }

            $totalPolygons = count($request['uuids']);
            $updateExisting = isset($request['update_existing']) ? $request['update_existing'] : false;

            Log::info("Starting analysis for slug: $slug with $totalPolygons polygons", [
                'update_existing' => $updateExisting,
            ]);

            $needsBatching = $this->shouldUseBatching($slug, $totalPolygons);
            $batchSize = $needsBatching ? self::DEFAULT_BATCH_SIZE : $totalPolygons;

            if ($needsBatching) {
                Log::info("Using batched processing for slug: $slug with batch size: $batchSize");

                return $this->processBatchedAnalysis($request, $slug, $slugMappings, $batchSize);
            } else {
                return $this->processSingleBatch($request, $slug, $slugMappings);
            }
        } catch (\Exception $e) {
            Log::error('Global error in indicator analysis', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'An error occurred during the analysis',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Determine if batching should be used based on slug and polygon count
     *
     * @param string $slug
     * @param int $totalPolygons
     * @return bool
     */
    protected function shouldUseBatching(string $slug, int $totalPolygons): bool
    {
        $treeCoverSlugs = ['treeCoverLoss', 'treeCoverLossFires', 'restorationByEcoRegion'];

        return in_array($slug, $treeCoverSlugs) && $totalPolygons > self::DEFAULT_BATCH_SIZE;
    }

    /**
     * Validate and adjust batch size to prevent memory issues
     *
     * @param int $batchSize
     * @return int
     */
    protected function validateBatchSize(int $batchSize): int
    {
        if ($batchSize <= 0) {
            return self::DEFAULT_BATCH_SIZE;
        }

        if ($batchSize > self::MAX_BATCH_SIZE) {
            Log::warning("Batch size $batchSize exceeds maximum, adjusting to " . self::MAX_BATCH_SIZE);

            return self::MAX_BATCH_SIZE;
        }

        return $batchSize;
    }

    /**
     * Process analysis in batches for large datasets
     *
     * @param array $request
     * @param string $slug
     * @param array $slugMappings
     * @param int $batchSize
     * @return \Illuminate\Http\JsonResponse
     */
    protected function processBatchedAnalysis(array $request, string $slug, array $slugMappings, int $batchSize)
    {
        $totalPolygons = count($request['uuids']);
        $updateExisting = isset($request['update_existing']) ? $request['update_existing'] : false;
        $force = isset($request['force']) ? $request['force'] : false;

        $batchSize = $this->validateBatchSize($batchSize);

        $totalProcessed = 0;
        $totalSkipped = 0;
        $totalErrors = 0;

        $batches = array_chunk($request['uuids'], $batchSize);
        $totalBatches = count($batches);

        Log::info("Processing $totalBatches batches for slug: $slug", [
            'total_polygons' => $totalPolygons,
            'batch_size' => $batchSize,
        ]);

        foreach ($batches as $batchIndex => $batchUuids) {
            $batchNumber = $batchIndex + 1;
            Log::info("Processing batch $batchNumber of $totalBatches for slug: $slug", [
                'batch_size' => count($batchUuids),
            ]);

            $batchRequest = [
                'uuids' => $batchUuids,
                'update_existing' => $updateExisting,
                'force' => $force,
            ];

            try {
                $batchResult = $this->processSingleBatch($batchRequest, $slug, $slugMappings);

                if (isset($batchResult->getData(true)['stats'])) {
                    $stats = $batchResult->getData(true)['stats'];
                    $totalProcessed += $stats['processed'] ?? 0;
                    $totalSkipped += $stats['skipped'] ?? 0;
                    $totalErrors += $stats['errors'] ?? 0;
                }

                Log::info("Batch $batchNumber completed successfully", [
                    'batch_processed' => $stats['processed'] ?? 0,
                    'batch_skipped' => $stats['skipped'] ?? 0,
                    'batch_errors' => $stats['errors'] ?? 0,
                ]);

            } catch (\Exception $e) {
                $totalErrors += count($batchUuids);
                Log::error("Batch $batchNumber failed", [
                    'error' => $e->getMessage(),
                    'batch_size' => count($batchUuids),
                ]);
            }

            if ($batchNumber < $totalBatches) {
                sleep(2);
            }

            DB::disconnect();
        }

        Log::info("Batched analysis completed for slug: $slug", [
            'total_polygons' => $totalPolygons,
            'total_processed' => $totalProcessed,
            'total_skipped' => $totalSkipped,
            'total_errors' => $totalErrors,
            'total_batches' => $totalBatches,
        ]);

        return response()->json([
            'message' => 'Batched analysis completed',
            'stats' => [
                'total_polygons' => $totalPolygons,
                'processed' => $totalProcessed,
                'skipped' => $totalSkipped,
                'errors' => $totalErrors,
                'total_batches' => $totalBatches,
            ],
        ]);
    }

    /**
     * Process a single batch of polygons
     *
     * @param array $request
     * @param string $slug
     * @param array $slugMappings
     * @return \Illuminate\Http\JsonResponse
     */
    protected function processSingleBatch(array $request, string $slug, array $slugMappings)
    {
        $totalPolygons = count($request['uuids']);
        $processedCount = 0;
        $skippedCount = 0;
        $errorCount = 0;
        $updateExisting = isset($request['update_existing']) ? $request['update_existing'] : false;

        foreach ($request['uuids'] as $index => $uuid) {
            try {
                if ($index % 100 === 0) {
                    Log::info("Processing polygon $index of $totalPolygons for slug: $slug");
                }

                $polygonGeometry = $this->getGeometry($uuid);

                if (! $polygonGeometry) {
                    Log::warning("Could not retrieve geometry for polygon UUID: $uuid");
                    $errorCount++;

                    continue;
                }

                $registerExist = DB::table($slugMappings[$slug]['table_name'])
                    ->where('site_polygon_id', $polygonGeometry['site_polygon_id'])
                    ->where('indicator_slug', $slug)
                    ->where('year_of_analysis', Carbon::now()->year)
                    ->exists();

                Log::debug('Checking existing records', [
                    'uuid' => $uuid,
                    'site_polygon_id' => $polygonGeometry['site_polygon_id'],
                    'table' => $slugMappings[$slug]['table_name'],
                    'exists' => $registerExist,
                ]);

                if ($registerExist && ! $updateExisting && ! $request['force']) {
                    $skippedCount++;
                    Log::debug("Skipping existing record for polygon: $uuid");

                    continue;
                }

                if (str_contains($slug, 'restorationBy')) {
                    $this->processRestorationAnalysis($uuid, $slug, $polygonGeometry, $slugMappings, $updateExisting);
                } else {
                    $this->processTreeCoverAnalysis($uuid, $slug, $polygonGeometry, $slugMappings, $updateExisting);
                }

                $processedCount++;

            } catch (\Exception $e) {
                $errorCount++;
                Log::error("Error processing polygon UUID: $uuid", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            } finally {
                if (($index + 1) % 50 === 0) {
                    DB::disconnect();
                }
            }
        }

        Log::info("Single batch analysis completed for slug: $slug", [
            'total_polygons' => $totalPolygons,
            'processed' => $processedCount,
            'skipped' => $skippedCount,
            'errors' => $errorCount,
            'update_existing' => $updateExisting,
        ]);

        return response()->json([
            'message' => 'Analysis completed',
            'stats' => [
                'total_polygons' => $totalPolygons,
                'processed' => $processedCount,
                'skipped' => $skippedCount,
                'errors' => $errorCount,
            ],
        ]);
    }

    /**
     * Process restoration analysis for a polygon
     *
     * @param string $uuid
     * @param string $slug
     * @param array $polygonGeometry
     * @param array $slugMappings
     * @param bool $updateExisting
     * @return void
     */
    protected function processRestorationAnalysis($uuid, $slug, $polygonGeometry, $slugMappings, $updateExisting)
    {
        $geojson = GeometryHelper::getPolygonGeojson($uuid);

        if (! $geojson) {
            Log::warning("Could not retrieve GeoJSON for polygon UUID: $uuid");

            return;
        }

        $retries = 0;
        $success = false;

        while (! $success && $retries < self::MAX_RETRIES) {
            try {
                $indicatorRestorationResponse = App::make(PythonService::class)->IndicatorPolygon(
                    $geojson,
                    $slugMappings[$slug]['indicator'],
                    getenv('GFW_SECRET_KEY')
                );

                if (! isset($indicatorRestorationResponse['area'][$slugMappings[$slug]['indicator']])) {
                    throw new \Exception('Invalid response structure from Python service');
                }

                if ($slug == 'restorationByEcoRegion') {
                    $value = json_encode($indicatorRestorationResponse['area'][$slugMappings[$slug]['indicator']]);
                } else {
                    $value = $this->formatKeysValues($indicatorRestorationResponse['area'][$slugMappings[$slug]['indicator']]);
                }

                $data = [
                    'indicator_slug' => $slug,
                    'site_polygon_id' => $polygonGeometry['site_polygon_id'],
                    'year_of_analysis' => Carbon::now()->year,
                    'value' => $value,
                ];

                // Insert or update the record based on the updateExisting flag
                $searchCriteria = [
                    'indicator_slug' => $slug,
                    'site_polygon_id' => $polygonGeometry['site_polygon_id'],
                    'year_of_analysis' => Carbon::now()->year,
                ];

                if ($updateExisting) {
                    // Update existing record or create if it doesn't exist (when force = true)
                    $model = $slugMappings[$slug]['model'];
                    $record = $model::where($searchCriteria)->first();

                    if ($record) {
                        Log::debug("Found existing record ID: {$record->id}, updating");
                        $record->value = $value;
                        $record->save();
                        Log::debug("Updated record with value: {$value}");
                    } else {
                        Log::debug("Record not found, creating new record for polygon UUID: $uuid");
                        $slugMappings[$slug]['model']::create($data);
                        Log::debug("Created new record with value: {$value}");
                    }
                } else {
                    // Only create if it doesn't exist
                    $exists = $slugMappings[$slug]['model']::where($searchCriteria)->exists();
                    if (! $exists) {
                        Log::debug("Creating new record for polygon UUID: $uuid");
                        $slugMappings[$slug]['model']::create($data);
                    } else {
                        Log::debug("Record already exists for polygon UUID: $uuid - skipping");
                    }
                }

                $success = true;
            } catch (\Exception $e) {
                $retries++;
                Log::warning("Retry $retries for polygon UUID: $uuid", [
                    'error' => $e->getMessage(),
                ]);

                if ($retries >= self::MAX_RETRIES) {
                    throw $e;
                }

                sleep(1);
            }
        }
    }

    /**
     * Process tree cover analysis for a polygon
     *
     * @param string $uuid
     * @param string $slug
     * @param array $polygonGeometry
     * @param array $slugMappings
     * @param bool $updateExisting
     * @return void
     */
    protected function processTreeCoverAnalysis($uuid, $slug, $polygonGeometry, $slugMappings, $updateExisting)
    {
        $retries = 0;
        $success = false;

        while (! $success && $retries < self::MAX_RETRIES) {
            try {
                $response = $this->sendApiRequestIndicator(
                    getenv('GFW_SECRET_KEY'),
                    $slugMappings[$slug]['query_url'],
                    $slugMappings[$slug]['sql'],
                    $polygonGeometry['geo']
                );

                if (! $response->successful()) {
                    throw new \Exception('API request failed with status: ' . $response->status());
                }

                $processedTreeCoverLossValue = $this->processTreeCoverLossValue(
                    $response->json()['data'],
                    $slugMappings[$slug]['indicator']
                );

                $data = $this->generateTreeCoverLossData($processedTreeCoverLossValue, $slug, $polygonGeometry);

                // Insert or update the record based on the updateExisting flag
                $searchCriteria = [
                    'indicator_slug' => $slug,
                    'site_polygon_id' => $polygonGeometry['site_polygon_id'],
                    'year_of_analysis' => Carbon::now()->year,
                ];

                if ($updateExisting) {
                    $model = $slugMappings[$slug]['model'];
                    $record = $model::where($searchCriteria)->first();

                    if ($record) {
                        Log::debug("Found existing tree cover loss record ID: {$record->id}, updating");
                        $record->value = $data['value'];
                        $record->save();
                        Log::debug("Updated tree cover loss record with value: {$data['value']}");
                    } else {
                        Log::debug("Tree cover loss record not found, creating new record for polygon UUID: $uuid");
                        $slugMappings[$slug]['model']::create($data);
                        Log::debug("Created new tree cover loss record with value: {$data['value']}");
                    }
                } else {
                    $exists = $slugMappings[$slug]['model']::where($searchCriteria)->exists();
                    if (! $exists) {
                        Log::debug("Creating new record for polygon UUID: $uuid");
                        $slugMappings[$slug]['model']::create($data);
                    } else {
                        Log::debug("Record already exists for polygon UUID: $uuid - skipping");
                    }
                }

                $success = true;
            } catch (\Exception $e) {
                $retries++;
                Log::warning("Retry $retries for polygon UUID: $uuid", [
                    'error' => $e->getMessage(),
                ]);

                if ($retries >= self::MAX_RETRIES) {
                    throw $e;
                }

                sleep(1);
            }
        }
    }

    /**
     * Get geometry for a polygon
     *
     * @param string $polygonUuid
     * @return array|null
     */
    public function getGeometry($polygonUuid)
    {
        try {
            $geojson = GeometryHelper::getMonitoredPolygonsGeojson($polygonUuid);

            if (! $geojson || ! isset($geojson['geometry']) || ! isset($geojson['site_polygon_id'])) {
                Log::warning("Invalid geometry for polygon UUID: $polygonUuid");

                return null;
            }

            $geoJsonObject = json_decode($geojson['geometry']->geojsonGeometry, true);

            if (! $geoJsonObject || ! isset($geoJsonObject['coordinates'])) {
                Log::warning("Invalid GeoJSON for polygon UUID: $polygonUuid");

                return null;
            }

            $sitePolygon = $geojson['geometry']->sitePolygon;
            $plantstart = $sitePolygon ? $sitePolygon->plantstart : null;

            return [
                'geo' => [
                    'type' => 'Polygon',
                    'coordinates' => $geoJsonObject['coordinates'],
                ],
                'site_polygon_id' => $geojson['site_polygon_id'],
                'plantstart' => $plantstart,
            ];
        } catch (\Exception $e) {
            Log::error("Error retrieving geometry for polygon UUID: $polygonUuid", [
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Send API request to indicator service
     *
     * @param string $secret_key
     * @param string $query_url
     * @param string $query_sql
     * @param array $geometry
     * @return \Illuminate\Http\Client\Response
     */
    public function sendApiRequestIndicator($secret_key, $query_url, $query_sql, $geometry)
    {
        $response = Http::withHeaders([
            'content-type' => 'application/json',
            'x-api-key' => $secret_key,
        ])->timeout(30)->post('https://data-api.globalforestwatch.org' . $query_url, [
            'sql' => $query_sql,
            'geometry' => $geometry,
        ]);

        if ($response->successful()) {
            $gfwDataFile = tempnam(sys_get_temp_dir(), 'gfw_') . '.json';
            $geometryFile = tempnam(sys_get_temp_dir(), 'geom_') . '.json';
            $outputFile = tempnam(sys_get_temp_dir(), 'output_') . '.json';

            try {
                file_put_contents($gfwDataFile, json_encode($response->json()));
                file_put_contents($geometryFile, json_encode($geometry));

                $process = new Process([
                    'python3',
                    base_path() . '/resources/python/gfw-area-adjustment/app.py',
                    $gfwDataFile,
                    $geometryFile,
                    $outputFile,
                ]);

                $process->setTimeout(60);
                $process->run();

                if (! $process->isSuccessful()) {
                    Log::error('Area adjustment failed: ' . $process->getErrorOutput());

                    return $response;
                }

                if (! file_exists($outputFile)) {
                    Log::error('Output file not created by Python script');

                    return $response;
                }

                $outputContent = file_get_contents($outputFile);
                if (empty($outputContent)) {
                    Log::error('Output file is empty');

                    return $response;
                }

                $adjustedData = json_decode($outputContent, true);
                if (! $adjustedData) {
                    Log::error('Failed to decode adjusted data: ' . json_last_error_msg());

                    return $response;
                }

                return new \Illuminate\Http\Client\Response(
                    new \GuzzleHttp\Psr7\Response(
                        200,
                        ['Content-Type' => 'application/json'],
                        json_encode($adjustedData)
                    )
                );

            } catch (\Exception $e) {
                Log::error('Error adjusting areas: ' . $e->getMessage());

                return $response;
            } finally {
                @unlink($gfwDataFile);
                @unlink($geometryFile);
                @unlink($outputFile);
            }
        }

        return $response;
    }

    /**
     * Process tree cover loss value
     *
     * @param array $data
     * @param string $indicator
     * @return array
     */
    public function processTreeCoverLossValue($data, $indicator)
    {
        $processedTreeCoverLossValue = [];
        if (! is_array($data)) {
            Log::warning('Invalid data format for tree cover loss processing', ['data' => $data]);

            return $processedTreeCoverLossValue;
        }

        foreach ($data as $i) {
            if (isset($i[$indicator . '__year']) && isset($i['area__ha'])) {
                $processedTreeCoverLossValue[$i[$indicator . '__year']] = $i['area__ha'];
            }
        }

        return $processedTreeCoverLossValue;
    }

    /**
     * Generate tree cover loss data
     *
     * @param array $processedTreeCoverLossValue
     * @param string $slug
     * @param array $polygonGeometry
     * @return array
     */
    public function generateTreeCoverLossData($processedTreeCoverLossValue, $slug, $polygonGeometry)
    {
        $yearsOfAnalysis = $this->getDynamicYearsOfAnalysis($polygonGeometry['plantstart']);
        $responseData = [];
        foreach ($yearsOfAnalysis as $year) {
            if (isset($processedTreeCoverLossValue[$year])) {
                $responseData[$year] = $processedTreeCoverLossValue[$year];
            } else {
                $responseData[$year] = 0.0;
            }
        }

        return [
            'indicator_slug' => $slug,
            'site_polygon_id' => $polygonGeometry['site_polygon_id'],
            'year_of_analysis' => Carbon::now()->year,
            'value' => json_encode($responseData),
        ];
    }

    /**
     * Get dynamic years of analysis based on plantstart date
     *
     * @param string|null $plantstart
     * @return array
     */
    protected function getDynamicYearsOfAnalysis($plantstart = null)
    {
        $currentYear = Carbon::now()->year;
        $startYear = 2010;

        if ($plantstart != null) {
            try {
                $plantstartYear = Carbon::parse($plantstart)->year;
                $endYear = max($plantstartYear, $currentYear);
                $startYear = max(2010, $endYear - 15);

            } catch (\Exception $e) {
                Log::warning("Invalid plantstart date: $plantstart, using default years", [
                    'error' => $e->getMessage(),
                ]);
                $endYear = $currentYear;
            }
        } else {
            $endYear = $currentYear;
            Log::debug('No plantstart date provided, using current year as end year');
        }

        return range($startYear, $endYear);
    }

    /**
     * Format keys and values
     *
     * @param array $data
     * @return string
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
