<?php

namespace App\Http\Controllers\V2\MonitoredData;

use App\Http\Controllers\Controller;
use App\Models\V2\EntityModel;
use App\Models\V2\Projects\Project;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SitePolygon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GetPolygonsIndicatorAnalysisController extends Controller
{
    public function __invoke(EntityModel $entity, string $slug)
    {
        $slugMappings = [
            'treeCoverLoss' => [
                'relation_name' => 'treeCoverLossIndicator',
                'extra_columns' => '',
            ],
            'treeCoverLossFires' => [
                'relation_name' => 'treeCoverLossIndicator',
            ],
            'restorationByStrategy' => [
                'relation_name' => 'hectaresIndicator',
            ],
            'restorationByLandUse' => [
                'relation_name' => 'hectaresIndicator',
            ],
            'restorationByEcoRegion' => [
                'relation_name' => 'hectaresIndicator',
            ],
        ];

        // $slugMappings = [
        //     'treeCoverLoss' => [
        //         'table_name' => 'indicator_output_tree_cover_loss',
        //     ],
        //     'treeCoverLossFires' => [
        //         'table_name' => 'indicator_output_tree_cover_loss',
        //     ],
        //     'restorationByStrategy' => [
        //         'table_name' => 'indicator_output_hectares',
        //     ],
        //     'restorationByLandUse' => [
        //         'table_name' => 'indicator_output_hectares',
        //     ],
        // ];
        try {
            return SitePolygon::whereHas($slugMappings[$slug]['relation_name'], function ($query) use ($slug) {
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
                ])
                // ->where('is_active', 1)
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
                        'id' => $polygon->id,
                        'poly_name' => $polygon->poly_name,
                        'poly_id' => $polygon->poly_id,
                        'status' => $polygon->status,
                        'plantstart' => $polygon->plantstart,
                        'site_name' => $polygon->site->name ?? '',
                        'size' => 100,
                        'indicator_slug' => $indicator->indicator_slug,
                        'year_of_analysis' => $indicator->year_of_analysis,
                        'created_at' => $indicator->created_at,
                        'base_line' => $indicator->created_at,
                    ];
                    if (str_contains($slug, 'treeCoverLoss')) {
                        $valueYears = json_decode($indicator->value, true);
                        $results['2015'] = $valueYears['2015'];
                        $results['2016'] = $valueYears['2016'];
                        $results['2017'] = $valueYears['2017'];
                        $results['2018'] = $valueYears['2018'];
                        $results['2019'] = $valueYears['2019'];
                        $results['2020'] = $valueYears['2020'];
                        $results['2021'] = $valueYears['2021'];
                        $results['2022'] = $valueYears['2022'];
                        $results['2023'] = $valueYears['2023'];
                        $results['2024'] = $valueYears['2024'];
                    }
                    if (str_contains($slug, 'restorationBy')) {
                        $values = json_decode($indicator->value, true);
                        $results = array_merge($results, $this->processValuesHectares($values));
                    }

                    return $results;
                });
            // return DB::table($slugMappings[$slug]['table_name'].' as i')
            //     ->join('site_polygon as sp', 'i.site_polygon_id', '=', 'sp.id')
            //     ->join('v2_sites as s', 'sp.site_id', '=', 's.uuid')
            //     ->where('i.indicator_slug', $slug)
            //     ->select([
            //         'i.site_polygon_id',
            //         'i.indicator_slug',
            //         'i.year_of_analysis',
            //         'i.value',
            //         'i.created_at',
            //         'sp.uuid',
            //         'sp.poly_name',
            //         'sp.status',
            //         'sp.plantstart',
            //         's.name',
            //     ])
            //     ->get();
            // return response()->json($polygonsIndicator);
        } catch (\Exception $e) {
            Log::info($e);
        }
    }

    public function processValuesHectares($values)
    {
        $separateKeys = [];
        foreach ($values as $key => $value) {
            $array = explode(',', str_replace('-', '_', $key));
            $arrayTrim = array_map('trim', $array);
            foreach ($arrayTrim as $item) {
                $separateKeys[$item] = $value;
            }
        }

        return $separateKeys;
    }
}
