<?php

namespace App\Http\Controllers\V2\Indicators;

use App\Helpers\TerrafundDashboardQueryHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GetHectaresRestoredController extends Controller
{
    public function __invoke(Request $request)
    {
        $projectsToQuery = TerrafundDashboardQueryHelper::buildQueryFromRequest($request)->pluck('uuid')->toArray();
        
        $projectsPolygons = DB::select("
                SELECT sp.uuid
                FROM site_polygon sp
                INNER JOIN v2_sites s ON sp.site_id = s.uuid
                INNER JOIN v2_projects p ON s.project_id = p.id
                WHERE p.uuid IN (". implode(',', array_fill(0, count($projectsToQuery), '?')) .")
            ", $projectsToQuery);

        $polygonsUuids = array_map(function($polygon) {
            return $polygon->uuid;
        }, $projectsPolygons);
        
        $hectaresRestored = DB::select("
                SELECT *
                FROM indicator_output_hectares
                WHERE indicator_id = '5'
                AND polygon_id IN (" . implode(',', array_fill(0, count($polygonsUuids), '?')) . ")
            ", $polygonsUuids);

        $groupedValues = [];

        foreach ($hectaresRestored as $hectare) {
            $decodedValue = json_decode($hectare->value, true);

            if ($decodedValue) {
                foreach ($decodedValue as $key => $value) {
                    if (!isset($groupedValues[$key])) {
                        $groupedValues[$key] = 0;
                    }
                    $groupedValues[$key] += $value;
                }
            }
        }

        return response()->json($groupedValues);
    }
}
