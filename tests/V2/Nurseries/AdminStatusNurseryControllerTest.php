<?php

namespace Tests\V2\Nurseries;

use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Organisation;
use App\Models\V2\Projects\Project;
use App\Models\V2\User;
use App\StateMachines\EntityStatusStateMachine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminStatusNurseryControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoke_action(): void
    {
        $organisation = Organisation::factory()->create();
        $project = Project::factory()->create([
            'framework_key' => 'ppc',
            'organisation_id' => $organisation->id,
        ]);

        $nursery = Nursery::factory()->create([
            'framework_key' => 'ppc',
            'project_id' => $project->id,
            'status' => EntityStatusStateMachine::AWAITING_APPROVAL,
        ]);

        $owner = User::factory()->create(['organisation_id' => $organisation->id]);
        $random = User::factory()->create();
        $tfAdmin = User::factory()->terrafundAdmin()->create();
        $ppcAdmin = User::factory()->ppcAdmin()->create();

        $payload = ['feedback' => 'testing more info'];
        $uri = '/api/v2/admin/nurseries/' . $nursery->uuid . '/moreinfo';

        $this->actingAs($random)
            ->putJson($uri, $payload)
            ->assertStatus(403);

        $this->actingAs($tfAdmin)
            ->putJson($uri, $payload)
            ->assertStatus(403);

        $this->actingAs($owner)
            ->putJson($uri, $payload)
            ->assertStatus(403);

        $this->actingAs($ppcAdmin)
            ->putJson($uri, $payload)
            ->assertSuccessful()
            ->assertJsonFragment(['status' => EntityStatusStateMachine::NEEDS_MORE_INFORMATION]);
    }
}
