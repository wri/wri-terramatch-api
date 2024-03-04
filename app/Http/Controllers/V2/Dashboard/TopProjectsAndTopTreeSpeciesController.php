<?php

namespace App\Http\Controllers\V2\Dashboard;

use App\Helpers\TerrafundDashboardQueryHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Dashboard\TopProjectsAndTopTreeSpeciesResource;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class TopProjectsAndTopTreeSpeciesController extends Controller
{
    public function __invoke(Request $request): TopProjectsAndTopTreeSpeciesResource
    {
        $projects = TerrafundDashboardQueryHelper::buildQueryFromRequest($request)->get();

        $response = (object) [
            'top_projects_most_planted_trees' => $this->getTopProjects($projects),
            'top_tree_species_planted' => $this->getTopTreeSpecies($projects),
        ];

        return new TopProjectsAndTopTreeSpeciesResource($response);
    }

    public function getTopProjects($projects)
    {

        $topProjects = [];
        $totalSpeciesAmountForSiteReport = 0;
        foreach($projects as $project) {
            $totalSpeciesAmountForSiteReport = $project->sites()->get()->sum(function ($site) {
                $latestReport = $site->reports()->orderByDesc('due_at')->first();
                if ($latestReport) {
                    return $latestReport->treeSpecies()->sum('amount');
                }
            });
            $topProjects[] = [
                'project' => $project->name,
                'trees_planted' => $totalSpeciesAmountForSiteReport,
            ];
        }

        return collect($topProjects)->sortByDesc('trees_planted')->take(10)->values()->all();
    }

    public function getTopTreeSpecies($projects)
    {

        $topSpecies = [];
        foreach($projects as $project) {
            $sites = $project->sites()->get();
            foreach ($sites as $site) {
                $latestReport = $site->reports()->orderByDesc('due_at')->first();
                if ($latestReport) {
                    $treesSpecies = $latestReport->treeSpecies()->get();
                    foreach ($treesSpecies as $species) {
                        $topSpecies[] = [
                            'name' => $species->name,
                            'amount' => $species->amount,
                        ];
                    }
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
