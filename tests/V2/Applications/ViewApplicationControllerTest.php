<?php

namespace Tests\V2\Applications;

use App\Models\V2\Forms\Application;
use App\Models\V2\Forms\Form;
use App\Models\V2\Forms\FormSubmission;
use App\Models\V2\FundingProgramme;
use App\Models\V2\Stages\Stage;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ViewApplicationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoke_action()
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $randomer = User::factory()->create();

        $fundingProgramme = FundingProgramme::factory()->create();

        $stage = Stage::factory()->create([
            'funding_programme_id' => $fundingProgramme->uuid,
            'name' => 'E.O.I',
        ]);

        $form = Form::factory()->create([
            'stage_id' => $stage->uuid,
        ]);

        $application = Application::factory()->create([
            'funding_programme_uuid' => $fundingProgramme->uuid,
            'organisation_uuid' => $user->organisation->uuid,
        ]);

        FormSubmission::factory()->create([
            'form_id' => $form->uuid,
            'stage_uuid' => $stage->uuid,
            'application_id' => $application->id,
            'organisation_uuid' => $application->organisaton_uuid,
        ]);

        $uri = '/api/v2/applications/' . $application->uuid;

        $this->actingAs($randomer)
            ->getJson($uri)
            ->assertStatus(403);

        $this->actingAs($user)
            ->getJson($uri)
            ->assertSuccessful()
            ->assertJsonFragment(['uuid' => $fundingProgramme->uuid])
            ->assertJsonFragment(['uuid' => $application->uuid])
            ->assertJsonFragment(['uuid' => $user->organisation->uuid]);

        $this->actingAs($admin)
            ->getJson($uri)
            ->assertSuccessful();
    }
}
