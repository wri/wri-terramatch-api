<?php

namespace Tests\V2\Applications;

use App\Models\User;
use App\Models\V2\Forms\Application;
use App\Models\V2\Forms\Form;
use App\Models\V2\Forms\FormSubmission;
use App\Models\V2\FundingProgramme;
use App\Models\V2\FundingType;
use App\Models\V2\LeadershipTeam;
use App\Models\V2\Organisation;
use App\Models\V2\ProjectPitch;
use App\Models\V2\SavedExport;
use App\Models\V2\TreeSpecies\TreeSpecies;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class AdminExportApplicationControllerTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function test_invoke_action()
    {
        $forms = Form::whereIn('title', [
            'TerraFund for AFR100: Landscapes - Request for Proposals (Non Profits)',
            'TerraFund for AFR100: Landscapes - Request for Proposals (Enterprise)',
        ])->get();

        if ($forms->count() == 0) {
            Artisan::call('v2-custom-form-update-data');
            Artisan::call('v2-custom-form-prep-phase2');
            Artisan::call('v2-custom-form-rfp-update-data');
            $forms = Form::whereIn('title', [
                'TerraFund for AFR100: Landscapes - Request for Proposals (Non Profits)',
                'TerraFund for AFR100: Landscapes - Request for Proposals (Enterprise)',
            ])->get();
        }

        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $fundingProgrammes = FundingProgramme::whereIn('id', [1,2])->get();

        foreach ($fundingProgrammes as $fundingProgramme) {
            $organisations = Organisation::factory()->count($this->faker->numberBetween(1, 5))->create();
            SavedExport::factory()->create([
                'funding_programme_id' => $fundingProgramme,
            ]);

            foreach ($organisations as $organisation) {
                $user = User::factory()->create(['organisation_id' => $organisation->id]);
                $pitch = ProjectPitch::factory()->create(['organisation_id' => $organisation->uuid]);

                TreeSpecies::factory()->count($this->faker->numberBetween(0, 5))->create(['speciesable_type' => ProjectPitch::class, 'speciesable_id' => $pitch->id]);
                LeadershipTeam::factory()->count($this->faker->numberBetween(0, 5))->create(['organisation_id' => $organisation->uuid]);
                FundingType::factory()->count($this->faker->numberBetween(0, 5))->create(['organisation_id' => $organisation->uuid]);
                $application = Application::factory()->create(['funding_programme_uuid' => $fundingProgramme->uuid,'organisation_uuid' => $organisation->uuid]);

                $file = UploadedFile::fake()->image('proof.pdf', 10, 10);
                $this->actingAs($user)->postJson('/api/v2/file/upload/organisation/avg_tree_survival_rate_proof/' . $organisation->uuid, ['title' => 'Proof file', 'upload_file' => $file])->assertSuccessful();

                $file = UploadedFile::fake()->image('restoration.png', 10, 10);
                $this->actingAs($user)->postJson('/api/v2/file/upload/project-pitch/restoration_photos/' . $pitch->uuid, ['title' => 'Restoration photos', 'upload_file' => $file])->assertSuccessful();

                $file = UploadedFile::fake()->image('additional.csv', 10, 10);
                $this->actingAs($user)->postJson('/api/v2/file/upload/project-pitch/additional/' . $pitch->uuid, ['title' => 'Additional file', 'upload_file' => $file])->assertSuccessful();

                foreach ($fundingProgramme->stages as $stage) {
                    if (! empty($stage->form)) {
                        FormSubmission::factory()->create([
                            'form_id' => $stage->form->uuid,
                            'stage_uuid' => $stage->uuid,
                            'application_id' => $application->id,
                            'organisation_uuid' => $organisation->uuid,
                            'project_pitch_uuid' => $pitch->uuid,
                        ]);
                    }
                }
            }

            $uri = '/api/v2/admin/forms/applications/' . $fundingProgramme->uuid . '/export';

            $this->actingAs($user)
                ->getJson($uri)
                ->assertStatus(403);

            /**
             * @todo At some point, it would be good to refactor this test
             * to include some successful paths - under time constraints for
             * v2.1, we're unable to do so now.
             */
        }
    }
}
