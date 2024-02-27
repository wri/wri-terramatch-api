<?php

namespace App\Http\Controllers\V2\Dashboard;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\V2\Projects\Project;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\V2\Sites\Site;

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

        if ($request->has('country')) {
            $country = $request->input('country');
            $query->where('country', $country);
        } elseif ($request->has('uuid')) {
            $projectUuid = $request->input('uuid');
            $query->where('v2_projects.uuid', $projectUuid);
        }

        return $query;
    }

    private function getRestorationStrategy(array $projectIds)
    {
        return DB::table(DB::raw("(SELECT DISTINCT
            project_id,
            JSON_UNQUOTE(JSON_EXTRACT(restoration_strategy, CONCAT('\$[', numbers.n, ']'))) AS strategy
        FROM
            v2_sites
        CROSS JOIN
            (
                SELECT 0 AS n UNION ALL
                SELECT 1 UNION ALL
                SELECT 2 UNION ALL
                SELECT 3
                -- Add more numbers if needed based on the maximum number of strategies per site
            ) numbers
        WHERE
            project_id IN (" . implode(',', $projectIds) . ")
            AND (
                JSON_UNQUOTE(JSON_EXTRACT(restoration_strategy, CONCAT('\$[', numbers.n, ']'))) = 'direct-seeding'
                OR JSON_UNQUOTE(JSON_EXTRACT(restoration_strategy, CONCAT('\$[', numbers.n, ']'))) = 'tree-planting'
                OR JSON_UNQUOTE(JSON_EXTRACT(restoration_strategy, CONCAT('\$[', numbers.n, ']'))) = 'assisted-natural-regeneration'
            )
        ) AS subquery"))
            ->groupBy('strategy')
            ->select('strategy', DB::raw('COUNT(*) as count_per_project'))
            ->get();
    }

    private function getLandUseType(array $projectIds)
    {
        return Site::select('land_use', DB::raw('COUNT(DISTINCT v2_sites.project_id) as count_per_project'))
            ->join(DB::raw('(SELECT project_id,
                                JSON_UNQUOTE(JSON_EXTRACT(land_use_types, CONCAT(\'$[\', numbers.n, \']\'))) AS land_use
                            FROM v2_sites
                            CROSS JOIN
                                (SELECT 0 AS n UNION ALL
                                 SELECT 1 UNION ALL
                                 SELECT 2 UNION ALL
                                 SELECT 3 UNION ALL
                                 SELECT 4) numbers
                            WHERE
                                v2_sites.project_id IN (' . implode(',', $projectIds) . ')
                                AND (
                                    JSON_UNQUOTE(JSON_EXTRACT(v2_sites.land_use_types, CONCAT(\'$[\', numbers.n, \']\'))) = \'agroforest\'
                                    OR JSON_UNQUOTE(JSON_EXTRACT(v2_sites.land_use_types, CONCAT(\'$[\', numbers.n, \']\'))) = \'open-natural-ecosystem\'
                                    OR JSON_UNQUOTE(JSON_EXTRACT(v2_sites.land_use_types, CONCAT(\'$[\', numbers.n, \']\'))) = \'mangrove\'
                                    OR JSON_UNQUOTE(JSON_EXTRACT(v2_sites.land_use_types, CONCAT(\'$[\', numbers.n, \']\'))) = \'natural-forest\'
                                    OR JSON_UNQUOTE(JSON_EXTRACT(v2_sites.land_use_types, CONCAT(\'$[\', numbers.n, \']\'))) = \'peatland\'
                                    OR JSON_UNQUOTE(JSON_EXTRACT(v2_sites.land_use_types, CONCAT(\'$[\', numbers.n, \']\'))) = \'riparian-area-or-wetland\'
                                    OR JSON_UNQUOTE(JSON_EXTRACT(v2_sites.land_use_types, CONCAT(\'$[\', numbers.n, \']\'))) = \'silvopasture\'
                                    OR JSON_UNQUOTE(JSON_EXTRACT(v2_sites.land_use_types, CONCAT(\'$[\', numbers.n, \']\'))) = \'urban-forest\'
                                    OR JSON_UNQUOTE(JSON_EXTRACT(v2_sites.land_use_types, CONCAT(\'$[\', numbers.n, \']\'))) = \'woodlot-or-plantation\'
                                )
                        ) AS subquery'), function ($join) {
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
