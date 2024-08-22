<?php

namespace Tests\V2\Nurseries;

use App\Helpers\CustomFormHelper;
use App\Models\V2\Organisation;
use App\Models\V2\Projects\Project;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CreateNurseryControllerWithFormTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function test_a_pd_can_create_a_nursery_with_form()
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

        $form = CustomFormHelper::generateFakeForm('nursery', 'ppc');

        $payload = [
            'parent_entity' => 'project',
            'parent_uuid' => $project->uuid,
            'form_uuid' => $form->uuid,
        ];

        $uri = '/api/v2/forms/nurseries' ;

        $this->actingAs($user)
            ->postJson($uri, $payload)
            ->assertStatus(403);

        $this->actingAs($tfAdmin)
            ->postJson($uri, $payload)
            ->assertStatus(403);

        $this->actingAs($ppcAdmin)
            ->postJson($uri, $payload)
            ->assertStatus(403);

        $this->actingAs($owner)
            ->postJson($uri, $payload)
            ->assertSuccessful();
    }
}
