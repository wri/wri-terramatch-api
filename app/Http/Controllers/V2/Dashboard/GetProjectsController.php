<?php

namespace App\Http\Controllers\V2\Dashboard;

use App\Helpers\TerrafundDashboardQueryHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Dashboard\GetProjectsResource;
use Illuminate\Http\Request;

class GetProjectsController extends Controller
{
    public function __invoke(Request $request): GetProjectsResource
    {
        $projects = TerrafundDashboardQueryHelper::buildQueryFromRequest($request)
            ->select('id', 'long', 'lat', 'name')
            ->get()
            ->map(function ($project) {
                $project->lat = round($project->lat, 2);
                $project->long = round($project->long, 2);
                return $project;
            });
        return new GetProjectsResource([
            'data' => $projects,
        ]);
    }
};
