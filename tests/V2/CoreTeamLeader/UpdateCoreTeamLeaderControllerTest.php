<?php

namespace Tests\V2\CoreTeamLeader;

use App\Models\User;
use App\Models\V2\CoreTeamLeader;
use App\Models\V2\Organisation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateCoreTeamLeaderControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_core_team_leader()
    {
        $user = User::factory()->create();
        $coreTeamLeader = CoreTeamLeader::factory()->create([
            'organisation_id' => $user->organisation->uuid,
        ]);

        $payload = [
            'position' => 'a position',
            'gender' => 'a gender',
            'age' => 25,
        ];

        $this->actingAs($user)
            ->patchJson('/api/v2/core-team-leader/' . $coreTeamLeader->uuid, $payload)
            ->assertStatus(200)
            ->assertJsonFragment([
                'position' => 'a position',
                'gender' => 'a gender',
                'age' => 25,
            ]);
    }

    public function test_user_cannot_update_core_team_leader_for_other_organisation()
    {
        $user = User::factory()->create();
        $organisation = Organisation::factory()->create();
        $coreTeamLeader = CoreTeamLeader::factory()->create([
            'organisation_id' => $organisation->uuid,
        ]);

        $payload = [
            'position' => 'a position',
            'gender' => 'a gender',
            'age' => 25,
        ];

        $this->actingAs($user)
            ->patchJson('/api/v2/core-team-leader/' . $coreTeamLeader->uuid, $payload)
            ->assertStatus(403);
    }
}
