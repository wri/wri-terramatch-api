<?php

namespace Tests\V2\Projects;

use App\Helpers\CustomFormHelper;
use App\Models\V2\Forms\Application;
use App\Models\V2\Forms\Form;
use App\Models\V2\Forms\FormSubmission;
use App\Models\V2\FundingProgramme;
use App\Models\V2\ProjectPitch;
use App\Models\V2\Projects\Project;
use App\Models\V2\User;
use App\StateMachines\EntityStatusStateMachine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CreateProjectWithFormControllerTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    /**
     * @dataProvider permissionsDataProvider
     */
    public function test_a_project_developer_can_create_a_project_from_a_given_form(string $fmKey)
    {
        list($fundingProgramme, $application, $organisation, $projectPitch, $formSubmissions, $form) = $this->prepareData($fmKey);

        $owner = User::factory()->create(['organisation_id' => $organisation->id]);

        $form = $this->createAndReturnForm($fmKey);
        $payload = [
            'parent_entity' => 'application',
            'parent_uuid' => $application->uuid,
            'form_uuid' => $form->uuid,
        ];

        $uri = '/api/v2/forms/projects';

        $response = $this->actingAs($owner)
            ->postJson($uri, $payload)
            ->assertSuccessful();

        $body = json_decode($response->getContent());

        $project = Project::where('uuid', $body->data->uuid)->first();

        $projectAssertions = [
            'framework_key' => $form->framework_key,
            'organisation_id' => $application->organisation->id,
            'application_id' => $application->id,
            'status' => EntityStatusStateMachine::STARTED,
            'project_status' => null,
            'name' => $projectPitch->project_name,
            'boundary_geojson' => $projectPitch->proj_boundary,
            'land_use_types' => null,
            'restoration_strategy' => $projectPitch->restoration_strategy,
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
        ];

        foreach ($projectAssertions as $property => $expectedValue) {
            $this->assertEquals($expectedValue, $project->{$property}, $property);
        }
    }

    /**
     * @dataProvider permissionsDataProvider
     */
    public function test_an_unauthorized_user_cant_create_a_project_from_a_given_form(string $fmKey)
    {
        list($fundingProgramme, $application, $organisation, $projectPitch, $formSubmissions, $form) = $this->prepareData($fmKey);

        $payload = [
            'parent_entity' => 'application',
            'parent_uuid' => $application->uuid,
        ];

        $uri = '/api/v2/forms/projects';

        $users = [
            User::factory()->admin()->create(['organisation_id' => $organisation->id]),
            User::factory()->terrafundAdmin()->create(['organisation_id' => $organisation->id]),
            User::factory()->admin()->create([]),
            User::factory()->terrafundAdmin()->create([]),
        ];

        foreach ($users as $user) {
            $this->actingAs($user)
                ->postJson($uri, $payload)
                ->assertStatus(403);
        }
    }

    private function prepareData(string $fmKey)
    {
        $fundingProgramme = FundingProgramme::factory()->create();
        $application = Application::factory()->create([
            'funding_programme_uuid' => $fundingProgramme->uuid,
        ]);
        $organisation = $application->organisation;
        $projectPitch = ProjectPitch::factory()->create([
            'funding_programme_id' => $fundingProgramme->uuid,
            'organisation_id' => $organisation->uuid,
        ]);
        $formSubmissions = FormSubmission::factory()->create([
            'project_pitch_uuid' => $projectPitch->uuid,
            'application_id' => $application->id,
            'organisation_uuid' => $organisation->uuid,
        ]);

        $form = $this->createAndReturnForm($fmKey);

        return [
            $fundingProgramme,
            $application,
            $organisation,
            $projectPitch,
            $formSubmissions,
            $form,
        ];
    }

    private function createAndReturnForm(string $fmKey): Form
    {
        $form = CustomFormHelper::generateFakeForm('project', $fmKey);

        $answers = [];

        foreach ($form->sections()->first()->questions as $question) {
            if ($question->linked_field_key == 'pro-name') {
                $answers[$question->uuid] = '* testing name updated *';
            }
        }

        return $form;
    }

    public static function permissionsDataProvider()
    {
        return [
            ['terrafund'],
        ];
    }
}
