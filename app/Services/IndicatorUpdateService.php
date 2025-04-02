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

    /**
     * Update indicator values for a specific polygon
     *
     * @param string $polygonUuid The UUID of the polygon
     * @return array Results of the update operation
     */
    public function updateIndicatorsForPolygon(string $polygonUuid)
    {
        $results = [];

        foreach ($this->slugMappings as $slug => $slugMapping) {
            $results[$slug] = [
                'status' => 'skipped',
                'message' => 'No processing needed',
            ];

            try {
                $polygonGeometry = $this->getGeometry($polygonUuid);
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

                if (str_contains($slug, 'treeCoverLoss')) {
                    $result = $this->processTreeCoverLossIndicator($slug, $polygonGeometry);
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
     * Process tree cover loss indicator
     */
    protected function processTreeCoverLossIndicator($slug, $polygonGeometry)
    {
        $response = $this->sendApiRequestIndicator(
            getenv('GFW_SECRET_KEY'),
            $this->slugMappings[$slug]['query_url'],
            $this->slugMappings[$slug]['sql'],
            $polygonGeometry['geo']
        );

        if (! $response->successful()) {
            return [
                'status' => 'error',
                'message' => 'API request failed with status: ' . $response->status(),
            ];
        }

        $processedValue = $this->processTreeCoverLossValue($response->json()['data'], $this->slugMappings[$slug]['indicator']);
        $data = $this->generateTreeCoverLossData($processedValue);

        $this->slugMappings[$slug]['model']::where('site_polygon_id', $polygonGeometry['site_polygon_id'])
            ->where('indicator_slug', $slug)
            ->where('year_of_analysis', Carbon::now()->year)
            ->update($data);

        return [
            'status' => 'success',
            'message' => 'Tree cover loss indicator updated successfully',
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
