<?php

namespace Tests\V2\User;

use App\Models\User;
use App\Models\V2\Action;
use App\Models\V2\Projects\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexMyActionsControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_can_view_their_actions()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();

        $user->projects()->attach($project->id);

        $projectAction = Action::factory()->project()->create([
            'organisation_id' => $user->organisation->id,
            'project_id' => $project->id,
        ]);
        $siteReportAction = Action::factory()->siteReport()->create([
            'organisation_id' => $user->organisation->id,
            'project_id' => $project->id,
        ]);
        $completedAction = Action::factory()->project()->complete()->create([
            'organisation_id' => $user->organisation->id,
            'project_id' => $project->id,
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

    public function test_users_should_not_view_actions_outside_projects_scope()
    {
        $user = User::factory()->admin()->create();
        $userProject = Project::factory()->create();
        $externalProject = Project::factory()->create();

        $user->projects()->attach($userProject->id);

        $nurseryAction = Action::factory()->nursery()->create([
            'organisation_id' => $user->organisation->id,
            'project_id' => $userProject->id,
        ]);

        $projectAction = Action::factory()->project()->create([
            'organisation_id' => $user->organisation->id,
            'project_id' => $externalProject->id,
        ]);
        $siteReportAction = Action::factory()->siteReport()->create([
            'organisation_id' => $user->organisation->id,
            'project_id' => $externalProject->id,
        ]);

        $this->actingAs($user)
            ->getJson('/api/v2/my/actions')
            ->assertStatus(200)
            ->assertJsonFragment([
                'uuid' => $nurseryAction->uuid,
            ])
            ->assertJsonMissing([
                'uuid' => $projectAction->uuid,
            ])
            ->assertJsonMissing([
                'uuid' => $siteReportAction->uuid,
            ]);
    }
}
