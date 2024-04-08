<?php

namespace Tests\V2\Disturbances;

use App\Models\User;
use App\Models\V2\Organisation;
use App\Models\V2\Projects\Project;
use App\Models\V2\Sites\Site;
use App\StateMachines\EntityStatusStateMachine;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreDisturbanceControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_stratas()
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
            'intensity' => 'high',
            'description' => 'testing disturbance',
        ];

        $uri = '/api/v2/disturbances';

        $this->actingAs($user)
            ->postJson($uri, $payload)
            ->assertStatus(403);

        $this->actingAs($owner)
            ->postJson($uri, $payload)
            ->assertSuccessful()
            ->assertJsonPath('data.intensity', 'high')
            ->assertJsonPath('data.description', 'testing disturbance');
    }
}
