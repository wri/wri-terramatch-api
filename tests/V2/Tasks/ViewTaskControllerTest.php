<?php

namespace Tests\V2\Tasks;

use App\Models\V2\Organisation;
use App\Models\V2\Projects\Project;
use App\Models\V2\Tasks\Task;
use App\Models\V2\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ViewTaskControllerTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function test_invoke_action()
    {
        $organisation = Organisation::factory()->create();
        $owner = User::factory()->create(['organisation_id' => $organisation->id]);

        $user = User::factory()->create();

        $period = Carbon::now();
        $date = Carbon::now()->firstOfMonth(4);

        $project = Project::factory()->create([
            'organisation_id' => $organisation->id,
            'framework_key' => 'ppc',
        ]);

        $task = Task::factory()->create([
            'organisation_id' => $organisation->id,
            'project_id' => $project->id,
            'period_key' => $period->year . '-' . $period->month,
        ]);


        $uri = '/api/v2/tasks/' . $task->uuid;

        $this->actingAs($user)
            ->getJson($uri)
            ->assertStatus(403);

        $this->actingAs($owner)
            ->getJson($uri)
            ->assertSuccessful();
    }
}
