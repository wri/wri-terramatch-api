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

            // Count total and processed polygons for logging
            $totalPolygons = count($request['uuids']);
            $processedCount = 0;
            $skippedCount = 0;
            $errorCount = 0;
            $updateExisting = isset($request['update_existing']) ? $request['update_existing'] : false;

            Log::info("Starting analysis for slug: $slug with $totalPolygons polygons", [
                'update_existing' => $updateExisting,
            ]);

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

                    // Check if we already have a record for this polygon in the current year
                    $registerExist = DB::table($slugMappings[$slug]['table_name'])
                        ->where('site_polygon_id', $polygonGeometry['site_polygon_id'])
                        ->where('indicator_slug', $slug)
                        ->where('year_of_analysis', Carbon::now()->year)
                        ->exists();

                    // Debug logging to check if records are found
                    Log::debug('Checking existing records', [
                        'uuid' => $uuid,
                        'site_polygon_id' => $polygonGeometry['site_polygon_id'],
                        'table' => $slugMappings[$slug]['table_name'],
                        'exists' => $registerExist,
                    ]);

                    // Skip existing records unless update_existing is true or force is true
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
                }
            }

            Log::info("Analysis completed for slug: $slug", [
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
                    // Update existing record using direct update method
                    Log::debug("Updating record for polygon UUID: $uuid with new data");

                    // Try to find and update the existing record
                    $model = $slugMappings[$slug]['model'];
                    $record = $model::where($searchCriteria)->first();

                    if ($record) {
                        Log::debug("Found existing record ID: {$record->id}, updating");
                        $record->value = $value;
                        $record->save();

                        // Log the value for debugging
                        Log::debug("Updated record with value: {$value}");
                    } else {
                        Log::warning("Record expected for update but not found for polygon UUID: $uuid");
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
                    // Update existing record using direct update method
                    Log::debug("Updating tree cover loss record for polygon UUID: $uuid with new data");

                    // Try to find and update the existing record
                    $model = $slugMappings[$slug]['model'];
                    $record = $model::where($searchCriteria)->first();

                    if ($record) {
                        Log::debug("Found existing tree cover loss record ID: {$record->id}, updating");
                        $record->value = $data['value'];
                        $record->save();

                        // Log the value for debugging
                        Log::debug("Updated tree cover loss record with value: {$data['value']}");
                    } else {
                        Log::warning("Tree cover loss record expected for update but not found for polygon UUID: $uuid");
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

            return [
                'geo' => [
                    'type' => 'Polygon',
                    'coordinates' => $geoJsonObject['coordinates'],
                ],
                'site_polygon_id' => $geojson['site_polygon_id'],
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
            'indicator_slug' => $slug,
            'site_polygon_id' => $polygonGeometry['site_polygon_id'],
            'year_of_analysis' => Carbon::now()->year,
            'value' => json_encode($responseData),
        ];
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
