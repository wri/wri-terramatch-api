<?php

namespace Tests\V2\ProjectReports;

use App\Models\User;
use App\Models\V2\Organisation;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\StateMachines\EntityStatusStateMachine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class AdminStatusProjectReportControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoke_action(): void
    {
        Artisan::call('v2migration:roles');
        $organisation = Organisation::factory()->create();
        $project = Project::factory()->create([
            'framework_key' => 'ppc',
            'organisation_id' => $organisation->id,
        ]);

        $report = ProjectReport::factory()->create([
            'project_id' => $project->id,
            'framework_key' => 'ppc',
            'status' => EntityStatusStateMachine::AWAITING_APPROVAL,
        ]);

        $owner = User::factory()->create(['organisation_id' => $organisation->id]);
        $owner->givePermissionTo('manage-own');

        $random = User::factory()->create();
        $random->givePermissionTo('manage-own');

        $tfAdmin = User::factory()->admin()->create();
        $tfAdmin->givePermissionTo('framework-terrafund');

        $ppcAdmin = User::factory()->admin()->create();
        $ppcAdmin->givePermissionTo('framework-ppc');

        $payload = ['feedback' => 'testing more info'];
        $uri = '/api/v2/admin/project-reports/' . $report->uuid . '/moreinfo';

        $this->actingAs($random)
            ->putJson($uri, $payload)
            ->assertStatus(403);

        $this->actingAs($tfAdmin)
            ->putJson($uri, $payload)
            ->assertStatus(403);

        $this->actingAs($owner)
            ->putJson($uri, $payload)
            ->assertStatus(403);

        $this->actingAs($ppcAdmin)
            ->putJson($uri, $payload)
            ->assertSuccessful()
            ->assertJsonFragment(['status' => EntityStatusStateMachine::NEEDS_MORE_INFORMATION]);
    }
}
