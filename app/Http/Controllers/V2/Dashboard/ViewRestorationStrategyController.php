<?php

namespace App\Http\Controllers\V2\Dashboard;

use App\Helpers\TerrafundDashboardQueryHelper;
use App\Http\Controllers\Controller;
use App\Models\V2\Sites\Site;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ViewRestorationStrategyController extends Controller
{
    protected const RESTORATION_STRATEGIES = ['direct-seeding', 'tree-planting', 'assisted-natural-regeneration'];
    protected const LAND_USE_TYPES = ['agroforest', 'open-natural-ecosystem', 'mangrove', 'natural-forest', 'peatland', 'riparian-area-or-wetland', 'silvopasture', 'urban-forest', 'woodlot-or-plantation'];
    protected const LAND_TENURES = ['private', 'public', 'indigenous', 'other', 'national_protected_area', 'communal'];

    public function __invoke(Request $request): JsonResponse
    {
        $query = $this->buildProjectQuery($request);

        $projectIds = $query->pluck('v2_projects.id')->toArray();

        $restorationStrategy = $this->getRestorationStrategy($projectIds);

        $landUseType = $this->getLandUseType($projectIds);

        $landTenures = $this->getLandTenures($projectIds);

        $result = [
            'restorationStrategies' => $this->getResultArray($restorationStrategy, 'strategy'),
            'landUseTypes' => $this->getResultArray($landUseType, 'land_use'),
            'landTenures' => $this->getResultArray($landTenures, 'land_tenure'),
        ];

        return response()->json($result);
    }

    private function buildProjectQuery(Request $request)
    {
        return TerrafundDashboardQueryHelper::buildQueryFromRequest($request);
    }

    private function getRestorationStrategy(array $projectIds)
    {
        if (empty($projectIds)) {
            return;
        }

        $conditions = implode(' OR ', array_map(function ($strategy) {
            return "JSON_UNQUOTE(JSON_EXTRACT(restoration_strategy, CONCAT('\$[', numbers.n, ']'))) = '$strategy'";
        }, self::RESTORATION_STRATEGIES));

        $numbers = implode(' UNION ALL ', array_map(function ($n) {
            return "SELECT $n AS n";
        }, range(0, 3)));

        return DB::table(DB::raw("(SELECT DISTINCT
            project_id,
            JSON_UNQUOTE(JSON_EXTRACT(restoration_strategy, CONCAT('\$[', numbers.n, ']'))) AS strategy
        FROM
            v2_sites
        CROSS JOIN
            ($numbers) numbers
        WHERE
            project_id IN (" . implode(',', $projectIds) . ")
            AND ($conditions)
        ) AS subquery"))
            ->groupBy('strategy')
            ->select('strategy', DB::raw('COUNT(*) as count_per_project'))
            ->get();
    }

    private function getLandUseType(array $projectIds)
    {
        if (empty($projectIds)) {
            return;
        }
        $conditions = implode(' OR ', array_map(function ($type) {
            return "JSON_UNQUOTE(JSON_EXTRACT(v2_sites.land_use_types, CONCAT('\$[', numbers.n, ']'))) = '$type'";
        }, self::LAND_USE_TYPES));

        $numbers = implode(' UNION ALL ', array_map(function ($n) {
            return "SELECT $n AS n";
        }, range(0, 4)));

        return Site::select('land_use', DB::raw('COUNT(DISTINCT v2_sites.project_id) as count_per_project'))
            ->join(DB::raw("(SELECT project_id,
                                JSON_UNQUOTE(JSON_EXTRACT(land_use_types, CONCAT('\$[', numbers.n, ']'))) AS land_use
                            FROM v2_sites
                            CROSS JOIN
                                ($numbers) numbers
                            WHERE
                                v2_sites.project_id IN (" . implode(',', $projectIds) . ")
                                AND ($conditions)
                        ) AS subquery"), function ($join) {
                $join->on('v2_sites.project_id', '=', 'subquery.project_id');
            })
            ->groupBy('land_use')
            ->get();
    }

    private function getLandTenures(array $projectIds)
    {
        if (empty($projectIds)) {
            return;
        }
        $conditions = implode(' OR ', array_map(function ($type) {
            return "JSON_UNQUOTE(JSON_EXTRACT(v2_sites.land_tenures, CONCAT('\$[', numbers.n, ']'))) = '$type'";
        }, self::LAND_TENURES));

        $numbers = implode(' UNION ALL ', array_map(function ($n) {
            return "SELECT $n AS n";
        }, range(0, 4)));

        return Site::select('land_tenure', DB::raw('COUNT(DISTINCT v2_sites.project_id) as count_per_project'))
            ->join(DB::raw("(SELECT project_id,
                                JSON_UNQUOTE(JSON_EXTRACT(land_tenures, CONCAT('\$[', numbers.n, ']'))) AS land_tenure
                            FROM v2_sites
                            CROSS JOIN
                                ($numbers) numbers
                            WHERE
                                v2_sites.project_id IN (" . implode(',', $projectIds) . ")
                                AND ($conditions)
                        ) AS subquery"), function ($join) {
                $join->on('v2_sites.project_id', '=', 'subquery.project_id');
            })
            ->groupBy('land_tenure')
            ->get();
    }

    private function getResultArray($data, $key)
    {
        return collect($data)->pluck('count_per_project', $key)->toArray();
    }
}
