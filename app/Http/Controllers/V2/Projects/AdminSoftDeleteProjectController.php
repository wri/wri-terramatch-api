<?php

namespace App\Http\Controllers\V2\Projects;

use App\Events\V2\General\EntityDeleteEvent;
use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Projects\ProjectResource;
use App\Models\V2\Projects\Project;
use Illuminate\Http\Request;

class AdminSoftDeleteProjectController extends Controller
{
    public function __invoke(Request $request, Project $project): ProjectResource
    {
        $this->authorize('delete', $project);

        $project->delete();

        EntityDeleteEvent::dispatch($request->user(), $project);

        return new ProjectResource($project);
    }
}
