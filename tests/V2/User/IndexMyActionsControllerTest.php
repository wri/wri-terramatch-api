<?php

namespace Tests\V2\User;

use App\Models\User;
use App\Models\V2\Action;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexMyActionsControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_can_view_their_actions()
    {
        $user = User::factory()->admin()->create();

        $projectAction = Action::factory()->project()->create([
            'organisation_id' => $user->organisation->id,
        ]);
        $siteReportAction = Action::factory()->siteReport()->create([
            'organisation_id' => $user->organisation->id,
        ]);
        $completedAction = Action::factory()->project()->complete()->create([
            'organisation_id' => $user->organisation->id,
        ]);

        $this->actingAs($user)
            ->getJson('/api/v2/my/actions')
            ->assertStatus(200)
            ->assertJsonFragment([
                'uuid' => $projectAction->uuid,
            ])
            ->assertJsonFragment([
                'uuid' => $siteReportAction->uuid,
            ])
            ->assertJsonMissing([
                'uuid' => $completedAction->uuid,
            ]);
    }
}
