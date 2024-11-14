<?php

namespace App\Http\Controllers\V2\Projects;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\Projects\AdminUpdateProjectRequest;
use App\Http\Resources\V2\Projects\ProjectResource;
use App\Models\V2\Projects\Project;

class AdminUpdateProjectController extends Controller
{
    public function __invoke(Project $project, AdminUpdateProjectRequest $request): ProjectResource
    {
        $this->authorize('update', $project);

        $project->update($request->validated());

        return new ProjectResource($project);
    }
}
