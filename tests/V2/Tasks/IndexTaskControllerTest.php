<?php

namespace Tests\V2\Tasks;

use App\Helpers\CustomFormHelper;
use App\Models\V2\Organisation;
use App\Models\V2\Projects\Project;
use App\Models\V2\Tasks\Task;
use App\Models\V2\User;
use App\StateMachines\TaskStatusStateMachine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class IndexTaskControllerTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function test_invoke_action()
    {
        $organisation = Organisation::factory()->create();
        $owner = User::factory()->create(['organisation_id' => $organisation->id]);

        $user = User::factory()->create();

        $project = Project::factory()->create([
            'organisation_id' => $organisation->id,
            'framework_key' => 'ppc',
        ]);

        Task::factory()->count(3)->create([
            'organisation_id' => $organisation->id,
            'project_id' => $project->id,
            'status' => TaskStatusStateMachine::DUE,
        ]);

        CustomFormHelper::generateFakeForm('site', 'ppc');

        $uri = '/api/v2/projects/' . $project->uuid . '/tasks';

        $this->actingAs($user)
            ->getJson($uri)
            ->assertStatus(403);

        $this->actingAs($owner)
            ->getJson($uri)
            ->assertSuccessful()
            ->assertJsonCount(3, 'data');
    }
}
