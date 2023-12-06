<?php

namespace Tests\V2\Tasks;

use App\Helpers\CustomFormHelper;
use App\Models\User;
use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Nurseries\NurseryReport;
use App\Models\V2\Organisation;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;
use App\Models\V2\Tasks\Task;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
//use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class SubmitTaskReportsControllerTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function test_invoke_action()
    {
        //        Artisan::call('v2migration:roles --fresh');

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
            'framework_key' => 'ppc',
            'due_at' => $date,
            'status' => ProjectReport::STATUS_DUE,
        ]);

        $siteReport = SiteReport::factory()->create([
            'site_id' => $site->id,
            'framework_key' => 'ppc',
            'due_at' => $date,
            'status' => SiteReport::STATUS_DUE,
        ]);

        $nurseryReport = NurseryReport::factory()->create([
            'nursery_id' => $nursery->id,
            'framework_key' => 'ppc',
            'due_at' => $date,
            'status' => NurseryReport::STATUS_DUE,
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
        $this->assertEquals(ProjectReport::STATUS_AWAITING_APPROVAL, $projectReport->status);
        $this->assertEquals(ProjectReport::COMPLETION_STATUS_COMPLETE, $projectReport->completion_status);
        $this->assertEquals(100, $projectReport->completion);

        $siteReport->refresh();
        $this->assertEquals(SiteReport::STATUS_AWAITING_APPROVAL, $siteReport->status);
        $this->assertEquals(SiteReport::COMPLETION_STATUS_COMPLETE, $siteReport->completion_status);
        $this->assertEquals(100, $siteReport->completion);

        $nurseryReport->refresh();
        $this->assertEquals(NurseryReport::STATUS_AWAITING_APPROVAL, $nurseryReport->status);
        $this->assertEquals(NurseryReport::COMPLETION_STATUS_COMPLETE, $nurseryReport->completion_status);
        $this->assertEquals(100, $nurseryReport->completion);

        $updatedTask = Task::find($task->id) ;
        $this->assertEquals(Task::STATUS_COMPLETE, $updatedTask->status);
    }
}
