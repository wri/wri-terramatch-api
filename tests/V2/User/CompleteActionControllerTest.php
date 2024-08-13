<?php

namespace Tests\V2\User;

use App\Models\V2\Action;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompleteActionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_can_complete_their_actions()
    {
        $user = User::factory()->create();

        $projectAction = Action::factory()->project()->create([
            'organisation_id' => $user->organisation->id,
        ]);

        $this->actingAs($user)
            ->putJson('/api/v2/my/actions/' . $projectAction->uuid . '/complete')
            ->assertStatus(200)
            ->assertJsonFragment([
                'uuid' => $projectAction->uuid,
                'status' => Action::STATUS_COMPLETE,
            ]);
    }
}
