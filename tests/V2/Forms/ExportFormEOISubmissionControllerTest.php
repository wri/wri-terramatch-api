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

class ExportFormEOISubmissionControllerTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function test_admin_can_export_form_submission()
    {
        $forms = Form::whereIn('title', [
            'TerraFund for AFR100: Landscapes - Expression of Interest (Enterprises)',
            'TerraFund for AFR100: Landscapes - Expression of Interest (Non Profits)',
        ])->get();

        if ($forms->count() == 0) {
            Artisan::call('v2-custom-form-update-data');
            Artisan::call('v2-custom-form-prep-phase2');
            Artisan::call('v2-custom-form-rfp-update-data');
            $forms = Form::whereIn('title', [
                'TerraFund for AFR100: Landscapes - Expression of Interest (Enterprises)',
                'TerraFund for AFR100: Landscapes - Expression of Interest (Non Profits)',
            ])->get();
        }

        $admin = User::factory()->admin()->create();

        foreach ($forms as $form) {
            $organisations = Organisation::factory()->count($this->faker->numberBetween(1, 5))->create();

            foreach ($organisations as $organisation) {
                $user = User::factory()->create(['organisation_id' => $organisation->id]);
                $pitch = ProjectPitch::factory()->create(['organisation_id' => $organisation->uuid]);
                $application = Application::factory()->create(['organisation_uuid' => $organisation->uuid]);

                TreeSpecies::factory()->count($this->faker->numberBetween(0, 5))->create([
                    'speciesable_type' => ProjectPitch::class,
                    'speciesable_id' => $pitch->id,
                ]);

                FormSubmission::factory()->create([
                    'organisation_uuid' => $organisation->uuid,
                    'project_pitch_uuid' => $pitch->uuid,
                    'application_id' => $application->id,
                    'user_id' => $user->uuid,
                    'form_id' => $form->uuid,
                ]);
            }

            $this->actingAs($admin)
                ->getJson('/api/v2/admin/forms/submissions/' . $form->uuid . '/export')
                ->assertStatus(200);
        }
    }

    public function test_user_cannot_export_form_submission()
    {
        $user = User::factory()->create();
        $submissions = FormSubmission::factory()->count(2)->create();
        $form = $submissions[0]->form;

        $this->actingAs($user)
            ->getJson('/api/v2/admin/forms/submissions/' . $form->uuid . '/export')
            ->assertStatus(403);
    }
}
