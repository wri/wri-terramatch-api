<?php

namespace App\Console\Commands;

use App\Helpers\GeometryHelper;
use App\Models\V2\MonitoredData\IndicatorHectares;
use App\Models\V2\MonitoredData\IndicatorTreeCoverLoss;
use App\Services\PythonService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UpdateValuesForIndicatorsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update-values-indicators';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update values for indicators';

    public function handle(): int
    {
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

        foreach ($slugMappings as $slug => $slugMapping) {
            $uuids = [];
            $processedCount = 0;
            $errorCount = 0;
            $this->info('Processing ' . $slug . '...');
            $progressBar = $this->output->createProgressBar();
            $progressBar->start();
            $data = $slugMapping['model']::with('sitePolygon')
                ->where('indicator_slug', $slug)
                ->select('id', 'site_polygon_id')->get();
            $uuids = $data->map(function ($item) {
                return $item->sitePolygon ? $item->sitePolygon->poly_id : null;
            })->filter()->toArray();

            foreach ($uuids as $uuid) {
                try {
                    $polygonGeometry = $this->getGeometry($uuid);
                    $registerExist = DB::table($slugMappings[$slug]['table_name'].' as i')
                        ->where('i.site_polygon_id', $polygonGeometry['site_polygon_id'])
                        ->where('i.indicator_slug', $slug)
                        ->where('i.year_of_analysis', Carbon::now()->year)
                        ->exists();

                    if (! $registerExist) {
                        continue;
                    }

                    if (str_contains($slug, 'restorationBy')) {
                        $geojson = GeometryHelper::getPolygonGeojson($uuid);
                        $indicatorRestorationResponse = App::make(PythonService::class)->IndicatorPolygon($geojson, $slugMappings[$slug]['indicator'], getenv('GFW_SECRET_KEY'));

                        if ($slug == 'restorationByEcoRegion') {
                            $value = json_encode($indicatorRestorationResponse['area'][$slugMappings[$slug]['indicator']]);
                        } else {
                            $value = $this->formatKeysValues($indicatorRestorationResponse['area'][$slugMappings[$slug]['indicator']]);
                        }
                        $data = [
                            'value' => $value,
                        ];
                        $slugMappings[$slug]['model']::where('site_polygon_id', $polygonGeometry['site_polygon_id'])
                            ->where('indicator_slug', $slug)
                            ->where('year_of_analysis', Carbon::now()->year)
                            ->update($data);

                        $processedCount++;
                        $progressBar->advance();

                        continue;
                    }

                    $response = $this->sendApiRequestIndicator(getenv('GFW_SECRET_KEY'), $slugMappings[$slug]['query_url'], $slugMappings[$slug]['sql'], $polygonGeometry['geo']);
                    if (str_contains($slug, 'treeCoverLoss')) {
                        $processedTreeCoverLossValue = $this->processTreeCoverLossValue($response->json()['data'], $slugMappings[$slug]['indicator']);
                    }

                    if ($response->successful()) {
                        if (str_contains($slug, 'treeCoverLoss')) {
                            $data = $this->generateTreeCoverLossData($processedTreeCoverLossValue);
                        } else {
                            $data = [
                                'value' => json_encode($response->json()['data']),
                            ];
                        }

                        $slugMappings[$slug]['model']::where('site_polygon_id', $polygonGeometry['site_polygon_id'])
                            ->where('indicator_slug', $slug)
                            ->where('year_of_analysis', Carbon::now()->year)
                            ->update($data);
                        $processedCount++;
                        $progressBar->advance();
                    } else {
                        Log::error('A problem occurred during the analysis of the geometry for the polygon: ' . $uuid);
                    }
                } catch (\Exception $e) {
                    Log::error('Error in the analysis: ' . $e->getMessage());
                    $errorCount++;
                }
            }
            $progressBar->finish();
            $this->info("\n\n{$slug} updated successfully.");
            $this->info("Processed: {$processedCount} polygons");
            $this->info("Errors: {$errorCount}");
        }

        return 0;
    }

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

    public function processTreeCoverLossValue($data, $indicator)
    {
        $processedTreeCoverLossValue = [];
        foreach ($data as $i) {
            $processedTreeCoverLossValue[$i[$indicator . '__year']] = $i['area__ha'];
        }

        return $processedTreeCoverLossValue;
    }

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
