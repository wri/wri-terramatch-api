<?php

namespace Tests\V2\Applications;

use App\Models\User;
use App\Models\V2\Forms\Application;
use App\Models\V2\Forms\Form;
use App\Models\V2\Forms\FormQuestion;
use App\Models\V2\Forms\FormSection;
use App\Models\V2\Forms\FormSubmission;
use App\Models\V2\FundingProgramme;
use App\Models\V2\FundingType;
use App\Models\V2\LeadershipTeam;
use App\Models\V2\Organisation;
use App\Models\V2\ProjectPitch;
use App\Models\V2\SavedExport;
use App\Models\V2\Stages\Stage;
use App\Models\V2\TreeSpecies\TreeSpecies;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ExportApplicationControllerTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function test_invoke_action()
    {
        $admin = User::factory()->admin()->create();
        $randomer = User::factory()->create();

        $fundingProgramme = FundingProgramme::factory()->create();
        $organisation = Organisation::factory()->create();
        $pitch = ProjectPitch::factory()->create(['organisation_id' => $organisation->uuid]);

        $user = User::factory()->create([
            'organisation_id' => $organisation->id,
        ]);

        $stage1 = Stage::factory()->create([
            'funding_programme_id' => $fundingProgramme->uuid,
            'name' => 'E.O.I',
        ]);

        $form1 = Form::factory()->create([
            'stage_id' => $stage1->uuid,
        ]);

        $sections1 = FormSection::factory()->count(2)->create([
            'form_id' => $form1->uuid,
        ]);

        foreach ($sections1 as $section) {
            $linkedFieldKeys = $this->faker->randomElements(array_keys(config('wri.linked-fields.models.organisation.fields')), 3);
            foreach ($linkedFieldKeys as $key) {
                $linkedField = config('wri.linked-fields.models.organisation.fields.' . $key);
                FormQuestion::factory()->create([
                    'form_section_id' => $section->id,
                    'name' => $linkedField['label'],
                    'linked_field_key' => $key,
                ]);
            }
        }

        $stage2 = Stage::factory()->create([
            'funding_programme_id' => $fundingProgramme->uuid,
            'name' => 'R.F.P.',
        ]);

        $form2 = Form::factory()->create([
            'stage_id' => $stage2->uuid,
        ]);

        $sections2 = FormSection::factory()->count(2)->create([
            'form_id' => $form1->uuid,
        ]);

        foreach ($sections2 as $section) {
            $linkedFieldKeys = $this->faker->randomElements(array_keys(config('wri.linked-fields.models.organisation.fields')), 3);
            foreach ($linkedFieldKeys as $key) {
                $linkedField = config('wri.linked-fields.models.organisation.fields.' . $key);
                FormQuestion::factory()->create([
                    'form_section_id' => $section->id,
                    'name' => $linkedField['label'],
                    'linked_field_key' => $key,
                ]);
            }
        }

        $application = Application::factory()->create([
            'funding_programme_uuid' => $fundingProgramme->uuid,
            'organisation_uuid' => $user->organisation->uuid,
        ]);

        FormSubmission::factory()->create([
            'form_id' => $form1->uuid,
            'stage_uuid' => $stage1->uuid,
            'application_id' => $application->id,
            'organisation_uuid' => $application->organisation_uuid,
            'project_pitch_uuid' => $pitch->uuid,
        ]);

        FormSubmission::factory()->create([
            'form_id' => $form2->uuid,
            'stage_uuid' => $stage2->uuid,
            'application_id' => $application->id,
            'organisation_uuid' => $application->organisation_uuid,
            'project_pitch_uuid' => $pitch->uuid,
        ]);

        $uri = '/api/v2/applications/' . $application->uuid . '/export';

        $this->actingAs($randomer)
            ->getJson($uri)
            ->assertStatus(403);

        $this->actingAs($admin)
            ->getJson($uri)
            ->assertSuccessful();

        $this->actingAs($user)
            ->getJson($uri)
            ->assertSuccessful();
    }

    public function test_eoi_rfp_()
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

        $fundingProgramme = FundingProgramme::find(1);
        if (! $fundingProgramme) {
            $fundingProgramme = FundingProgramme::factory()->create();
        }

        SavedExport::factory()->create([
            'funding_programme_id' => $fundingProgramme,
        ]);

        $organisation = Organisation::factory()->create();

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
