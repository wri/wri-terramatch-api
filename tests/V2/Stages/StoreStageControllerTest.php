<?php

namespace Tests\V2\Stages;

use App\Models\V2\Forms\Form;
use App\Models\V2\FundingProgramme;
use App\Models\V2\Stages\Stage;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class StoreStageControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admins_can_create_stages(): void
    {
        $user = User::factory()->admin()->create();
        $fundingProgramme = FundingProgramme::factory()->create();
        $form = Form::factory()->create();

        $payload = [
            'funding_programme_id' => $fundingProgramme->uuid,
            'form_id' => $form->uuid,
            'deadline_at' => '2024-07-08 12:00:00',
            'order' => 1,
            'name' => 'the stage name',
        ];

        $response = $this->actingAs($user)
            ->postJson('/api/v2/admin/funding-programme/stage', $payload)
            ->assertStatus(201)
            ->assertJsonFragment([
                'name' => 'the stage name',
                'funding_programme_id' => $fundingProgramme->uuid,
                'deadline_at' => '2024-07-08T17:00:00.000000Z',
            ]);

        $form = Form::isUuid($form->uuid)->first();
        $stage = Stage::isUuid($response['data']['uuid'])->first();
        $this->assertEquals($form->stage_id, $stage->uuid);
    }

    public function test_non_admins_cannot_create_stages(): void
    {
        $user = User::factory()->create();

        $payload = [
            'funding_programme_id' => FundingProgramme::factory()->create()->uuid,
            'order' => 1,
        ];

        $this->actingAs($user)
            ->postJson('/api/v2/admin/funding-programme/stage', $payload)
            ->assertStatus(403);
    }
}
