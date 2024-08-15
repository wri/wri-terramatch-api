<?php

namespace Tests\V2\CoreTeamLeader;

use App\Models\V2\Organisation;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreCoreTeamLeaderControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_core_team_leader()
    {
        $user = User::factory()->create();

        $payload = [
            'organisation_id' => $user->organisation->uuid,
            'position' => 'a position',
            'gender' => 'a gender',
            'age' => 25,
        ];

        $this->actingAs($user)
            ->postJson('/api/v2/core-team-leader', $payload)
            ->assertStatus(201)
            ->assertJsonFragment([
                'position' => 'a position',
                'gender' => 'a gender',
                'age' => 25,
            ]);
    }

    public function test_user_cannot_create_core_team_leader_for_other_organisation()
    {
        $user = User::factory()->create();
        $organisation = Organisation::factory()->create();

        $payload = [
            'organisation_id' => $organisation->uuid,
            'position' => 'a position',
            'gender' => 'a gender',
            'age' => 25,
        ];

        $this->actingAs($user)
            ->postJson('/api/v2/core-team-leader', $payload)
            ->assertStatus(403);
    }
}
