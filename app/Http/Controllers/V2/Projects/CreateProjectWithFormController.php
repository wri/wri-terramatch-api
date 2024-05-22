<?php

namespace App\Http\Controllers\V2\Projects;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Entities\EntityWithSchemaResource;
use App\Models\V2\Forms\Application;
use App\Models\V2\Forms\Form;
use App\Models\V2\Projects\Project;
use App\Models\V2\TreeSpecies\TreeSpecies;
use App\StateMachines\EntityStatusStateMachine;
use Illuminate\Http\Request;

class CreateProjectWithFormController extends Controller
{
    public function __invoke(Request $request): EntityWithSchemaResource
    {
        $this->authorize('create', Project::class);
        $data = $request->validate([
            'parent_entity' => 'required|in:application',
            'parent_uuid' => 'required|exists:applications,uuid',
            'form_uuid' => 'required|exists:forms,uuid',
        ]);

        $form = $this->getForm($data['form_uuid']);

        $application = Application::where('uuid', $data['parent_uuid'])->firstOrFail();

        $formSubmission = $application->formSubmissions->first();

        $projectPitch = $formSubmission->projectPitch;

        $project = Project::create([
            'framework_key' => $form ? $form->framework_key : 'terrafund',
            'organisation_id' => $application->organisation->id,
            'application_id' => $application->id,
            'status' => EntityStatusStateMachine::STARTED,
            'project_status' => null,
            'name' => $projectPitch->project_name,
            'boundary_geojson' => $projectPitch->proj_boundary,
            'land_use_types' => $projectPitch->land_use_types ?? $projectPitch->land_systems,
            'restoration_strategy' => $projectPitch->restoration_strategy ?? $projectPitch->tree_restoration_practices,
            'country' => $projectPitch->project_country,
            'continent' => null,
            'planting_start_date' => $projectPitch->expected_active_restoration_start_date,
            'planting_end_date' => $projectPitch->expected_active_restoration_end_date,
            'description' => $projectPitch->description_of_project_timeline,
            'history' => $projectPitch->curr_land_degradation,
            'objectives' => $projectPitch->project_objectives,
            'environmental_goals' => $projectPitch->environmental_goals,
            'socioeconomic_goals' => $projectPitch->proj_impact_socieconom,
            'sdgs_impacted' => null,
            'long_term_growth' => null,
            'community_incentives' => null,
            'budget' => $projectPitch->project_budget,
            'jobs_created_goal' => $projectPitch->num_jobs_created,
            'total_hectares_restored_goal' => $projectPitch->total_hectares,
            'trees_grown_goal' => $projectPitch->total_trees,
            'survival_rate' => null,
            'year_five_crown_cover' => null,
            'monitored_tree_cover' => null,
            'organization_name' => null,
            'project_county_district' => $projectPitch->project_county_district,
            'description_of_project_timeline' => $projectPitch->description_of_project_timeline,
            'siting_strategy_description' => $projectPitch->siting_strategy_description,
            'siting_strategy' => $projectPitch->siting_strategy,
            'landholder_comm_engage' => $projectPitch->landholder_comm_engage,
            'proj_partner_info' => $projectPitch->proj_partner_info,
            'proj_success_risks' => $projectPitch->proj_success_risks,
            'seedlings_source' => $projectPitch->seedlings_source,
            'pct_employees_men' => $projectPitch->pct_employees_men,
            'pct_employees_women' => $projectPitch->pct_employees_women,
            'pct_employees_18to35' => $projectPitch->pct_employees_18to35,
            'pct_employees_older35' => $projectPitch->pct_employees_older35,
            'proj_beneficiaries' => $projectPitch->proj_beneficiaries,
            'pct_beneficiaries_women' => $projectPitch->pct_beneficiaries_women,
            'pct_beneficiaries_small' => $projectPitch->pct_beneficiaries_small,
            'pct_beneficiaries_large' => $projectPitch->pct_beneficiaries_large,
            'pct_beneficiaries_youth' => $projectPitch->pct_beneficiaries_youth,
            'land_tenure_project_area' => $projectPitch->land_tenure_proj_area,
            'detailed_project_budget' => $projectPitch->detailed_project_budget,
            'proof_of_land_tenure_mou' => $projectPitch->proof_of_land_tenure_mou,
            'detailed_intervention_types' => $projectPitch->detailed_intervention_types,
            'proj_impact_foodsec' => $projectPitch->proj_impact_foodsec,
            'pct_employees_marginalised' => $projectPitch->pct_employees_marginalised,
            'pct_beneficiaries_marginalised' => $projectPitch->pct_beneficiaries_marginalised,
            'pct_beneficiaries_men' => $projectPitch->pct_beneficiaries_men,
            'proposed_gov_partners' => $projectPitch->proposed_gov_partners,
            'proposed_num_nurseries' => $projectPitch->proposed_num_nurseries,
            'proj_boundary' => $projectPitch->proj_boundary,
            'states' => $projectPitch->states,
            'proj_impact_biodiv' => $projectPitch->biodiversity_impact,
        ]);

        foreach ($projectPitch->treeSpecies()->get() as $treeSpecies) {
            $project->treeSpecies()->create([
                'collection' => $treeSpecies->collection ?? TreeSpecies::COLLECTION_PRIMARY,
                'amount' => $treeSpecies->amount,
            ]);
        }

        $request->user()->projects()->sync([$project->id => ['is_monitoring' => false]], false);
        $project->dispatchStatusChangeEvent($request->user());

        return $project->createSchemaResource();
    }

    private function getForm(string $form_uuid): Form
    {
        return Form::where('uuid', $form_uuid)
            ->where('model', Project::class)
            ->first();
    }
}
