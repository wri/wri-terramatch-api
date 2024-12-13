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
        if (! isset($slugMappings[$slug])) {
            return response()->json(['message' => 'Indicator not found'], 404);
        }

        return $this->exportCsv($entity, $slug);
    }

    public function exportCsv($entity, $slug)
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
        ];

        $sitePolygonsIndicator = SitePolygon::whereHas($slugMappings[$slug]['relation_name'], function ($query) use ($slug) {
            $query->where('indicator_slug', $slug)
                ->where('year_of_analysis', date('Y'));
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
                $indicator = $polygon->{$slugMappings[$slug]['relation_name']}()
                    ->where('indicator_slug', $slug)
                    ->select([
                        'indicator_slug',
                        'year_of_analysis',
                        'value',
                        'created_at',
                    ])
                    ->first();
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
                    $results['2015'] = $valueYears['2015'];
                    $results['2016'] = $valueYears['2016'];
                    $results['2017'] = (float) $valueYears['2017'];
                    $results['2018'] = $valueYears['2018'];
                    $results['2019'] = $valueYears['2019'];
                    $results['2020'] = $valueYears['2020'];
                    $results['2021'] = $valueYears['2021'];
                    $results['2022'] = $valueYears['2022'];
                    $results['2023'] = $valueYears['2023'];
                    $results['2024'] = $valueYears['2024'];
                }
                if ($slug == 'restorationByEcoRegion') {
                    $values = json_decode($indicator->value, true);
                    $results = array_merge($results, RestorationByEcoregionHelper::getCategoryEcoRegion($values, true));
                }
                if ($slug == 'restorationByLandUse' || $slug == 'restorationByStrategy') {
                    $values = json_decode($indicator->value, true);
                    $results = array_merge($results, $this->processValuesHectares($values));
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
