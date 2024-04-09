<?php

namespace App\Http\Controllers\V2\Dashboard;

use App\Helpers\TerrafundDashboardQueryHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Dashboard\TotalSectionHeaderResource;
use Illuminate\Http\Request;

class TotalTerrafundHeaderDashboardController extends Controller
{
    public function __invoke(Request $request): TotalSectionHeaderResource
    {
        $projects = TerrafundDashboardQueryHelper::buildQueryFromRequest($request)->get();

        $response = (object)[
            'total_non_profit_count' => $this->getTotalNonProfitCount($projects),
            'total_enterprise_count' => $this->getTotalEnterpriseCount($projects),
            'total_entries' => $this->getTotalJobsCreatedSum($projects),
            'total_hectares_restored' => "-",
            'total_hectares_restored_goal' => $this->getTotalHectaresRestoredGoalSum($projects),
            'total_trees_restored' => $this->getTotalTreesRestoredSum($projects),
            'total_trees_restored_goal' => $this->getTotalTreesGrownGoalSum($projects),
        ];

        return new TotalSectionHeaderResource($response);
    }

    public function getTotalNonProfitCount($projects)
    {
        $projects = $projects->filter(function ($project) {
            return $project->organisation->type === 'non-profit-organization';
        });

        return $projects->count();
    }

    public function getTotalEnterpriseCount($projects)
    {
        $projects = $projects->filter(function ($project) {
            return $project->organisation->type === 'for-profit-organization';
        });

        return $projects->count();
    }

    public function getTotalJobsCreatedSum($projects)
    {
        return $projects->sum(function ($project) {
            $totalSum = $project->reports()->selectRaw('SUM(ft_total) as total_ft, SUM(pt_total) as total_pt')->first();
            return $totalSum->total_ft + $totalSum->total_pt;
        });
    }

    public function getTotalHectaresRestoredGoalSum($projects)
    {
        return $projects->sum('total_hectares_restored_goal');
    }

    public function getTotalTreesRestoredSum($projects)
    {
        return $projects->sum(function ($project) {
            return $project->sites()->with(['reports.treeSpecies'])->get()->sum(function ($site) {
                return $site->reports->sum(function ($report) {
                    return $report->treeSpecies->sum('amount');
                });
            });
        });
    }

    public function getTotalTreesGrownGoalSum($projects)
    {
        return $projects->sum('trees_grown_goal');
    }
}
