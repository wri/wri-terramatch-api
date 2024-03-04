<?php

namespace App\Http\Controllers\V2\Dashboard;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\V2\Projects\Project;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\V2\Sites\Site;
use App\Helpers\TerrafundDashboardQueryHelper;

class ViewRestorationStrategyController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $query = $this->buildProjectQuery($request);

        $projectIds = $query->pluck('v2_projects.id')->toArray();

        $restorationStrategy = $this->getRestorationStrategy($projectIds);

        $landUseType = $this->getLandUseType($projectIds);

        $result = [
            'restorationStrategies' => $this->getResultArray($restorationStrategy, 'strategy'),
            'landUseTypes' => $this->getResultArray($landUseType, 'land_use')
        ];

        return new JsonResponse($result);
    }

    private function buildProjectQuery(Request $request)
    {
        $query = Project::join('organisations', 'v2_projects.organisation_id', '=', 'organisations.id')
            ->where('v2_projects.framework_key', '=', 'terrafund');

        $query = TerrafundDashboardQueryHelper::buildQueryFromRequest($query, $request);

        return $query;
    }

    private function getRestorationStrategy(array $projectIds)
    {
        $strategies = ['direct-seeding', 'tree-planting', 'assisted-natural-regeneration'];
    
        $conditions = implode(' OR ', array_map(function ($strategy) {
            return "JSON_UNQUOTE(JSON_EXTRACT(restoration_strategy, CONCAT('\$[', numbers.n, ']'))) = '$strategy'";
        }, $strategies));
    
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
        $landUseTypes = ['agroforest', 'open-natural-ecosystem', 'mangrove', 'natural-forest', 'peatland', 'riparian-area-or-wetland', 'silvopasture', 'urban-forest', 'woodlot-or-plantation'];
    
        $conditions = implode(' OR ', array_map(function ($type) {
            return "JSON_UNQUOTE(JSON_EXTRACT(v2_sites.land_use_types, CONCAT('\$[', numbers.n, ']'))) = '$type'";
        }, $landUseTypes));
    
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

    private function getResultArray($data, $key)
    {
        return collect($data)->pluck('count_per_project', $key)->toArray();
    }
}
