<?php

namespace App\Http\Controllers\V2\Dashboard;

use App\Helpers\TerrafundDashboardQueryHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TotalTerrafundHeaderDashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $projects = TerrafundDashboardQueryHelper::buildQueryFromRequest($request)->get();

        return response()->json([
            'total_non_profit_count' => $this->getTotalNonProfitCount($projects),
            'total_enterprise_count' => $this->getTotalEnterpriseCount($projects),
            'total_entries' => $this->getTotalJobsCreatedSum($projects),
            'total_hectares_retored' => $this->getTotalHectaresRestoredGoalSum($projects),
            'total_trees_restored' => $this->getTotalTreesRestoredSum($projects),
            'total_trees_restored_goal' => $this->getTotalTreesGrownGoalSum($projects),
        ]);
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
            return $project->reports()->sum(DB::raw('pt_total + ft_total'));
        });
    }

    public function getTotalHectaresRestoredGoalSum($projects)
    {
        return $projects->sum('total_hectares_restored_goal');
    }

    public function getTotalTreesRestoredSum($projects)
    {
        return $projects->sum(function ($project) {
            return $project->sites()->get()->sum(function ($site) {
                $latestReport = $site->reports()->orderByDesc('due_at')->first();
                if ($latestReport) {
                    return $latestReport->treeSpecies()->sum('amount');
                } else {
                    return 0;
                }
            });
        });
    }

    public function getTotalTreesGrownGoalSum($projects)
    {
        return $projects->sum('trees_grown_goal');
    }
}
