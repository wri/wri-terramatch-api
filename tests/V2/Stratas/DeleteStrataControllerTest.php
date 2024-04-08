<?php

namespace Tests\V2\Stratas;

use App\Models\User;
use App\Models\V2\Organisation;
use App\Models\V2\Projects\Project;
use App\Models\V2\Sites\Site;
use App\Models\V2\Stratas\Strata;
use App\StateMachines\EntityStatusStateMachine;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteStrataControllerTest extends TestCase
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

        $strata = Strata::factory()->create([
            'stratasable_type' => Site::class,
            'stratasable_id' => $site->id,
        ]);

        $uri = '/api/v2/stratas/' . $strata->uuid;

        $this->actingAs($user)
            ->deleteJson($uri)
            ->assertStatus(403);

        $this->actingAs($owner)
            ->deleteJson($uri)
            ->assertSuccessful();
    }
}
