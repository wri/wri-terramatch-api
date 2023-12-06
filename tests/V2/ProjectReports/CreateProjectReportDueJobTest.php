<?php

namespace Tests\Feature\ProjectReports;

use App\Jobs\V2\CreateProjectReportDueJob;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Tasks\Task;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateProjectReportDueJobTest extends TestCase
{
    use RefreshDatabase;

    public function testCreateProjectDueSubmissions()
    {
        $project = Project::factory(['framework_key' => 'terrafund'])->create();

        CreateProjectReportDueJob::dispatchSync('terrafund');

        $this->assertDatabaseHas(ProjectReport::class, [
            'project_id' => $project->id,
            'framework_key' => 'terrafund',
            'due_at' => Carbon::now()->addMonth()->startOfDay()->toDateTimeString(),
            'status' => ProjectReport::STATUS_DUE,
        ]);

        $this->assertDatabaseHas(Task::class, [
            'project_id' => $project->id,
            'status' => Task::STATUS_DUE,
        ]);
    }
}
