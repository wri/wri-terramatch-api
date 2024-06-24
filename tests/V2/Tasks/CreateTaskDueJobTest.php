<?php

namespace Tests\V2\Tasks;

use App\Jobs\V2\CreateTaskDueJob;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Tasks\Task;
use App\StateMachines\ReportStatusStateMachine;
use App\StateMachines\TaskStatusStateMachine;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateTaskDueJobTest extends TestCase
{
    use RefreshDatabase;

    public function testCreateTaskDueSubmissions()
    {
        $project = Project::factory(['framework_key' => 'terrafund'])->create();

        CreateTaskDueJob::dispatchSync('terrafund');

        $dueAt = Carbon::now()->addMonth()->startOfDay()->toDateTimeString();

        $this->assertDatabaseHas(Task::class, [
            'project_id' => $project->id,
            'due_at' => $dueAt,
            'status' => TaskStatusStateMachine::DUE,
        ]);

        $this->assertDatabaseHas(ProjectReport::class, [
            'project_id' => $project->id,
            'due_at' => $dueAt,
            'framework_key' => 'terrafund',
            'status' => ReportStatusStateMachine::DUE,
        ]);

    }
}
