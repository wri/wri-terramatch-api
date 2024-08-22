<?php

namespace Tests\V2\Projects;

use App\Helpers\CustomFormHelper;
use App\Models\V2\Organisation;
use App\Models\V2\Projects\Project;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SubmitProjectControllerTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function test_invoke_action()
    {
        $organisation = Organisation::factory()->create();
        $owner = User::factory()->create(['organisation_id' => $organisation->id]);

        $user = User::factory()->create();

        $project = Project::factory()->create([
            'organisation_id' => $organisation->id,
            'framework_key' => 'ppc',
        ]);

        CustomFormHelper::generateFakeForm('project', 'ppc');

        $uri = '/api/v2/forms/projects/' . $project->uuid . '/submit';

        $this->actingAs($user)
            ->putJson($uri)
            ->assertStatus(403);

        $this->actingAs($owner)
            ->putJson($uri)
            ->assertSuccessful();
    }
}
