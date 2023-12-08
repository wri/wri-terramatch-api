<?php

namespace Tests\V2\CoreTeamLeader;

use App\Models\User;
use App\Models\V2\CoreTeamLeader;
use App\Models\V2\Organisation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteCoreTeamLeaderControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_delete_core_team_leader()
    {
        $user = User::factory()->admin()->create();
        $coreTeamLeader = CoreTeamLeader::factory()->create([
            'organisation_id' => $user->organisation->uuid,
        ]);

        $this->actingAs($user)
            ->deleteJson('/api/v2/core-team-leader/' . $coreTeamLeader->uuid)
            ->assertStatus(200);
    }

    public function test_user_cannot_delete_core_team_leader_for_other_organisation()
    {
        $user = User::factory()->create();
        $organisation = Organisation::factory()->create();
        $coreTeamLeader = CoreTeamLeader::factory()->create([
            'organisation_id' => $organisation->uuid,
        ]);

        $this->actingAs($user)
            ->deleteJson('/api/v2/core-team-leader/' . $coreTeamLeader->uuid)
            ->assertStatus(403);
    }
}
