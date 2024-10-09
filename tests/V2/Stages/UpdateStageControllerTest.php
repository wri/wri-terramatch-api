<?php

namespace Tests\V2\Stages;

use App\Models\V2\Forms\Form;
use App\Models\V2\FundingProgramme;
use App\Models\V2\Stages\Stage;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class UpdateStageControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admins_can_update_stages(): void
    {
        $user = User::factory()->admin()->create();
        $stage = Stage::factory()->create([
            'order' => 1,
        ]);
        $fundingProgramme = FundingProgramme::factory()->create();
        $form = Form::factory()->create();

        $payload = [
            'funding_programme_id' => $fundingProgramme->uuid,
            'deadline_at' => '2024-07-08 12:00:00',
            'order' => 2,
            'form_id' => $form->uuid,
        ];

        $this->actingAs($user)
            ->patchJson('/api/v2/admin/funding-programme/stage/' . $stage->uuid, $payload)
            ->assertStatus(200)
            ->assertJsonFragment([
                'funding_programme_id' => $fundingProgramme->uuid,
                'deadline_at' => '2024-07-08T17:00:00.000000Z',
            ]);

        $form->refresh();
        $this->assertEquals($form->stage_id, $stage->uuid);
    }

    public function test_non_admins_cannot_update_stages(): void
    {
        $user = User::factory()->create();
        $stage = Stage::factory()->create([
            'order' => 1,
        ]);
        $fundingProgramme = FundingProgramme::factory()->create();

        $payload = [
            'funding_programme_id' => $fundingProgramme->uuid,
            'order' => 2,
        ];

        $this->actingAs($user)
            ->patchJson('/api/v2/admin/funding-programme/stage/' . $stage->uuid, $payload)
            ->assertStatus(403);
    }
}
