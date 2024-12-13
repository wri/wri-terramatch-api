<?php

namespace App\Http\Controllers\V2\MonitoredData;

use App\Http\Controllers\Controller;
use App\Models\V2\EntityModel;
use App\Models\V2\Projects\Project;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SitePolygon;
use Illuminate\Support\Facades\Log;

class GetIndicatorPolygonStatusController extends Controller
{
    public function __invoke(EntityModel $entity)
    {
        try {
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
                    ->where('is_active', 1)
                    ->get()
                    ->groupBy('status')
                    ->map(function ($group) {
                        return $group->count();
                    });
            $statuses = ['draft', 'submitted', 'needs-more-information', 'approved'];
            $statusesByCount = [];

            foreach ($statuses as $status) {
                if (! isset($sitePolygonGroupByStatus[$status])) {
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
