<?php

namespace App\Http\Controllers\V2\MonitoredData;

use App\Helpers\RestorationByEcoregionHelper;
use App\Http\Controllers\Controller;
use App\Models\V2\EntityModel;
use App\Models\V2\Projects\Project;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SitePolygon;
use League\Csv\Writer;

class IndicatorEntitySlugExportController extends Controller
{
    public function __invoke(EntityModel $entity, string $slug)
    {
        $defaulHeaders = [
            'poly_name' => 'Polygon Name',
            'size' => 'Size (ha)',
            'site_name' => 'Site Name',
            'status' => 'Status',
            'plantstart' => 'Plant Start Date',
        ];
        $treeCoverLossHeaders = [
            ...$defaulHeaders,
            '2010' => '2010',
            '2011' => '2011',
            '2012' => '2012',
            '2013' => '2013',
            '2014' => '2014',
            '2015' => '2015',
            '2016' => '2016',
            '2017' => '2017',
            '2018' => '2018',
            '2019' => '2019',
            '2020' => '2020',
            '2021' => '2021',
            '2022' => '2022',
            '2023' => '2023',
            '2024' => '2024',
            '2025' => '2025',
        ];
        $restorationByEcoRegionHeaders = [
            ...$defaulHeaders,
            'created_at' => 'Baseline',
            'australasian' => 'Australasian',
            'afrotropical' => 'Afrotropical',
            'palearctic' => 'Palearctic11',
        ];
        $restorationByStrategyHeaders = [
            ...$defaulHeaders,
            'created_at' => 'Baseline',
            'tree_planting' => 'Tree Planting',
            'assisted_natural_regeneration' => 'Assisted Natural Regeneration',
            'direct_seeding' => 'Direct Seeding',
        ];
        $restorationByLandUseHeaders = [
            ...$defaulHeaders,
            'created_at' => 'Baseline',
            'agroforest' => 'Agroforest',
            'natural_forest' => 'Natural Forest',
            'mangrove' => 'Mangrove',
        ];
        $treeCoverHeaders = [
            ...$defaulHeaders,
            'percent_cover' => 'Percent Cover',
            'project_phase' => 'Project Phase',
            'plus_minus_percent' => 'Plus Minus Percent',
        ];
        $slugMappings = [
            'treeCoverLoss' => [
                'relation_name' => 'treeCoverLossIndicator',
                'columns' => $treeCoverLossHeaders,
                'indicator_title' => 'Tree Cover Loss',
            ],
            'treeCoverLossFires' => [
                'relation_name' => 'treeCoverLossIndicator',
                'columns' => $treeCoverLossHeaders,
                'indicator_title' => 'Tree Cover Loss from Fire',
            ],
            'restorationByStrategy' => [
                'relation_name' => 'hectaresIndicator',
                'columns' => $restorationByStrategyHeaders,
                'indicator_title' => 'Hectares Under Restoration By Strategy',
            ],
            'restorationByLandUse' => [
                'relation_name' => 'hectaresIndicator',
                'columns' => $restorationByLandUseHeaders,
                'indicator_title' => 'Hectares Under Restoration By Target Land Use System',
            ],
            'restorationByEcoRegion' => [
                'relation_name' => 'hectaresIndicator',
                'columns' => $restorationByEcoRegionHeaders,
                'indicator_title' => 'Hectares Under Restoration By WWF EcoRegion',
            ],
            'treeCover' => [
                'relation_name' => 'treeCoverIndicator',
                'columns' => $treeCoverHeaders,
                'indicator_title' => 'Tree Cover',
            ],
        ];
        if (! isset($slugMappings[$slug])) {
            return response()->json(['message' => 'Indicator not found'], 404);
        }

        return $this->exportCsv($entity, $slug, $slugMappings);
    }

    public function exportCsv($entity, $slug, $slugMappings)
    {
        $sitePolygonsIndicator = SitePolygon::whereHas($slugMappings[$slug]['relation_name'], function ($query) use ($slug) {
            $query->where('indicator_slug', $slug)
                ->where('year_of_analysis', date('Y'))
                ->where('status', 'approved');
        })
            ->whereHas('site', function ($query) use ($entity) {
                if (get_class($entity) == Site::class) {
                    $query->where('uuid', $entity->uuid);
                } elseif (get_class($entity) == Project::class) {
                    $query->where('project_id', $entity->project->id);
                }
            })
            ->select([
                'id',
                'poly_name',
                'status',
                'plantstart',
                'site_id',
                'is_active',
                'poly_id',
                'calc_area',
            ])
            ->where('is_active', 1)
            ->get()
            ->map(function ($polygon) use ($slugMappings, $slug) {
                if ($slug == 'treeCover') {
                    $indicator = $polygon->{$slugMappings[$slug]['relation_name']}()
                        ->where('indicator_slug', $slug)
                        ->select([
                            'indicator_slug',
                            'year_of_analysis',
                            'percent_cover',
                            'project_phase',
                            'plus_minus_percent',
                            'created_at',
                        ])
                        ->first();
                } else {
                    $indicator = $polygon->{$slugMappings[$slug]['relation_name']}()
                        ->where('indicator_slug', $slug)
                        ->select([
                            'indicator_slug',
                            'year_of_analysis',
                            'value',
                            'created_at',
                        ])
                        ->first();
                }
                $results = [
                    'poly_name' => $polygon->poly_name,
                    'status' => $polygon->status,
                    'plantstart' => $polygon->plantstart,
                    'site_name' => $polygon->site->name ?? '',
                    'size' => $polygon->calc_area ?? 0,
                    'created_at' => $indicator->created_at,
                ];
                if (str_contains($slug, 'treeCoverLoss')) {
                    $valueYears = json_decode($indicator->value, true);
                    foreach (range(2010, 2025) as $year) {
                        $results["$year"] = (float) $valueYears[$year] ?? 0;
                    }
                }
                if ($slug == 'restorationByEcoRegion') {
                    $values = json_decode($indicator->value, true);
                    $results = array_merge($results, RestorationByEcoregionHelper::getCategoryEcoRegion($values, true));
                }
                if ($slug == 'restorationByLandUse' || $slug == 'restorationByStrategy') {
                    $values = json_decode($indicator->value, true);
                    $results = array_merge($results, $this->processValuesHectares($values));
                }
                if ($slug == 'treeCover') {
                    $results['percent_cover'] = $indicator->percent_cover;
                    $results['project_phase'] = $indicator->project_phase;
                    $results['plus_minus_percent'] = $indicator->plus_minus_percent;
                }

                return $results;
            });

        $filteredIndicators = [];
        foreach ($sitePolygonsIndicator as $polygon) {
            $filteredIndicator = [];
            foreach ($slugMappings[$slug]['columns'] as $key => $label) {
                $filteredIndicator[$key] = $polygon[$key] ?? '';
            }
            $filteredIndicators[] = $filteredIndicator;
        }

        $csv = Writer::createFromString('');

        $csv->insertOne(array_values($slugMappings[$slug]['columns']));

        foreach ($filteredIndicators as $filteredIndicator) {
            $csv->insertOne(array_values($filteredIndicator));
        }

        $csvContent = $csv->toString();

        return response($csvContent, 200, [
          'Content-Type' => 'text/csv',
          'Content-Disposition' => 'attachment; filename=indicator' . $slugMappings[$slug]['indicator_title'] . '.csv',
      ]);

    }

    public function processValuesHectares($values)
    {
        $separateKeys = [];
        foreach ($values as $key => $value) {
            $array = explode(',', str_replace('-', '_', $key));
            $arrayTrim = array_map('trim', $array);
            foreach ($arrayTrim as $item) {
                $separateKeys[$item] = round((float) $value, 3);
            }
        }

        return $separateKeys;
    }
}
