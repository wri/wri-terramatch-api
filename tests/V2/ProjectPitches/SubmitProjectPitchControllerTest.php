<?php

namespace Tests\V2\ProjectPitches;

use App\Models\V2\FundingProgramme;
use App\Models\V2\ProjectPitch;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SubmitProjectPitchControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_can_update_forms(): void
    {
        $user = User::factory()->create();
        $fundingProgramme = FundingProgramme::factory()->create();
        $projectPitch = ProjectPitch::factory()->create([
            'organisation_id' => $user->organisation->uuid,
            'funding_programme_id' => $fundingProgramme->uuid,
            'status' => ProjectPitch::STATUS_DRAFT,
        ]);

        $this->actingAs($user)
            ->putJson('/api/v2/project-pitches/submit/' . $projectPitch->uuid, [])
            ->assertSuccessful()
            ->assertJsonFragment([
                'status' => ProjectPitch::STATUS_ACTIVE,
            ]);
    }
}
