<?php

namespace Tests\V2\Projects;

use App\Helpers\CustomFormHelper;
use App\Models\V2\Organisation;
use App\Models\V2\Projects\Project;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ViewProjectControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_invoke_action()
    {
        $tfAdmin = User::factory()->terrafundAdmin()->create();
        $ppcAdmin = User::factory()->ppcAdmin()->create();
        $user = User::factory()->create();

        $organisation = Organisation::factory()->create();
        $owner = User::factory()->create(['organisation_id' => $organisation->id]);

        $project = Project::factory()->create([
            'organisation_id' => $organisation->id,
            'framework_key' => 'terrafund',
        ]);

        $uri = '/api/v2/projects/' . $project->uuid;

        $this->actingAs($user)
            ->getJson($uri)
            ->assertStatus(403);

        $this->actingAs($ppcAdmin)
            ->getJson($uri)
            ->assertStatus(403);

        $this->actingAs($owner)
            ->getJson($uri)
            ->assertSuccessful();
    }

    public function test_it_does_not_return_soft_deleted_projects()
    {
        $organisation = Organisation::factory()->create();
        $user = User::factory()->create();
        $owner = User::factory()->create(['organisation_id' => $organisation->id]);
        $project = Project::factory()->create(['organisation_id' => $organisation->id]);
        CustomFormHelper::generateFakeForm('project', 'ppc');

        $project->delete();

        $uri = '/api/v2/projects/' . $project->uuid;

        $this->actingAs($user)
            ->getJson($uri)
            ->assertStatus(404);

        $this->actingAs($owner)
            ->getJson($uri)
            ->assertStatus(404);
    }
}
