<?php

namespace App\Http\Controllers\V2\MonitoredData;

use App\Helpers\RestorationByEcoregionHelper;
use App\Http\Controllers\Controller;
use App\Models\V2\EntityModel;
use App\Models\V2\Projects\Project;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SitePolygon;
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
        if (! isset($slugMappings[$slug])) {
            return response()->json([]);
        }

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
                    'calc_area',
                ])
                ->where('is_active', 1)
                ->where('status', 'approved')
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
                        'poly_name' => $polygon->poly_name ?? '-',
                        'poly_id' => $polygon->poly_id,
                        'site_id' => $polygon->site_id,
                        'status' => $polygon->status,
                        'plantstart' => $polygon->plantstart ?? '-',
                        'site_name' => $polygon->site->name ?? '-',
                        'size' => round($polygon->calc_area ?? 0, 1),
                        'indicator_slug' => $indicator->indicator_slug,
                        'year_of_analysis' => $indicator->year_of_analysis,
                        'created_at' => $indicator->created_at,
                        'base_line' => $indicator->created_at,
                        'data' => [],
                    ];
                    if (str_contains($slug, 'treeCoverLoss')) {
                        $valueYears = json_decode($indicator->value, true);
                        foreach (range(2010, 2025) as $year) {
                            $results['data']["$year"] = round((float) ($valueYears["$year"] ?? 0), 1);
                        }
                    }

                    if ($slug == 'restorationByEcoRegion') {
                        $values = json_decode($indicator->value, true);
                        $results = array_merge($results, RestorationByEcoregionHelper::getCategoryEcoRegion($values));
                    }

                    if ($slug == 'restorationByLandUse' || $slug == 'restorationByStrategy') {
                        $values = json_decode($indicator->value, true);
                        $results = array_merge($results, $this->processValuesHectares($values));
                    }

                    return $results;
                });
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
            $counter = 0;
            foreach ($arrayTrim as $item) {
                if ($counter == 0) {
                    $separateKeys[$item] = round((float) $value, 1);
                    $counter++;
                } else {
                    $separateKeys[$item] = null;
                }
            }
        }

        return ['data' => $separateKeys];
    }
}
