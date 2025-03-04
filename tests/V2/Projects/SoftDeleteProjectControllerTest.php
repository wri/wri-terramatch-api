<?php

namespace Tests\V2\Projects;

use App\Models\V2\Organisation;
use App\Models\V2\Projects\Project;
use App\Models\V2\User;
use App\StateMachines\EntityStatusStateMachine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SoftDeleteProjectControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_invoke_action()
    {
        $user = User::factory()->create();

        $organisation = Organisation::factory()->create();
        $owner = User::factory()->create(['organisation_id' => $organisation->id]);

        $project1 = Project::factory()->create([
            'organisation_id' => $organisation->id,
            'framework_key' => 'terrafund',
            'status' => EntityStatusStateMachine::AWAITING_APPROVAL,
        ]);

        $project2 = Project::factory()->create([
            'organisation_id' => $organisation->id,
            'framework_key' => 'terrafund',
            'status' => EntityStatusStateMachine::STARTED,
        ]);

        $uri1 = '/api/v2/projects/' . $project1->uuid;
        $uri2 = '/api/v2/projects/' . $project2->uuid;

        $this->actingAs($user)
            ->deleteJson($uri1)
            ->assertStatus(403);

        $this->actingAs($owner)
            ->deleteJson($uri1)
            ->assertStatus(403);

        $this->actingAs($user)
            ->deleteJson($uri2)
            ->assertStatus(403);

        $this->actingAs($owner)
            ->deleteJson($uri2)
            ->assertSuccessful();

        $project2->refresh();

        $this->assertTrue($project2->trashed());
    }
}
