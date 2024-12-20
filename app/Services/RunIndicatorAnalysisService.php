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
            foreach ($request['uuids'] as $uuid) {
                $polygonGeometry = $this->getGeometry($uuid);
                $registerExist = DB::table($slugMappings[$slug]['table_name'].' as i')
                    ->where('i.site_polygon_id', $polygonGeometry['site_polygon_id'])
                    ->where('i.indicator_slug', $slug)
                    ->where('i.year_of_analysis', Carbon::now()->year)
                    ->exists();

                if ($registerExist) {
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
                        'indicator_slug' => $slug,
                        'site_polygon_id' => $polygonGeometry['site_polygon_id'],
                        'year_of_analysis' => Carbon::now()->year,
                        'value' => $value,
                    ];
                    $slugMappings[$slug]['model']::create($data);

                    continue;
                }

                $response = $this->sendApiRequestIndicator(getenv('GFW_SECRET_KEY'), $slugMappings[$slug]['query_url'], $slugMappings[$slug]['sql'], $polygonGeometry['geo']);
                if (str_contains($slug, 'treeCoverLoss')) {
                    $processedTreeCoverLossValue = $this->processTreeCoverLossValue($response->json()['data'], $slugMappings[$slug]['indicator']);
                }

                if ($response->successful()) {
                    if (str_contains($slug, 'treeCoverLoss')) {
                        $data = $this->generateTreeCoverLossData($processedTreeCoverLossValue, $slug, $polygonGeometry);
                    } else {
                        $data = [
                            'indicator_slug' => $slug,
                            'site_polygon_id' => $polygonGeometry['site_polygon_id'],
                            'year_of_analysis' => Carbon::now()->year,
                            'value' => json_encode($response->json()['data']),
                        ];
                    }

                    $slugMappings[$slug]['model']::create($data);
                } else {
                    Log::error('A problem occurred during the analysis of the geometry for the polygon: ' . $uuid);
                }
            }

            return response()->json(['message' => 'Analysis completed']);
        } catch (\Exception $e) {
            Log::info($e);

            return response()->json([
                'message' => 'An error occurred during the analysis',
                'error' => $e->getMessage(),
            ], 500);
        }
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
        $response = Http::withHeaders([
            'content-type' => 'application/json',
            'x-api-key' => $secret_key,
        ])->post('https://data-api.globalforestwatch.org' . $query_url, [
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

                $process->run();

                if (! $process->isSuccessful()) {
                    Log::error('Area adjustment failed: ' . $process->getErrorOutput());

                    return $response;
                }

                $adjustedData = json_decode(file_get_contents($outputFile), true);

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

    public function processTreeCoverLossValue($data, $indicator)
    {
        $processedTreeCoverLossValue = [];
        foreach ($data as $i) {
            $processedTreeCoverLossValue[$i[$indicator . '__year']] = $i['area__ha'];
        }

        return $processedTreeCoverLossValue;
    }

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
