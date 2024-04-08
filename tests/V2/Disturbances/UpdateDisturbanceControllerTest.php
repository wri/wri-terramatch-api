<?php

namespace Tests\V2\Disturbances;

use App\Models\User;
use App\Models\V2\Disturbance;
use App\Models\V2\Organisation;
use App\Models\V2\Projects\Project;
use App\Models\V2\Sites\Site;
use App\StateMachines\EntityStatusStateMachine;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateDisturbanceControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_strata()
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

        $disturbance = Disturbance::factory()->create([
            'disturbanceable_type' => Site::class,
            'disturbanceable_id' => $site->id,
        ]);

        $payload = [
            'intensity' => 'low',
            'description' => 'testing disturbance',
        ];

        $uri = '/api/v2/disturbances/' . $disturbance->uuid;

        $this->actingAs($user)
            ->patchJson($uri, $payload)
            ->assertStatus(403);

        $this->actingAs($owner)
            ->patchJson($uri, $payload)
            ->assertSuccessful()
            ->assertJsonPath('data.intensity', 'low')
            ->assertJsonPath('data.description', 'testing disturbance');
    }
}
