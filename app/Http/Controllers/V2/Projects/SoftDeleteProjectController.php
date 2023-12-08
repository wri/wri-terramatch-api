<?php

namespace App\Http\Controllers\V2\Projects;

use App\Http\Controllers\Controller;
use App\Models\V2\Projects\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SoftDeleteProjectController extends Controller
{
    public function __invoke(Request $request, Project $project): JsonResponse
    {
        $this->authorize('delete', $project);

        if ($project->status != Project::STATUS_STARTED) {
            return new JsonResponse('Only started / draft projects can be deleted.', 200);
        }

        $project->delete();

        return new JsonResponse('Project succesfully deleted', 200);
    }
}
