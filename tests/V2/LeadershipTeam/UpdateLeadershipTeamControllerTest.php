<?php

namespace Tests\V2\LeadershipTeam;

use App\Models\User;
use App\Models\V2\LeadershipTeam;
use App\Models\V2\Organisation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateLeadershipTeamControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_leadership_team()
    {
        $user = User::factory()->create();
        $leadershipTeam = LeadershipTeam::factory()->create([
            'organisation_id' => $user->organisation->uuid,
        ]);

        $payload = [
            'position' => 'a position',
            'gender' => 'a gender',
            'age' => 25,
        ];

        $this->actingAs($user)
            ->patchJson('/api/v2/leadership-team/' . $leadershipTeam->uuid, $payload)
            ->assertStatus(200)
            ->assertJsonFragment([
                'position' => 'a position',
                'gender' => 'a gender',
                'age' => 25,
            ]);
    }

    public function test_user_cannot_update_leadership_team_for_other_organisation()
    {
        $user = User::factory()->create();
        $organisation = Organisation::factory()->create();
        $leadershipTeam = LeadershipTeam::factory()->create([
            'organisation_id' => $organisation->uuid,
        ]);

        $payload = [
            'position' => 'a position',
            'gender' => 'a gender',
            'age' => 25,
        ];

        $this->actingAs($user)
            ->patchJson('/api/v2/leadership-team/' . $leadershipTeam->uuid, $payload)
            ->assertStatus(403);
    }
}
