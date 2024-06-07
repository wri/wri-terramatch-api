<?php

namespace App\Http\Controllers\V2\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\V2\Forms\FormOptionListOption;
use App\Models\V2\Projects\Project;
use Illuminate\Http\Request;

class ProjectProfileDetailsController extends Controller
{
    public function __invoke(Request $request, string $project = null)
    {
        $project = Project::where('uuid', $project)->first();
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

        return response()->json($response);
    }

    public function getCountry($slug)
    {
        return FormOptionListOption::where('slug', $slug)->value('label');
    }
}
