<?php

namespace Tests\V2\UpdateRequests;

use App\Models\V2\Organisation;
use App\Models\V2\Projects\Project;
use App\Models\V2\Sites\Site;
use App\Models\V2\UpdateRequests\UpdateRequest;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminViewUpdateRequestControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoke_action(): void
    {
        $organisation = Organisation::factory()->create();
        $project = Project::factory()->create([
            'framework_key' => 'ppc',
            'organisation_id' => $organisation->id,
        ]);

        $site = Site::factory()->create([
            'framework_key' => 'ppc',
            'project_id' => $project->id,
        ]);

        $updateRequest = UpdateRequest::factory()->create([
            'framework_key' => 'ppc',
            'organisation_id' => $organisation->id,
            'project_id' => $project->id,
            'updaterequestable_type' => Site::class,
            'updaterequestable_id' => $site->id,
        ]);

        $owner = User::factory()->create(['organisation_id' => $organisation->id]);
        $random = User::factory()->create();
        $tfAdmin = User::factory()->terrafundAdmin()->create();
        $ppcAdmin = User::factory()->ppcAdmin()->create();

        $uri = '/api/v2/update-requests/' . $updateRequest->uuid;

        $this->actingAs($random)
            ->getJson($uri)
            ->assertStatus(403);

        $this->actingAs($tfAdmin)
            ->getJson($uri)
            ->assertStatus(403);

        $this->actingAs($owner)
            ->getJson($uri)
            ->assertSuccessful();

        $this->actingAs($ppcAdmin)
            ->getJson($uri)
            ->assertSuccessful();
    }
}
