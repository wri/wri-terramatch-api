<?php

namespace Projects;

use App\Helpers\CustomFormHelper;
use App\Models\User;
use App\Models\V2\Forms\Application;
use App\Models\V2\Forms\Form;
use App\Models\V2\Forms\FormSubmission;
use App\Models\V2\FundingProgramme;
use App\Models\V2\ProjectPitch;
use App\Models\V2\Projects\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class CreateBlankProjectWithFormControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * @dataProvider permissionsDataProvider
     */
    public function test_a_project_developer_can_create_a_blank_project_from_a_given_form(string $permission, string $fmKey)
    {
        //        Artisan::call('v2migration:roles --fresh');

        list($fundingProgramme, $application, $organisation, $projectPitch, $formSubmissions, $form) = $this->prepareData($fmKey);

        $owner = User::factory()->create(['organisation_id' => $organisation->id]);
        $owner->givePermissionTo('manage-own');

        $form = $this->createAndReturnForm($fmKey);

        $uri = '/api/v2/forms/projects/' . $form->uuid;

        $response = $this->actingAs($owner)
            ->postJson($uri, [])
            ->assertSuccessful();

        $body = json_decode($response->getContent());

        $project = Project::where('uuid', $body->data->uuid)->first();

        $projectAssertions = [
            'framework_key' => $form->framework_key,
            'organisation_id' => $organisation->id,
            'application_id' => null,
            'status' => Project::STATUS_STARTED,
            'project_status' => null,
            'name' => null,
            'boundary_geojson' => null,
            'land_use_types' => null,
            'restoration_strategy' => null,
            'country' => null,
            'continent' => null,
            'planting_start_date' => null,
            'planting_end_date' => null,
            'description' => null,
            'history' => null,
            'objectives' => null,
            'environmental_goals' => null,
            'socioeconomic_goals' => null,
            'sdgs_impacted' => null,
            'long_term_growth' => null,
            'community_incentives' => null,
            'budget' => null,
            'jobs_created_goal' => null,
            'total_hectares_restored_goal' => null,
            'trees_grown_goal' => null,
            'survival_rate' => null,
            'year_five_crown_cover' => null,
            'monitored_tree_cover' => null,
        ];

        foreach ($projectAssertions as $property => $expectedValue) {
            $this->assertEquals($expectedValue, $project->{$property}, $property);
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
            ['framework-terrafund', 'terrafund'],
            ['framework-ppc', 'ppc'],
        ];
    }
}
