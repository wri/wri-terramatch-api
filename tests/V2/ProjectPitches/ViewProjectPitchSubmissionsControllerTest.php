<?php

namespace Tests\V2\ProjectPitches;

use App\Models\User;
use App\Models\V2\Forms\FormSubmission;
use App\Models\V2\ProjectPitch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ViewProjectPitchSubmissionsControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_can_view_project_pitches()
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $owner = User::factory()->create();

        $projectPitch = ProjectPitch::factory()->create([
            'organisation_id' => $owner->organisation->uuid,
        ]);

        FormSubmission::factory()->count(3)->create([
            'organisation_uuid' => $owner->organisation->uuid,
            'project_pitch_uuid' => $projectPitch->uuid,
            ]);

        $uri = '/api/v2/project-pitches/' . $projectPitch->uuid .'/submissions';

        $this->actingAs($user)
            ->getJson($uri)
            ->assertStatus(403);

        $this->actingAs($admin)
            ->getJson($uri)
            ->assertStatus(200)
            ->assertJsonCount(3, 'data');

        $this->actingAs($owner)
            ->getJson($uri)
            ->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }
}
