<?php

namespace Tests\V2\ProjectPitches;

use App\Models\User;
use App\Models\V2\ProjectPitch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ViewProjectPitchControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_can_view_project_pitches(): void
    {
        $user = User::factory()->admin()->create();
        $projectPitch = ProjectPitch::factory()->create([
            'organisation_id' => $user->organisation->uuid,
        ]);

        $this->actingAs($user)
            ->getJson('/api/v2/project-pitches/' . $projectPitch->uuid)
            ->assertStatus(200);
    }

    public function test_users_cannot_view_pitches_that_do_not_belong_to_them(): void
    {
        $user = User::factory()->create();
        $projectPitch = ProjectPitch::factory()->create();

        $this->actingAs($user)
            ->getJson('/api/v2/project-pitches/' . $projectPitch->uuid)
            ->assertStatus(403);
    }
}
