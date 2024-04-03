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

        $projects->each((function ($project) use (&$topProjects) {
            $totalSpeciesAmountForSiteReport = $project->sites()->with(['reports.treeSpecies'])->get()->sum(function ($site) {
                return $site->reports->sum(function ($report) {
                    return $report->treeSpecies->sum('amount');
                });
            });
            $topProjects[] = [
                'project' => $project->name,
                'uuid' => $project->uuid,
                'trees_planted' => $totalSpeciesAmountForSiteReport,
            ];
        }));

        return collect($topProjects)->sortByDesc('trees_planted')->take(10)->values()->all();
    }

    public function getTopTreeSpecies($projects)
    {

        $topSpecies = [];

        $projects->each(function ($project) use (&$topSpecies) {
            $project->sites()->with(['reports.treeSpecies'])->get()->each(function ($site) use (&$topSpecies) {
                $site->reports->each(function ($report) use (&$topSpecies) {
                    $report->treeSpecies->each(function ($species) use (&$topSpecies) {
                        $topSpecies[] = [
                            'name' => $species->name,
                            'amount' => $species->amount,
                        ];
                    });
                });
            });
        });
        $speciesCollection = new Collection($topSpecies);
        $speciesGrouped = $speciesCollection->groupBy('name');

        $sumSpeciesValues = $speciesGrouped->map(function ($species, $name) {
            return ['name' => $name, 'amount' => $species->sum('amount')];
        });

        return $sumSpeciesValues->sortByDesc('amount')->take(20)->values()->all();
    }
}
