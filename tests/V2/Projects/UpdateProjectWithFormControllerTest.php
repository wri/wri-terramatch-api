<?php

namespace Tests\V2\Projects;

use App\Helpers\CustomFormHelper;
use App\Models\User;
use App\Models\V2\Organisation;
use App\Models\V2\Projects\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
//use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class UpdateProjectWithFormControllerTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function test_invoke_action()
    {
        //        Artisan::call('v2migration:roles --fresh');
        $tfAdmin = User::factory()->admin()->create();
        $tfAdmin->givePermissionTo('framework-terrafund');

        $ppcAdmin = User::factory()->admin()->create();
        $ppcAdmin->givePermissionTo('framework-ppc');

        $organisation = Organisation::factory()->create();
        $owner = User::factory()->create(['organisation_id' => $organisation->id]);
        $owner->givePermissionTo('manage-own');

        $user = User::factory()->create();

        $project = Project::factory()->create([
            'organisation_id' => $organisation->id,
            'framework_key' => 'ppc',
            'status' => Project::STATUS_STARTED,
        ]);

        $form = CustomFormHelper::generateFakeForm('project', 'ppc');

        $answers = [];

        foreach ($form->sections()->first()->questions as $question) {
            if ($question->input_type == 'conditional') {
                foreach ($question->children as $child) {
                    $answers1[$child->uuid] = '* testing conditional *';
                    $fragment[$child->uuid] = '* testing conditional *';
                }
            }

            if ($question->linked_field_key == 'pro-name') {
                $answers[$question->uuid] = '* testing name updated *';
            }
        }

        $payload = ['answers' => $answers];
        $uri = '/api/v2/forms/projects/' . $project->uuid;

        $this->actingAs($user)
            ->putJson($uri, $payload)
            ->assertStatus(403);

        $this->actingAs($tfAdmin)
            ->putJson($uri, $payload)
            ->assertStatus(403);

        $this->actingAs($ppcAdmin)
            ->putJson($uri, $payload)
            ->assertSuccessful();

        $this->actingAs($owner)
            ->putJson($uri, $payload)
            ->assertSuccessful();

        //        $updated = $project->fresh();
        //        $this->assertEquals($updated->name, '* testing name updated *');
    }

    public function test_update_request()
    {
        //        Artisan::call('v2migration:roles --fresh');
        $organisation = Organisation::factory()->create();
        $owner = User::factory()->create(['organisation_id' => $organisation->id]);
        $owner->givePermissionTo('manage-own');

        $project = Project::factory()->create([
            'organisation_id' => $organisation->id,
            'framework_key' => 'ppc',
            'status' => Project::STATUS_APPROVED,
        ]);

        $form = CustomFormHelper::generateFakeForm('project', 'ppc');

        $answers = [];

        foreach ($form->sections()->first()->questions as $question) {
            if ($question->linked_field_key == 'pro-name') {
                $answers[$question->uuid] = '* testing name updated *';
            }

            if ($question->input_type == 'conditional') {
                foreach ($question->children as $child) {
                    $answers1[$child->uuid] = '* testing conditional *';
                    $fragment[$child->uuid] = '* testing conditional *';
                }
            }
        }

        $uri = '/api/v2/forms/projects/' . $project->uuid;

        $this->actingAs($owner)
            ->putJson($uri, ['answers' => $answers])
            ->assertSuccessful()
            ->assertJsonFragment($answers);
    }
}
