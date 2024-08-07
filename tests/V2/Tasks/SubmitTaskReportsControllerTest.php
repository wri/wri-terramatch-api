<?php

namespace Tests\V2\Tasks;

use App\Helpers\CustomFormHelper;
use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Nurseries\NurseryReport;
use App\Models\V2\Organisation;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;
use App\Models\V2\Tasks\Task;
use App\Models\V2\User;
use App\StateMachines\EntityStatusStateMachine;
use App\StateMachines\ReportStatusStateMachine;
use App\StateMachines\TaskStatusStateMachine;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class SubmitTaskReportsControllerTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function test_invoke_action()
    {
        Artisan::call('v2migration:roles');

        $organisation = Organisation::factory()->create();
        $owner = User::factory()->create(['organisation_id' => $organisation->id]);
        $owner->givePermissionTo('manage-own');

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
            'due_at' => $date,
        ]);

        $site = Site::factory()->create([
            'project_id' => $project->id,
            'framework_key' => 'ppc',
        ]);

        $nursery = Nursery::factory()->create([
            'project_id' => $project->id,
            'framework_key' => 'ppc',
        ]);

        $projectReport = ProjectReport::factory()->create([
            'project_id' => $project->id,
            'task_id' => $task->id,
            'framework_key' => 'ppc',
            'due_at' => $date,
            'status' => EntityStatusStateMachine::AWAITING_APPROVAL,
        ]);

        $siteReport = SiteReport::factory()->create([
            'site_id' => $site->id,
            'task_id' => $task->id,
            'framework_key' => 'ppc',
            'due_at' => $date,
            'nothing_to_report' => true,
            'status' => ReportStatusStateMachine::DUE,
        ]);

        $nurseryReport = NurseryReport::factory()->create([
            'nursery_id' => $nursery->id,
            'task_id' => $task->id,
            'framework_key' => 'ppc',
            'due_at' => $date,
            'completion' => 100,
            'status' => EntityStatusStateMachine::STARTED,
        ]);

        CustomFormHelper::generateFakeForm('site', 'ppc');

        $uri = '/api/v2/tasks/' . $task->uuid . '/submit';

        $this->actingAs($user)
            ->putJson($uri)
            ->assertStatus(403);

        $this->actingAs($owner)
             ->putJson($uri)
             ->assertSuccessful();

        $projectReport->refresh();
        $this->assertEquals(EntityStatusStateMachine::AWAITING_APPROVAL, $projectReport->status);

        $siteReport->refresh();
        $this->assertEquals(EntityStatusStateMachine::AWAITING_APPROVAL, $siteReport->status);

        $nurseryReport->refresh();
        $this->assertEquals(EntityStatusStateMachine::AWAITING_APPROVAL, $nurseryReport->status);

        $updatedTask = Task::find($task->id);
        $this->assertEquals(TaskStatusStateMachine::AWAITING_APPROVAL, $updatedTask->status);
    }
}
