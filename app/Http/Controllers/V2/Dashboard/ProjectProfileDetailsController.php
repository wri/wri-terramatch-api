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
            $response = (object)[
                'name' => $project->name,
                'descriptionObjetive' => $project->objectives,
                'country' => $this->getCountry($project->country),
                'countrySlug' => $project->country,
                'organisation' => $project->organisation->type,
                'survivalRate' => $project->survival_rate,
                'restorationStrategy' => $project->restoration_strategy,
                'targetLandUse' => $project->land_use_types,
                'landTenure' => $project->land_tenure_project_area,
            ];

            return new ProjectProfileDetailsResource($response);
        }

    }

    public function getCountry($slug)
    {
        return FormOptionListOption::where('slug', $slug)->value('label');
    }
}
