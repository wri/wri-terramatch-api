<?php

namespace App\Services\Dashboard;

use App\Helpers\TerrafundDashboardQueryHelper;
use App\Models\V2\Sites\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RunHectaresRestoredService
{
    public function runHectaresRestoredJob(Request $request)
    {
        $projectsToQuery = TerrafundDashboardQueryHelper::buildQueryFromRequest($request)->pluck('uuid');
        $HECTARES_BY_RESTORATION = 'restorationByStrategy';
        $HECTARES_BY_TARGET_LAND_USE_TYPES = 'restorationByLandUse';

        $projectsPolygons = $this->getProjectsPolygons($projectsToQuery);
        $polygonIds = $projectsPolygons->pluck('id')->toArray();

        $restorationStrategiesRepresented = $this->polygonToOutputHectares($HECTARES_BY_RESTORATION, $polygonIds);
        $targetLandUseTypesRepresented = $this->polygonToOutputHectares($HECTARES_BY_TARGET_LAND_USE_TYPES, $polygonIds);

        if ($restorationStrategiesRepresented->isEmpty() && $targetLandUseTypesRepresented->isEmpty()) {
            return (object) [
                'restoration_strategies_represented' => [],
                'target_land_use_types_represented' => [],
                'message' => 'No data available for restoration strategies and target land use types.',
            ];
        }

        return (object) [
            'restoration_strategies_represented' => $this->calculateGroupedHectares($restorationStrategiesRepresented),
            'target_land_use_types_represented' => $this->calculateGroupedHectares($targetLandUseTypesRepresented),
        ];
    }

    /**
     * Get polygons associated with projects by UUID.
     *
     * @param \Illuminate\Support\Collection $projects
     * @return \Illuminate\Support\Collection
     */
    public function getProjectsPolygons($projects)
    {
        if ($projects->isEmpty()) {
            return collect();
        }

        return DB::table('site_polygon as sp')
            ->join('v2_sites as s', 'sp.site_id', '=', 's.uuid')
            ->join('v2_projects as p', 's.project_id', '=', 'p.id')
            ->whereIn('s.status', Site::$approvedStatuses)
            ->where('sp.status', 'approved')
            ->where('sp.is_active', true)
            ->whereIn('p.uuid', $projects)
            ->select('sp.id')
            ->get();
    }

    /**
     * Get hectares data based on indicator and polygon UUIDs.
     *
     * @param string $indicatorId
     * @param array $polygonsUuids
     * @return \Illuminate\Support\Collection
     */
    public function polygonToOutputHectares($indicatorId, $polygonIds)
    {
        if (empty($polygonIds)) {
            return collect();
        }

        return DB::table('indicator_output_hectares')
            ->where('indicator_slug', $indicatorId)
            ->whereIn('site_polygon_id', $polygonIds)
            ->get();
    }

    /**
     * Calculate grouped hectares by summing values from decoded JSON data.
     *
     * @param \Illuminate\Support\Collection $polygonsToOutputHectares
     * @return array
     */
    public function calculateGroupedHectares($polygonsToOutputHectares)
    {
        $hectaresRestored = [];

        $polygonsToOutputHectares->each(function ($hectare) use (&$hectaresRestored) {
            $decodedValue = json_decode($hectare->value, true);

            if ($decodedValue) {
                foreach ($decodedValue as $key => $value) {
                    if (! isset($hectaresRestored[$key])) {
                        $hectaresRestored[$key] = 0;
                    }
                    $hectaresRestored[$key] += $value;
                }
            }
        });

        return array_map(fn ($value) => round($value, 3), $hectaresRestored);
    }
}
