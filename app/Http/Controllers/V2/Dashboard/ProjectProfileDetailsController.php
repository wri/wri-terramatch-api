<?php

namespace App\Http\Controllers\V2\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Dashboard\ProjectProfileDetailsResource;
use App\Models\V2\Forms\FormOptionListOption;
use App\Models\V2\Projects\Project;
use Illuminate\Http\Request;

class ProjectProfileDetailsController extends Controller
{
    public function __invoke(Request $request): ProjectProfileDetailsResource
    {
        if ($request->has('uuid')) {
            $project = Project::where('uuid', $request->input('uuid'))->first();
            return new ProjectProfileDetailsResource($project);
        }

    }
}
