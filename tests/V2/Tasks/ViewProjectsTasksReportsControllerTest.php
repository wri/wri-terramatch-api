<?php

namespace Tests\V2\Tasks;

use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Nurseries\NurseryReport;
use App\Models\V2\Organisation;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;
use App\Models\V2\Tasks\Task;
use App\Models\V2\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ViewProjectsTasksReportsControllerTest extends TestCase
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
            'framework_key' => 'terrafund',
        ]);

        $task = Task::factory()->create([
            'organisation_id' => $organisation->id,
            'project_id' => $project->id,
            'period_key' => $period->year . '-' . $period->month,
            'due_at' => $date,
        ]);

        $site = Site::factory()->create([
            'project_id' => $project->id,
            'framework_key' => 'terrafund',
        ]);

        $nursery = Nursery::factory()->create([
            'project_id' => $project->id,
            'framework_key' => 'terrafund',
        ]);

        ProjectReport::factory()->create([
            'project_id' => $project->id,
            'framework_key' => 'terrafund',
            'due_at' => $date,
        ]);

        SiteReport::factory()->create([
            'site_id' => $site->id,
            'framework_key' => 'terrafund',
            'due_at' => $date,
        ]);

        NurseryReport::factory()->create([
            'nursery_id' => $nursery->id,
            'framework_key' => 'terrafund',
            'due_at' => $date,
        ]);

        $uri = '/api/v2/tasks/' . $task->uuid . '/reports';

        $this->actingAs($user)
            ->getJson($uri)
            ->assertStatus(403);

        $this->actingAs($owner)
            ->getJson($uri)
            ->assertSuccessful()
            ->assertJsonCount(3, 'data');
    }
}
