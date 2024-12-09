<?php

namespace App\Http\Controllers\V2\MonitoredData;

use App\Helpers\GeometryHelper;
use App\Http\Controllers\Controller;
use App\Models\V2\MonitoredData\IndicatorHectares;
use App\Models\V2\MonitoredData\IndicatorTreeCoverLoss;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RunIndicatorAnalysisNotDelayedJobController extends Controller
{
    public function __invoke(Request $request, string $slug)
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
                'sql' => 'SELECT eco_name FROM results',
                'query_url' => '/dataset/wwf_terrestrial_ecoregions/latest/query',
                'indicator' => 'wwf_terrestrial_ecoregions',
                'model' => IndicatorHectares::class,
                'table_name' => 'indicator_output_hectares',
            ],
        ];

        if (! isset($slugMappings[$slug])) {
            return response()->json(['message' => 'Slug Not Found'], 400);
        }

        try {
            $polygonUuids = $request->all();
            foreach ($polygonUuids['uuids'] as $uuid) {
                $polygonGeometry = $this->getGeometry($uuid);
                $registerExist = DB::table($slugMappings[$slug]['table_name'].' as i')
                    ->where('i.site_polygon_id', $polygonGeometry['site_polygon_id'])
                    ->where('i.indicator_slug', $slug)
                    ->where('i.year_of_analysis', Carbon::now()->year)
                    ->exists();
                if ($registerExist) {
                    continue;
                }
                $response = $this->sendApiRequestIndicator(getenv('GFW_SECRET_KEY'), $slugMappings[$slug]['query_url'], $slugMappings[$slug]['sql'], $polygonGeometry['geo']);
                if (str_contains($slug, 'treeCoverLoss')) {
                    $processedTreeCoverLossValue = $this->processTreeCoverLossValue($response->json()['data']);
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
        return Http::withHeaders([
            'content-type' => 'application/json',
            'x-api-key' => $secret_key,
        ])->post('https://data-api.globalforestwatch.org' . $query_url, [
            'sql' => $query_sql,
            'geometry' => $geometry,
        ]);
    }

    public function processTreeCoverLossValue($data)
    {
        $processedTreeCoverLossValue = [];
        foreach ($data as $i) {
            $processedTreeCoverLossValue[$i['umd_tree_cover_loss__year']] = $i['area__ha'];
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
}
