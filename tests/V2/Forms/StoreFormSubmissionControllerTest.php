<?php

namespace Tests\V2\Forms;

use App\Models\V2\Forms\Form;
use App\Models\V2\Forms\FormSubmission;
use App\Models\V2\Organisation;
use App\Models\V2\ProjectPitch;
use App\Models\V2\TreeSpecies\TreeSpecies;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

final class StoreFormSubmissionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoke_action(): void
    {
        $form = Form::first();
        if (empty($form)) {
            Artisan::call('v2-custom-form-update-data');
            $form = Form::first();
        }

        $organisation = Organisation::factory()->create();
        $projectPitch = ProjectPitch::factory()->create(['organisation_id' => $organisation->uuid]);
        $user = User::factory()->create(['organisation_id' => $organisation->id]);

        $this->actingAs($user)
            ->postJson('/api/v2/forms/submissions/', ['project_pitch_uuid' => $projectPitch->uuid, 'form_uuid' => $form->uuid])
            ->assertSuccessful();
    }

    public function test_invoke_action_without_a_project_pitch()
    {
        $form = Form::first();
        if (empty($form)) {
            Artisan::call('v2-custom-form-update-data');
            $form = Form::first();
        }

        $organisation = Organisation::factory()->create();
        $user = User::factory()->create(['organisation_id' => $organisation->id]);

        $this->actingAs($user)
            ->postJson('/api/v2/forms/submissions/', ['form_uuid' => $form->uuid])
            ->assertSuccessful();
    }

    public function test_lifecycle()
    {
        $form = Form::first();
        if (empty($form)) {
            Artisan::call('v2-custom-form-update-data');
            $form = Form::first();
        }

        $organisation = Organisation::factory()->create();
        $projectPitch = ProjectPitch::factory()->create(['organisation_id' => $organisation->uuid, 'restoration_intervention_types' => ['reforestation', 'test']]);

        TreeSpecies::factory()->count(3)->create([
            'speciesable_type' => ProjectPitch::class,
                'speciesable_id' => $projectPitch->id,
        ]);
        $user = User::factory()->create(['organisation_id' => $organisation->id]);

        $response = $this->actingAs($user)
            ->postJson('/api/v2/forms/submissions/', ['project_pitch_uuid' => $projectPitch->uuid, 'form_uuid' => $form->uuid])
            ->assertSuccessful();

        $submissionUuid = $response->json('data.uuid');
        $answers = $response->json('data.answers');
        // organisation hq country
        $q1Uuid = FormSubmission::isUuid($submissionUuid)->first()->getForm()->sections()->first()->questions()->first()->uuid;

        $this->assertEquals($organisation->hq_country, $answers[$q1Uuid]);
        $hqCountry = 'ZW';

        $this->actingAs($user)
            ->patchJson('/api/v2/forms/submissions/' . $submissionUuid, [
                'answers' => [
                    $q1Uuid => $hqCountry,
                ],
            ])
            ->assertStatus(200)
            ->assertJsonFragment([$q1Uuid => $hqCountry]);

        $organisation = Organisation::find($organisation->id);

        $this->assertEquals($organisation->hq_country, $hqCountry);
    }

    public function test_lifecycle_translated(): void
    {
        $form = Form::first();
        if (empty($form)) {
            Artisan::call('v2-custom-form-update-data');
            $form = Form::first();
        }

        $organisation = Organisation::factory()->create();
        $projectPitch = ProjectPitch::factory()->create(['organisation_id' => $organisation->uuid]);
        $user = User::factory()->create(['organisation_id' => $organisation->id]);

        $response = $this->actingAs($user)
            ->postJson('/api/v2/forms/submissions' . '?lang=fr-FR', ['project_pitch_uuid' => $projectPitch->uuid,'form_uuid' => $form->uuid])
            ->assertSuccessful();

        $submissionUuid = $response->json('data.uuid');
        $answers = $response->json('data.answers');
        // organisation hq country
        $q1Uuid = FormSubmission::isUuid($submissionUuid)->first()->getForm()->sections()->first()->questions()->first()->uuid;

        $this->assertEquals($organisation->hq_country, $answers[$q1Uuid]);
        $hqCountry = 'ZW';

        $this->actingAs($user)
            ->patchJson('/api/v2/forms/submissions/' . $submissionUuid . '?lang=fr-FR', [
                'answers' => [
                    $q1Uuid => $hqCountry,
                ],
            ])
            ->assertStatus(200)
            ->assertJsonFragment([$q1Uuid => $hqCountry]);


        $this->actingAs($user)
            ->getJson('/api/v2/forms/submissions/' . $submissionUuid . '?lang=fr-FR')
            ->assertSuccessful(200);

        $organisation = Organisation::find($organisation->id);

        $this->assertEquals($organisation->hq_country, $hqCountry); //hq country
    }
}
