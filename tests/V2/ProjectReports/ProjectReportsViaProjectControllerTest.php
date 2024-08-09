<?php

namespace Tests\V2\ProjectReports;

use App\Models\Organisation;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\User;
use App\StateMachines\EntityStatusStateMachine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProjectReportsViaProjectControllerTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function test_invoke_action()
    {
        $tfAdmin = User::factory()->terrafundAdmin()->create();
        $ppcAdmin = User::factory()->ppcAdmin()->create();

        $user = User::factory()->create();

        $organisation = Organisation::factory()->create();
        $owner = User::factory()->create(['organisation_id' => $organisation->id]);

        ProjectReport::query()->delete();
        $project = Project::factory()->create(['organisation_id' => $organisation->id, 'framework_key' => 'ppc']);
        ProjectReport::factory()->count(4)->create([
            'project_id' => $project->id,
            'framework_key' => 'ppc',
            'status' => EntityStatusStateMachine::APPROVED,
        ]);
        ProjectReport::factory()->count(2)->create(['framework_key' => 'ppc']);

        $uri = '/api/v2/projects/' . $project->uuid . '/reports';

        $this->actingAs($user)
            ->getJson($uri)
            ->assertStatus(403);

        $this->actingAs($tfAdmin)
            ->getJson($uri)
            ->assertStatus(403);

        $this->actingAs($owner)
            ->getJson($uri)
            ->assertSuccessful()
            ->assertJsonCount(4, 'data');

        $this->actingAs($ppcAdmin)
            ->getJson($uri)
            ->assertSuccessful()
            ->assertJsonCount(4, 'data');
    }
}
