<?php

namespace App\Http\Controllers\V2\Dashboard;

use App\Helpers\TerrafundDashboardQueryHelper;
use App\Http\Controllers\Controller;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\SiteReport;
use App\Models\V2\TreeSpecies\TreeSpecies;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TotalTerrafundHeaderDashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        return response()->json([
            'total_non_profit_count' => $this->getTotalNonProfitCount($request),
            'total_enterprise_count' => $this->getTotalEnterpriseCount($request),
            'total_entries' => $this->getTotalJobsCreatedSum($request),
            'total_hectares_retored' => $this->getTotalHectaresRestoredGoalSum($request),
            'total_trees_restored' => $this->getTotalTreesRestoredSum($request),
            'total_trees_restored_goal' => $this->getTotalTreesGrownGoalSum($request),
        ]);
    }

    public function getTotalNonProfitCount(Request $request)
    {
        $query = Project::query();
        $query = $query->whereHas('organisation', function ($query) {
            $query->where('type', 'non-profit-organization');
        });
        $projects = TerrafundDashboardQueryHelper::buildQueryFromRequest($query, $request);

        return $projects->count();
    }

    public function getTotalEnterpriseCount(Request $request)
    {
        $query = Project::query();
        $query = $query->whereHas('organisation', function ($query) {
            $query->where('type', 'for-profit-organization');
        });
        $projects = TerrafundDashboardQueryHelper::buildQueryFromRequest($query, $request);

        return $projects->count();
    }

    public function getTotalJobsCreatedSum(Request $request)
    {
        $query = Project::query();
        $query = TerrafundDashboardQueryHelper::buildQueryFromRequest($query, $request);
        $projects = $query->pluck('id')->toArray();
        $totalSum = 0;
        foreach ($projects as $projectId) {
            $reports = ProjectReport::where('project_id', $projectId)
                ->sum(DB::raw('pt_total + ft_total'));
            $totalSum += $reports;
        }

        return intval($totalSum);
    }

    public function getTotalHectaresRestoredGoalSum(Request $request)
    {
        $query = Project::query();
        $query = TerrafundDashboardQueryHelper::buildQueryFromRequest($query, $request);
        $total = $query->sum('total_hectares_restored_goal');

        return intval($total);
    }

    public function getTotalTreesRestoredSum(Request $request)
    {
        $query = Project::query();
        $query = TerrafundDashboardQueryHelper::buildQueryFromRequest($query, $request);
        $projects = $query->get();

        $totalSpeciesAmount = 0;

        foreach ($projects as $project) {
            $sites = $project->sites()->pluck('id')->toArray();
            foreach ($sites as $siteId) {
                $latestSiteReportId = SiteReport::where('site_id', $siteId)
                    ->orderByDesc('due_at')
                    ->value('id');
                if ($latestSiteReportId !== null) {
                    $totalSpeciesAmount += TreeSpecies::where('speciesable_id', $latestSiteReportId)->sum('amount');
                }
            }
        }

        return $totalSpeciesAmount;
    }

    public function getTotalTreesGrownGoalSum(Request $request)
    {
        $query = Project::query();
        $projects = TerrafundDashboardQueryHelper::buildQueryFromRequest($query, $request);
        $total = $projects->sum('trees_grown_goal');

        return intval($total);
    }
}
