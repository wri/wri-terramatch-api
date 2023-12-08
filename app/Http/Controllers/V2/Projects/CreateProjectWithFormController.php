<?php

namespace App\Http\Controllers\V2\Projects;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Projects\ProjectWithSchemaResource;
use App\Models\V2\Forms\Application;
use App\Models\V2\Forms\Form;
use App\Models\V2\Projects\Project;
use Illuminate\Http\Request;

class CreateProjectWithFormController extends Controller
{
    public function __invoke(Request $request): ProjectWithSchemaResource
    {
        $this->authorize('create', Project::class);

        $data = $request->validate([
            'parent_entity' => 'required|in:application',
            'parent_uuid' => 'required|exists:applications,uuid',
        ]);

        $form = $this->getForm();

        $application = Application::where('uuid', $data['parent_uuid'])->firstOrFail();

        $formSubmission = $application->formSubmissions->first();

        $projectPitch = $formSubmission->projectPitch;

        $project = Project::create([
            'framework_key' => 'terrafund',
            'organisation_id' => $application->organisation->id,
            'application_id' => $application->id,
            'status' => Project::STATUS_STARTED,
            'project_status' => null,
            'name' => $projectPitch->project_name,
            'boundary_geojson' => $projectPitch->proj_boundary,
            'land_use_types' => null,
            'restoration_strategy' => null,
            'country' => $projectPitch->project_country,
            'continent' => null,
            'planting_start_date' => $projectPitch->expected_active_restoration_start_date,
            'planting_end_date' => $projectPitch->expected_active_restoration_end_date,
            'description' => $projectPitch->expected_active_restoration_end_date,
            'history' => $projectPitch->description_of_project_timeline,
            'objectives' => $projectPitch->project_objectives,
            'environmental_goals' => $projectPitch->environmental_goals,
            'socioeconomic_goals' => null,
            'sdgs_impacted' => null,
            'long_term_growth' => null,
            'community_incentives' => null,
            'budget' => $projectPitch->project_budget,
            'jobs_created_goal' => null,
            'total_hectares_restored_goal' => null,
            'trees_grown_goal' => null,
            'survival_rate' => null,
            'year_five_crown_cover' => null,
            'monitored_tree_cover' => null,
        ]);

        return new ProjectWithSchemaResource($project, ['schema' => $form]);
    }

    private function getForm(): Form
    {
        return Form::where('framework_key', 'terrafund')
            ->where('model', Project::class)
            ->first();
    }
}
