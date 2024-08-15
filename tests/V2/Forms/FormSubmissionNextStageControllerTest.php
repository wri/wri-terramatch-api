<?php

namespace Tests\V2\Forms;

use App\Models\V2\Forms\Application;
use App\Models\V2\Forms\Form;
use App\Models\V2\Forms\FormSubmission;
use App\Models\V2\Organisation;
use App\Models\V2\ProjectPitch;
use App\Models\V2\TreeSpecies\TreeSpecies;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class FormSubmissionNextStageControllerTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function test_next_stage_creation_for_form_submission()
    {
        $form = Form::where('title', 'TerraFund for AFR100: Landscapes - Expression of Interest (Enterprises)')->first();

        if (empty($form)) {
            Artisan::call('v2-custom-form-update-data');
            Artisan::call('v2-custom-form-prep-phase2');
            Artisan::call('v2-custom-form-rfp-update-data');
            $form = Form::where('title', 'TerraFund for AFR100: Landscapes - Expression of Interest (Enterprises)')->first();
        }

        $organisation = Organisation::factory()->create();
        $applicant = User::factory()->create(['organisation_id' => $organisation->id]);
        $pitch = ProjectPitch::factory()->create(['organisation_id' => $organisation->uuid]);
        $application = Application::factory()->create([
            'organisation_uuid' => $organisation->uuid,
            'funding_programme_uuid' => $form->stage->funding_programme_id,
        ]);

        TreeSpecies::factory()->count($this->faker->numberBetween(0, 5))->create(['speciesable_type' => ProjectPitch::class, 'speciesable_id' => $pitch->id]);

        $formSubmission = FormSubmission::factory()->create([
            'status' => FormSubmission::STATUS_APPROVED,
            'organisation_uuid' => $organisation->uuid,
            'application_id' => $application->id,
            'user_id' => $applicant->uuid,
            'form_id' => $form->uuid,
            'stage_uuid' => $form->stage_id,
        ]);

        $this->actingAs($applicant)
            ->postJson('/api/v2/forms/submissions/' . $formSubmission->uuid . '/next-stage')
            ->assertSuccessful()
            ->assertJsonFragment(['title' => 'TerraFund for AFR100: Landscapes - Request for Proposals (Enterprise)']);
    }
}
