<?php

namespace App\Http\Controllers\V2\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\V2\Projects\Project;
use Illuminate\Http\Request;
use App\Http\Resources\V2\Dashboard\ProjectProfileDetailsResource;

class ProjectProfileDetailsController extends Controller
{
    public function __invoke(Request $request, Project $project)
    {
        return new ProjectProfileDetailsResource($project);
    }
}
