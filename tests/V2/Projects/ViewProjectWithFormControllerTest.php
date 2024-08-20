<?php

namespace Tests\V2\Projects;

use App\Helpers\CustomFormHelper;
use App\Models\V2\Organisation;
use App\Models\V2\Projects\Project;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ViewProjectWithFormControllerTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function test_invoke_action()
    {
        $tfAdmin = User::factory()->terrafundAdmin()->create();
        $ppcAdmin = User::factory()->ppcAdmin()->create();
        $user = User::factory()->create();

        $organisation = Organisation::factory()->create();
        $owner = User::factory()->create(['organisation_id' => $organisation->id]);

        $project = Project::factory()->create([
            'organisation_id' => $organisation->id,
            'framework_key' => 'ppc',
        ]);

        CustomFormHelper::generateFakeForm('project', 'ppc');

        $uri = '/api/v2/forms/projects/' . $project->uuid;

        $this->actingAs($user)
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
