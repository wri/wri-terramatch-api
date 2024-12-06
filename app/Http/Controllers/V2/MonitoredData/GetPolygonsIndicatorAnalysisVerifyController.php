<?php

namespace App\Http\Controllers\V2\MonitoredData;

use App\Http\Controllers\Controller;
use App\Models\V2\Sites\SitePolygon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\V2\EntityModel;
use App\Models\V2\Projects\Project;
use App\Models\V2\Sites\Site;

class GetPolygonsIndicatorAnalysisVerifyController extends Controller
{
    public function __invoke(EntityModel $entity, string $slug)
    {
        $slugMappings = [
            'treeCoverLoss' => [
                'relation_name' => 'treeCoverLossIndicator',
                'indicator_title' => 'Tree Cover Loss'
            ],
            'treeCoverLossFires' => [
                'relation_name' => 'treeCoverLossIndicator',
                'indicator_title' => 'Tree Cover Loss from Fire'
            ],
            'restorationByStrategy' => [
                'relation_name' => 'hectaresIndicator',
                'indicator_title' => 'Hectares Under Restoration By Strategy'
            ],
            'restorationByLandUse' => [
                'relation_name' => 'hectaresIndicator',
                'indicator_title' => 'Hectares Under Restoration By Target Land Use System'
            ],
            'restorationByEcoRegion' => [
                'relation_name' => 'hectaresIndicator',
                'indicator_title' => 'Hectares Under Restoration By WWF EcoRegion'
            ],
        ];
        try {
            $polygonUuids = SitePolygon::whereHas('site', function ($query) use ($entity) {
                    if (get_class($entity) == Site::class) {
                        $query->where('uuid', $entity->uuid);
                    } elseif (get_class($entity) == Project::class) {
                        $query->where('project_id', $entity->project->id);
                    }
                })
                ->select(['id', 'poly_id', 'is_active'])
                // ->where('is_active', 1)
                ->get()
                ->map(function ($polygon) use ($slugMappings, $slug) {
                    $indicator = $polygon->{$slugMappings[$slug]['relation_name']}()
                        ->where('indicator_slug', $slug)
                        ->where('year_of_analysis', date('Y'))
                        ->where('site_polygon_id', $polygon->id)
                        ->first();
                    if (!$indicator) {
                        return $polygon->poly_id;
                    }
                    return null;
                })
                ->filter();
            if ($polygonUuids->isEmpty()) {
                return response()->json(['message' => 'All polygons have already been analyzed to ' . $slugMappings[$slug]['indicator_title']], 200);
            } else {
                return response()->json($polygonUuids);

            }
        } catch (\Exception $e) {
            Log::info($e);
        }
    }
}
