<?php

namespace App\Http\Controllers\V2\Dashboard;

use App\Helpers\TerrafundDashboardQueryHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Dashboard\TopProjectsAndTopTreeSpeciesResource;
use App\Models\V2\Projects\Project;
use App\Models\V2\TreeSpecies\TreeSpecies;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            $totalSpeciesAmountForSiteReport = $project->trees_planted_count;

            $topProjects[] = [
                'organization' => $project->organisation->name,
                'project' => $project->name,
                'uuid' => $project->uuid,
                'trees_planted' => $totalSpeciesAmountForSiteReport,
            ];
        }));

        return collect($topProjects)->sortByDesc('trees_planted')->take(10)->values()->all();
    }

    public function getTopTreeSpecies($projects)
    {
        $speciesCollection = TreeSpecies::where('speciesable_type', Project::class)
            ->whereIn('speciesable_id', $projects->pluck('id'))
            ->groupBy(DB::raw('BINARY name'), 'name')
            ->selectRaw('sum(amount) as total, name')
            ->orderBy('total', 'desc')
            ->limit(20)
            ->get();

        return $speciesCollection;
    }
}
