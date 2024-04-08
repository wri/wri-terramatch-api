<?php

namespace Tests\V2\Invasives;

use App\Models\User;
use App\Models\V2\Organisation;
use App\Models\V2\Projects\Project;
use App\Models\V2\Sites\Site;
use App\StateMachines\EntityStatusStateMachine;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreInvasiveControllerTest extends TestCase
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

        $payload = [
            'model_type' => 'site',
            'model_uuid' => $site->uuid,
            'name' => 'testing invasive',
            'type' => 'common',
        ];

        $uri = '/api/v2/invasives';

        $this->actingAs($user)
            ->postJson($uri, $payload)
            ->assertStatus(403);

        $this->actingAs($owner)
            ->postJson($uri, $payload)
            ->assertSuccessful()
            ->assertJsonPath('data.type', 'common')
            ->assertJsonPath('data.name', 'testing invasive');
    }
}
