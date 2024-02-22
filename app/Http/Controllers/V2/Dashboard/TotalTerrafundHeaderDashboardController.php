<?php

namespace App\Http\Controllers\V2\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\SiteReport;
use App\Models\V2\TreeSpecies\TreeSpecies;
use Illuminate\Support\Facades\DB;

class TotalTerrafundHeaderDashboardController extends Controller
{
    public function __invoke()
    {
        return response()->json([
            'total_non_profit_count' => $this->getTotalNonProfitCount(),
            'total_enterprise_count' => $this->getTotalEnterpriseCount(),
            'total_entries' => $this->getTotalJobsCreatedSum(),
            'total_hectares_retored' => $this->getTotalHectaresRestoredGoalSum(),
            'total_trees_restored' => $this->getTotalTreesRestoredSum(),
            'total_trees_restored_goal' => $this->getTotalTreesGrownGoalSum(),
        ]);
    }

    public function getTotalNonProfitCount()
    {
        return Project::where('framework_key', 'terrafund')
            ->whereHas('organisation', function ($query) {
                $query->where('type', 'non-profit-organization');
            })
            ->count();
    }

    public function getTotalEnterpriseCount()
    {
        return Project::where('framework_key', 'terrafund')
            ->whereHas('organisation', function ($query) {
                $query->where('type', 'for-profit-organization');
            })
            ->count();
    }

    public function getTotalJobsCreatedSum()
    {
        $total = ProjectReport::whereNull('deleted_at')
        ->sum(DB::raw('pt_total + ft_total'));

        return intval($total);
    }

    public function getTotalHectaresRestoredGoalSum()
    {
        $total = Project::sum('total_hectares_restored_goal');

        return intval($total);
    }

    public function getTotalTreesRestoredSum()
    {
        $projects = Project::where('framework_key', 'terrafund')->get();

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

    public function getTotalTreesGrownGoalSum()
    {
        $total = Project::sum('trees_grown_goal');

        return intval($total);
    }
}
