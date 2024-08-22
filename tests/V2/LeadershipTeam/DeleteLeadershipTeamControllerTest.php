<?php

namespace Tests\V2\LeadershipTeam;

use App\Models\V2\LeadershipTeam;
use App\Models\V2\Organisation;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteLeadershipTeamControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_delete_leadership_team()
    {
        $user = User::factory()->admin()->create();
        $leadershipTeam = LeadershipTeam::factory()->create([
            'organisation_id' => $user->organisation->uuid,
        ]);

        $this->actingAs($user)
            ->deleteJson('/api/v2/leadership-team/' . $leadershipTeam->uuid)
            ->assertStatus(200);
    }

    public function test_user_cannot_delete_leadership_team_for_other_organisation()
    {
        $user = User::factory()->create();
        $organisation = Organisation::factory()->create();
        $leadershipTeam = LeadershipTeam::factory()->create([
            'organisation_id' => $organisation->uuid,
        ]);

        $this->actingAs($user)
            ->deleteJson('/api/v2/leadership-team/' . $leadershipTeam->uuid)
            ->assertStatus(403);
    }
}
