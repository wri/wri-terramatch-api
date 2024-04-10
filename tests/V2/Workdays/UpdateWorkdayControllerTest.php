<?php

namespace Tests\V2\Workdays;

use App\Models\User;
use App\Models\V2\Organisation;
use App\Models\V2\Projects\Project;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;
use App\Models\V2\Workdays\Workday;
use App\StateMachines\EntityStatusStateMachine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class UpdateWorkdayControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoke_action()
    {
        Artisan::call('v2migration:roles');
        $organisation = Organisation::factory()->create();
        $owner = User::factory()->create(['organisation_id' => $organisation->id]);
        $owner->givePermissionTo('manage-own');

        $user = User::factory()->create();

        $project = Project::factory()->create([
            'organisation_id' => $organisation->id,
            'framework_key' => 'ppc',
        ]);

        $site = Site::factory()->create([
            'project_id' => $project->id,
            'framework_key' => 'ppc',
            'status' => EntityStatusStateMachine::STARTED,
        ]);

        $report = SiteReport::factory()->create([
            'site_id' => $site->id,
            'framework_key' => 'ppc',
            'status' => EntityStatusStateMachine::STARTED,
        ]);

        $workday = Workday::factory()->create([
            'workdayable_type' => SiteReport::class,
            'workdayable_id' => $report->id,
        ]);

        $payload = [
            'amount' => 26,
            'gender' => 'female',
        ];

        $uri = '/api/v2/workdays/' . $workday->uuid;

        $this->actingAs($user)
            ->patchJson($uri, $payload)
            ->assertStatus(403);

        $this->actingAs($owner)
            ->patchJson($uri, $payload)
            ->assertSuccessful()
            ->assertJsonPath('data.amount', 26)
            ->assertJsonPath('data.gender', 'female');
    }
}
