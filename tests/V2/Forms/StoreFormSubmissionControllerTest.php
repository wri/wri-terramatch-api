<?php

namespace Tests\V2\Forms;

use App\Models\V2\Forms\Form;
use App\Models\V2\Organisation;
use App\Models\V2\ProjectPitch;
use App\Models\V2\TreeSpecies\TreeSpecies;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
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
        $projectPitches = ProjectPitch::factory()->count(3)->create(['organisation_id' => $organisation->uuid]);
        $projectPitch = ProjectPitch::factory()->create(['organisation_id' => $organisation->uuid, 'restoration_intervention_types' => ['reforestation', 'test']]);

        TreeSpecies::factory()->count(3)->create([
            'speciesable_type' => ProjectPitch::class,
                'speciesable_id' => $projectPitch->id,
        ]);
        $user = User::factory()->create(['organisation_id' => $organisation->id]);

        Storage::fake('uploads');
        $file = UploadedFile::fake()->image('cover photo.png', 10, 10);

        $this->actingAs($user)
            ->postJson('/api/v2/file/upload/organisation/cover/' . $organisation->uuid, ['title' => 'Cover Photo Test1', 'upload_file' => $file])
            ->assertSuccessful();

        $response = $this->actingAs($user)
            ->postJson('/api/v2/forms/submissions/', ['project_pitch_uuid' => $projectPitch->uuid, 'form_uuid' => $form->uuid])
            ->assertSuccessful();

        $submissionUuid = $response->json('data.uuid');
        $questions = $response->json('data.form.form_sections.0.form_questions');
        $answers = $response->json('data.answers');
        $q1Uuid = data_get($questions[0], 'uuid'); //organisation hq country

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

        Storage::fake('uploads');
        $file = UploadedFile::fake()->image('cover photo.png', 10, 10);

        $this->actingAs($user)
            ->postJson('/api/v2/file/upload/organisation/cover/' . $organisation->uuid, ['title' => 'Cover Photo Test1', 'upload_file' => $file])
            ->assertSuccessful();

        $response = $this->actingAs($user)
            ->postJson('/api/v2/forms/submissions' . '?lang=fr-FR', ['project_pitch_uuid' => $projectPitch->uuid,'form_uuid' => $form->uuid])
            ->assertSuccessful();

        $submissionUuid = $response->json('data.uuid');
        $questions = $response->json('data.form.form_sections.0.form_questions');
        $answers = $response->json('data.answers');
        $q1Uuid = data_get($questions[0], 'uuid'); //organisation HQ Country

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
