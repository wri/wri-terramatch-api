<?php

namespace Tests\V2\Invasives;

use App\Models\User;
use App\Models\V2\Invasive;
use App\Models\V2\Organisation;
use App\Models\V2\Projects\Project;
use App\Models\V2\Sites\Site;
// use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetInvasivesForEntityControllerTest extends TestCase
{
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

        $site = Site::factory()->create([
            'project_id' => $project->id,
            'framework_key' => 'ppc',
            'status' => Site::STATUS_STARTED,
        ]);

        Invasive::factory()->create([
            'invasiveable_type' => Site::class,
            'invasiveable_id' => $site->id,
        ]);

        $uri = '/api/v2/invasives/sites/' . $site->uuid;

        $this->actingAs($user)
            ->getJson($uri)
            ->assertStatus(403);

        $this->actingAs($owner)
            ->getJson($uri)
            ->assertSuccessful();
    }
}
