<?php

namespace App\Http\Controllers\V2\Dashboard;

use App\Helpers\TerrafundDashboardQueryHelper;
use App\Http\Controllers\Controller;
use App\Models\V2\Projects\Project;
use App\Models\V2\Sites\SiteReport;
use App\Models\V2\TreeSpecies\TreeSpecies;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class TopProjectsAndTopTreeSpeciesController extends Controller
{
    public function __invoke(Request $request)
    {
        return response()->json([
            'top_projects_most_planted_trees' => $this->getTopProjects($request),
            'top_tree_species_planted' => $this->getTopTreeSpecies($request),
        ]);
    }

    public function getTopProjects($request)
    {
        $query = Project::query();
        $query = $query->whereHas('organisation', function ($query) {
            $query->whereIn('type', ['for-profit-organization', 'non-profit-organization']);
        });
        $projects = TerrafundDashboardQueryHelper::buildQueryFromRequest($query, $request)->get();
        $topProjects = [];
        foreach ($projects as $project) {
            $sites = $project->sites()->pluck('id')->toArray();
            $totalSpeciesAmountForSiteReport = 0;
            foreach ($sites as $siteId) {
                $latestSiteReportId = SiteReport::where('site_id', $siteId)
                    ->orderByDesc('due_at')
                    ->value('id');
                if ($latestSiteReportId !== null) {
                    $totalSpeciesAmountForSiteReport += TreeSpecies::where('speciesable_id', $latestSiteReportId)->sum('amount');
                }
            }
            $topProjects[] = [
                'project' => $project->name,
                'trees_planted' => $totalSpeciesAmountForSiteReport,
            ];
        }

        return collect($topProjects)->sortByDesc('trees_planted')->take(10)->values()->all();
    }

    public function getTopTreeSpecies($request)
    {
        $query = Project::query();
        $query = $query->whereHas('organisation', function ($query) {
            $query->whereIn('type', ['for-profit-organization', 'non-profit-organization']);
        });
        $projects = TerrafundDashboardQueryHelper::buildQueryFromRequest($query, $request)->get();
        $topSpecies = [];
        foreach ($projects as $project) {
            $sites = $project->sites()->pluck('id')->toArray();
            foreach ($sites as $siteId) {
                $latestSiteReportId = SiteReport::where('site_id', $siteId)
                    ->orderByDesc('due_at')
                    ->value('id');
                $treesSpecies = TreeSpecies::where('speciesable_id', $latestSiteReportId)->get();
                foreach ($treesSpecies as $species) {
                    $topSpecies[] = [
                        'name' => $species->name,
                        'amount' => $species->amount,
                    ];
                }
            }
        }
        $speciesCollection = new Collection($topSpecies);
        $speciesGrouped = $speciesCollection->groupBy('name');

        $sumSpeciesValues = $speciesGrouped->map(function ($species, $name) {
            return ['name' => $name, 'amount' => $species->sum('amount')];
        });

        return $sumSpeciesValues->sortByDesc('amount')->take(20)->values()->all();
    }
}
