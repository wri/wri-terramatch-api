<?php

namespace Tests\V2\SiteReports;

use App\Models\Organisation;
use App\Models\User;
use App\Models\V2\Projects\Project;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;
use App\StateMachines\EntityStatusStateMachine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class SiteReportsViaSiteControllerTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function test_invoke_action()
    {
        Artisan::call('v2migration:roles');
        $tfAdmin = User::factory()->admin()->create();
        $tfAdmin->givePermissionTo('framework-terrafund');

        $ppcAdmin = User::factory()->admin()->create();
        $ppcAdmin->givePermissionTo('framework-ppc');

        $user = User::factory()->create();

        $organisation = Organisation::factory()->create();
        $owner = User::factory()->create(['organisation_id' => $organisation->id]);
        $owner->givePermissionTo('manage-own');


        $project = Project::factory()->create(['organisation_id' => $organisation->id, 'framework_key' => 'ppc']);
        $site = Site::factory()->create(['project_id' => $project->id, 'framework_key' => 'ppc']);

        SiteReport::query()->delete();
        SiteReport::factory()->count(4)->create([
            'site_id' => $site->id,
            'framework_key' => 'ppc',
            'status' => EntityStatusStateMachine::APPROVED,
        ]);
        SiteReport::factory()->count(2)->create(['framework_key' => 'ppc']);

        $uri = '/api/v2/sites/' . $site->uuid . '/reports';

        $this->actingAs($user)
            ->getJson($uri)
            ->assertStatus(403);

        $this->actingAs($tfAdmin)
            ->getJson($uri)
            ->assertStatus(403);

        $this->actingAs($owner)
            ->getJson($uri)
            ->assertSuccessful()
            ->assertJsonCount(4, 'data');

        $this->actingAs($ppcAdmin)
            ->getJson($uri)
            ->assertSuccessful()
            ->assertJsonCount(4, 'data');
    }
}
