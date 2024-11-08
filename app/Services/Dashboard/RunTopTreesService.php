<?php

namespace App\Services\Dashboard;

use App\Helpers\TerrafundDashboardQueryHelper;
use Illuminate\Http\Request;

class RunTopTreesService
{
    public function runTopTreesJob(Request $request)
    {
        $projects = TerrafundDashboardQueryHelper::buildQueryFromRequest($request)->get();

        return (object) [
            'top_projects_most_planted_trees' => $this->getTopProjects($projects),
        ];
    }

    public function getTopProjects($projects)
    {

        $topProjects = [];

        $projects->each((function ($project) use (&$topProjects) {

            $topProjects[] = [
                'organization' => $project->organisation->name,
                'project' => $project->name,
                'uuid' => $project->uuid,
                'trees_planted' => $project->trees_planted_count,
            ];
        }));

        return collect($topProjects)->sortByDesc('trees_planted')->take(10)->values()->all();
    }
}
