<?php

namespace Tests\V2\Stratas;

use App\Models\User;
use App\Models\V2\Organisation;
use App\Models\V2\Projects\Project;
use App\Models\V2\Sites\Site;
use App\StateMachines\EntityStatusStateMachine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class StoreStrataControllerTest extends TestCase
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
            'extent' => 56,
            'description' => 'testing strata',
        ];

        $uri = '/api/v2/stratas';

        $this->actingAs($user)
            ->postJson($uri, $payload)
            ->assertStatus(403);

        $this->actingAs($owner)
            ->postJson($uri, $payload)
            ->assertStatus(201)
            ->assertJsonFragment([
                'extent' => 56,
                'description' => 'testing strata',
            ]);
    }
}
