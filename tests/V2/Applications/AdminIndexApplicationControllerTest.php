<?php

namespace Tests\V2\Applications;

use App\Models\User;
use App\Models\V2\Forms\Application;
use App\Models\V2\Forms\Form;
use App\Models\V2\Forms\FormSubmission;
use App\Models\V2\FundingProgramme;
use App\Models\V2\Stages\Stage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminIndexApplicationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoke_action()
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $fundingProgramme1 = FundingProgramme::factory()->create();

        $stage1 = Stage::factory()->create([
            'funding_programme_id' => $fundingProgramme1->uuid,
            'name' => 'E.O.I',
        ]);

        $form = Form::factory()->create([
            'stage_id' => $stage1->uuid,
        ]);

        $applications = Application::factory()->count(4)->create(
            ['funding_programme_uuid' => $fundingProgramme1->uuid]
        );

        foreach ($applications as $application) {
            $formSubmissions = FormSubmission::factory()->create([
                'form_id' => $form->uuid,
                'stage_uuid' => $stage1->uuid,
                'application_id' => $application->id,
                'organisation_uuid' => $application->organisaton_uuid,
            ]);
        }

        $fundingProgramme2 = FundingProgramme::factory()->create();

        $stage2 = Stage::factory()->create([
            'funding_programme_id' => $fundingProgramme2->uuid,
            'name' => 'R.F.P.',
        ]);

        $form = Form::factory()->create([
            'stage_id' => $stage2->uuid,
        ]);

        $applications = Application::factory()->count(8)->create(
            ['funding_programme_uuid' => $fundingProgramme2->uuid]
        );

        foreach ($applications as $application) {
            FormSubmission::factory()->create([
                'form_id' => $form->uuid,
                'stage_uuid' => $stage2->uuid,
                'application_id' => $application->id,
                'organisation_uuid' => $application->organisaton_uuid,
            ]);
        }

        $this->actingAs($user)
            ->getJson('/api/v2/admin/forms/applications')
            ->assertStatus(403);

        $this->actingAs($admin)
            ->getJson('/api/v2/admin/forms/applications')
            ->assertSuccessful()
            ->assertJsonCount(12, 'data');

        $this->actingAs($admin)
            ->getJson('/api/v2/admin/forms/applications?search=test')
            ->assertSuccessful();

        $this->actingAs($admin)
            ->getJson('/api/v2/admin/forms/applications?per_page=5')
            ->assertSuccessful()
            ->assertJsonCount(5, 'data');

        $this->actingAs($admin)
            ->getJson('/api/v2/admin/forms/applications?per_page=5&page=3')
            ->assertSuccessful()
            ->assertJsonCount(2, 'data');

        $this->actingAs($admin)
            ->getJson('/api/v2/admin/forms/applications?filter[funding_programme_uuid]=' . $fundingProgramme1->uuid)
            ->assertSuccessful()
            ->assertJsonCount(4, 'data');

        $this->actingAs($admin)
            ->getJson('/api/v2/admin/forms/applications?filter[project_pitch_uuid]=' . $formSubmissions->first()->project_pitch_uuid)
            ->assertSuccessful()
            ->assertJsonCount(1, 'data');

        $this->actingAs($admin)
            ->getJson('/api/v2/admin/forms/applications?filter[organisation_uuid]=' . $application->organisation_uuid)
            ->assertSuccessful()
            ->assertJsonCount(1, 'data');

        $this->actingAs($admin)
            ->getJson('/api/v2/admin/forms/applications?sort=-organisation_name&per_page=5')
            ->assertSuccessful()
            ->assertJsonCount(5, 'data');

        $this->actingAs($admin)
            ->getJson('/api/v2/admin/forms/applications?filter[funding_programme_uuid]=' . $fundingProgramme2->uuid . '&sort=-funding_programme_name')
            ->assertSuccessful()
            ->assertJsonCount(8, 'data');

        $this->actingAs($admin)
            ->getJson('/api/v2/admin/forms/applications?filter[current_stage]=' . $stage1->uuid)
            ->assertSuccessful()
            ->assertJsonCount(4, 'data');

        $this->actingAs($admin)
            ->getJson('/api/v2/admin/forms/applications?filter[current_submission_status]=' . FormSubmission::STATUS_STARTED)
            ->assertSuccessful();
    }
}
