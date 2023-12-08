<?php

namespace Tests\V2\Applications;

use App\Models\User;
use App\Models\V2\Forms\Application;
use App\Models\V2\Forms\Form;
use App\Models\V2\Forms\FormSubmission;
use App\Models\V2\FundingProgramme;
use App\Models\V2\Stages\Stage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ViewMyApplicationControllerTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function test_invoke_action()
    {
        $user = User::factory()->create();
        $randomUser = User::factory()->create();

        $fundingProgramme = FundingProgramme::factory()->create();

        for ($i = 0 ; $i <= 3 ; $i++) {
            $stage = Stage::factory()->create([
                'funding_programme_id' => $fundingProgramme->uuid,
                'name' => $this->faker->word,
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
        }

        $uri = '/api/v2/my/applications';

        $this->actingAs($user)
            ->getJson($uri)
            ->assertSuccessful()
            ->assertJsonCount(4, 'data');

        $this->actingAs($randomUser)
            ->getJson($uri)
            ->assertSuccessful()
            ->assertJsonCount(0, 'data');
    }

    public function test_duplicate()
    {
        $user = User::factory()->create();
        $fundingProgramme = FundingProgramme::factory()->create();

        $stages = [];
        $forms = [];
        for ($i = 0; $i < 3; $i++) {
            $stages[$i] = Stage::factory()->create([
                'funding_programme_id' => $fundingProgramme->uuid,
                'name' => $this->faker->word,
            ]);

            $forms[$i] = Form::factory()->create([
                'stage_id' => $stages[$i]->uuid,
            ]);
        }

        for ($c = 0; $c < 2; $c++) {
            $application = Application::factory()->create([
                'funding_programme_uuid' => $fundingProgramme->uuid,
                'organisation_uuid' => $user->organisation->uuid,
            ]);

            for ($i = 0; $i < 3; $i++) {
                FormSubmission::factory()->create([
                    'form_id' => $forms[$i]->uuid,
                    'stage_uuid' => $stages[$i]->uuid,
                    'application_id' => $application->id,
                    'organisation_uuid' => $application->organisaton_uuid,
                ]);
            }
        }

        $uri = '/api/v2/my/applications';

        $this->actingAs($user)
            ->getJson($uri)
            ->assertSuccessful()
            ->assertJsonCount(2, 'data');
    }
}
