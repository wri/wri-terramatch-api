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

class AdminDeleteApplicationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoke_action()
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $fundingProgramme = FundingProgramme::factory()->create();

        $stage = Stage::factory()->create([
            'funding_programme_id' => $fundingProgramme->uuid,
            'name' => 'E.O.I',
        ]);

        $form = Form::factory()->create([
            'stage_id' => $stage->uuid,
        ]);

        $application = Application::factory()->create(
            ['funding_programme_uuid' => $fundingProgramme->uuid]
        );

        $formSubmission = FormSubmission::factory()->create([
            'form_id' => $form->uuid,
            'stage_uuid' => $stage->uuid,
            'application_id' => $application->id,
            'organisation_uuid' => $application->organisaton_uuid,
        ]);

        $this->actingAs($admin)
            ->getJson('/api/v2/admin/forms/applications/' . $application->uuid)
            ->assertSuccessful();

        $this->actingAs($admin)
            ->getJson('/api/v2/admin/forms/submissions/' . $formSubmission->uuid)
            ->assertSuccessful();

        $this->actingAs($user)
            ->deleteJson('/api/v2/admin/forms/applications/' . $application->uuid)
            ->assertStatus(403);

        $this->actingAs($admin)
            ->deleteJson('/api/v2/admin/forms/applications/' . $application->uuid)
            ->assertSuccessful();

        $this->actingAs($admin)
            ->deleteJson('/api/v2/admin/forms/applications/' . $application->uuid)
            ->assertStatus(404);

        $this->actingAs($admin)
            ->deleteJson('/api/v2/admin/forms/submissions/' . $formSubmission->uuid)
            ->assertStatus(404);
    }
}
