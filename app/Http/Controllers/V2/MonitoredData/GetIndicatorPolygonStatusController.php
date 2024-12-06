<?php

namespace App\Http\Controllers\V2\MonitoredData;

use App\Http\Controllers\Controller;
use App\Models\V2\Sites\SitePolygon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\V2\EntityModel;
use App\Models\V2\Projects\Project;
use App\Models\V2\Sites\Site;

class GetIndicatorPolygonStatusController extends Controller
{
    public function __invoke(EntityModel $entity)
    {
        try {
            // $sitePolygonsStatusIndicators = SitePolygon::whereHas('site', function ($query) use ($entity) {
            //         if (get_class($entity) == Site::class) {
            //             $query->where('uuid', $entity->uuid);
            //         } elseif (get_class($entity) == Project::class) {
            //             $query->where('project_id', $entity->project->id);
            //         }
            //     })
            //     ->select([
            //         'id',
            //         'status',
            //     ])
            //     ->where('is_active', 1)
            //     ->get()
            //     ->map(function ($polygon) {
            //         $treeCoverLoss = $polygon->treeCoverLossIndicator;
            //         $hectares = $polygon->hectaresIndicator;
            //         $merge = $treeCoverLoss->merge($hectares);
            //         if (!$merge->isEmpty()) {
            //             $results = [
            //                 'id' => $polygon->id,
            //                 'poly_name' => $polygon->poly_name,
            //                 'status' => $polygon->status,
            //                 'site_name' => $polygon->site->name ?? '',
            //                 'project_name' => $polygon->site->project->name ?? '',
            //             ];
            //             return $results;
            //         }
            //     })->filter()
            //     ->groupBy('status')
            //     ->map(function ($group) {
            //         return $group->count();
            //     });

            // $statuses = ['draft', 'submitted', 'needs-more-information', 'approved'];
            // $statusesByCount = [];
            // foreach ($statuses as $status) {
            //     if (!isset($sitePolygonsStatusIndicators[$status])) {
            //         $statusesByCount[$status] = 0;
            //     } else {
            //         $statusesByCount[$status] = $sitePolygonsStatusIndicators[$status];
            //     }
            // }
            // return response()->json($statusesByCount);

            $sitePolygonGroupByStatus = SitePolygon::whereHas('site', function ($query) use ($entity) {
                        if (get_class($entity) == Site::class) {
                            $query->where('uuid', $entity->uuid);
                        } elseif (get_class($entity) == Project::class) {
                            $query->where('project_id', $entity->project->id);
                        }
                    })
                    ->select([
                        'id',
                        'status',
                        'is_active',
                    ])
                    // ->where('is_active', 1)
                    ->get()
                    ->groupBy('status')
                    ->map(function ($group) {
                        return $group->count();
                    });
            $statuses = ['draft', 'submitted', 'needs-more-information', 'approved'];
            $statusesByCount = [];
            Log::info($sitePolygonGroupByStatus);
            foreach ($statuses as $status) {
                if (!isset($sitePolygonGroupByStatus[$status])) {
                    $statusesByCount[$status] = 0;
                } else {
                    $statusesByCount[$status] = $sitePolygonGroupByStatus[$status];
                }
            }
            return response()->json($statusesByCount);
        } catch (\Exception $e) {
            Log::info($e);
        }
    }
}
