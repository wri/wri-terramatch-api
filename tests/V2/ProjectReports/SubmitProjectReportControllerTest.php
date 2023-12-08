<?php

namespace Tests\V2\ProjectReports;

use App\Helpers\CustomFormHelper;
use App\Models\User;
use App\Models\V2\Organisation;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
//use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class SubmitProjectReportControllerTest extends TestCase
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

        $project = Project::factory()->create([
            'organisation_id' => $organisation->id,
            'framework_key' => 'ppc',
        ]);

        $report = ProjectReport::factory()->create([
            'project_id' => $project->id,
            'framework_key' => 'ppc',
        ]);

        CustomFormHelper::generateFakeForm('project-report', 'ppc');

        $uri = '/api/v2/forms/project-reports/' . $report->uuid . '/submit';

        $this->actingAs($user)
            ->putJson($uri)
            ->assertStatus(403);

        $this->actingAs($owner)
            ->putJson($uri)
            ->assertSuccessful();
    }
}
