<?php

namespace App\Http\Controllers\V2\Indicators;

use App\Helpers\TerrafundDashboardQueryHelper;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GetHectaresRestoredController extends Controller
{
    public function __invoke(Request $request)
    {
        try {
            $projectsToQuery = TerrafundDashboardQueryHelper::buildQueryFromRequest($request)->pluck('uuid')->toArray();
            $HECTAREAS_BY_RESTORATION = 'restorationByStrategy';
            $HECTAREAS_BY_TARGET_LAND_USE_TYPES = 'restorationByLandUse';

            $projectsPolygons = $this->getProjectsPolygons($projectsToQuery);
            $polygonsUuids = array_column($projectsPolygons, 'uuid');

            $restorationStrategiesRepresented = $this->polygonToOutputHectares($HECTAREAS_BY_RESTORATION, $polygonsUuids);
            $targetLandUseTypesRepresented = $this->polygonToOutputHectares($HECTAREAS_BY_TARGET_LAND_USE_TYPES, $polygonsUuids);

            if (empty($restorationStrategiesRepresented) && empty($targetLandUseTypesRepresented)) {
                return response()->json([
                    'restoration_strategies_represented' => [],
                    'target_land_use_types_represented' => [],
                    'message' => 'No data available for restoration strategies and target land use types.',
                ]);
            }

            return response()->json([
                'restoration_strategies_represented' => $this->calculateGroupedHectares($restorationStrategiesRepresented),
                'target_land_use_types_represented' => $this->calculateGroupedHectares($targetLandUseTypesRepresented),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'An error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getProjectsPolygons($projects)
    {
        if (count($projects) === 0) {
            return [];
        }

        return DB::select('
                SELECT sp.uuid
                FROM site_polygon sp
                INNER JOIN v2_sites s ON sp.site_id = s.uuid
                INNER JOIN v2_projects p ON s.project_id = p.id
                WHERE p.uuid IN ('. implode(',', array_fill(0, count($projects), '?')) .')
            ', $projects);
    }

    public function polygonToOutputHectares($indicatorId, $polygonsUuids)
    {
        if (count($polygonsUuids) === 0) {
            return [];
        }
        return DB::select('
                SELECT *
                FROM indicator_output_hectares
                WHERE indicator_slug = ?
                AND polygon_id IN (' . implode(',', array_fill(0, count($polygonsUuids), '?')) . ')
            ', array_merge([$indicatorId], $polygonsUuids));
    }

    public function calculateGroupedHectares($polygonsToOutputHectares)
    {
        $hectaresRestored = [];

        foreach ($polygonsToOutputHectares as $hectare) {
            $decodedValue = json_decode($hectare->value, true);

            if ($decodedValue) {
                foreach ($decodedValue as $key => $value) {
                    if (! isset($hectaresRestored[$key])) {
                        $hectaresRestored[$key] = 0;
                    }
                    $hectaresRestored[$key] += $value;
                }
            }
        }

        foreach ($hectaresRestored as $key => $value) {
            $hectaresRestored[$key] = round($value, 3);
        }

        return $hectaresRestored;
    }
}
