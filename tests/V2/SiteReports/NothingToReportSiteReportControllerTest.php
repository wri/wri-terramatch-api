<?php

namespace Tests\V2\SiteReports;

use App\Models\V2\Organisation;
use App\Models\V2\Projects\Project;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;
use App\Models\V2\User;
use App\StateMachines\EntityStatusStateMachine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class NothingToReportSiteReportControllerTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function test_invoke_action()
    {
        $tfAdmin = User::factory()->terrafundAdmin()->create();
        $ppcAdmin = User::factory()->ppcAdmin()->create();

        $organisation = Organisation::factory()->create();
        $owner = User::factory()->create(['organisation_id' => $organisation->id]);

        $user = User::factory()->create();

        $project = Project::factory()->create([
            'organisation_id' => $organisation->id,
            'framework_key' => 'ppc',
        ]);

        $site = Site::factory()->create([
            'project_id' => $project->id,
            'framework_key' => 'ppc',
        ]);

        $report = SiteReport::factory()->create([
            'site_id' => $site->id,
            'framework_key' => 'ppc',
        ]);

        $uri = '/api/v2/site-reports/' . $report->uuid . '/nothing-to-report';

        $this->actingAs($user)
            ->putJson($uri)
            ->assertStatus(403);

        $this->actingAs($tfAdmin)
            ->putJson($uri)
            ->assertStatus(403);

        $this->actingAs($owner)
            ->putJson($uri)
            ->assertSuccessful()
            ->assertJsonFragment([
                'status' => EntityStatusStateMachine::AWAITING_APPROVAL,
                'nothing_to_report' => true,
            ]);

        $this->actingAs($ppcAdmin)
            ->putJson($uri)
            ->assertSuccessful()
            ->assertJsonFragment([
                'status' => EntityStatusStateMachine::AWAITING_APPROVAL,
                'nothing_to_report' => true,
            ]);
    }
}
