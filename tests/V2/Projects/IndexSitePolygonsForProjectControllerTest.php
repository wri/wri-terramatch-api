<?php

namespace Tests\V2\Projects;

use App\Models\V2\Organisation;
use App\Models\V2\Projects\Project;
use App\Models\V2\Sites\Site;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class IndexSitePolygonsForProjectControllerTest extends TestCase
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

        $site1 = Site::factory()->create([
            'name' => 'testing nope',
            'project_id' => $project->id,
            'framework_key' => 'ppc',
        ]);

        $site2 = Site::factory()->create([
            'name' => 'testing here',
            'project_id' => $project->id,
            'framework_key' => 'ppc',
        ]);

        Site::factory()->create();

        $uri = '/api/v2/projects/' . $project->uuid . '/site-polygons';

        $this->actingAs($user)
            ->getJson($uri)
            ->assertStatus(403);

        $this->actingAs($tfAdmin)
            ->getJson($uri)
            ->assertStatus(403);

        $this->actingAs($owner)
            ->getJson($uri)
            ->assertSuccessful()
            ->assertJsonCount(2, 'data');

        $this->actingAs($ppcAdmin)
            ->getJson($uri)
            ->assertSuccessful()
            ->assertJsonCount(2, 'data');

        $this->actingAs($ppcAdmin)
            ->getJson($uri . '?search=here')
            ->assertSuccessful()
            ->assertJsonCount(1, 'data');
    }
}
