<?php

namespace App\Services\Dashboard;

use App\Helpers\TerrafundDashboardQueryHelper;
use Illuminate\Http\Request;

class RunTotalHeaderService
{
    public function runTotalHeaderJob(Request $request)
    {
        $projects = $this->getProjectsData($request);

        return (object)[
            'total_non_profit_count' => $this->getTotalNonProfitCount($projects),
            'total_enterprise_count' => $this->getTotalEnterpriseCount($projects),
            'total_entries' => $this->getTotalJobsCreatedSum($projects),
            'total_hectares_restored' => round($this->getTotalHectaresSum($projects)),
            'total_hectares_restored_goal' => $projects->sum('total_hectares_restored_goal'),
            'total_trees_restored' => $this->getTotalTreesRestoredSum($projects),
            'total_trees_restored_goal' => $projects->sum('trees_grown_goal'),
        ];

        return $response;
    }

    private function getProjectsData(Request $request)
    {
        return TerrafundDashboardQueryHelper::buildQueryFromRequest($request)
            ->with(['organisation:id,type,name'])
            ->select([
                'v2_projects.id',
                'v2_projects.organisation_id',
                'v2_projects.total_hectares_restored_goal',
                'v2_projects.trees_grown_goal',
            ])
            ->get();
    }

    public function getTotalNonProfitCount($projects)
    {
        return $projects->where('organisation.type', 'non-profit-organization')->count();
    }

    public function getTotalEnterpriseCount($projects)
    {
        return $projects->where('organisation.type', 'for-profit-organization')->count();
    }

    public function getTotalJobsCreatedSum($projects)
    {
        return $projects->sum('total_approved_jobs_created');
    }

    public function getTotalHectaresSum($projects)
    {
        return $projects->sum('total_hectares_restored_sum');
    }

    public function getTotalTreesRestoredSum($projects)
    {
        return $projects->sum('approved_trees_planted_count');
    }
}
