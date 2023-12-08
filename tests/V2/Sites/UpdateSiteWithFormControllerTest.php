<?php

namespace Tests\V2\Sites;

use App\Helpers\CustomFormHelper;
use App\Models\User;
use App\Models\V2\Organisation;
use App\Models\V2\Projects\Project;
use App\Models\V2\Sites\Site;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
//use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class UpdateSiteWithFormControllerTest extends TestCase
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
        ]);

        $site = Site::factory()->create([
            'project_id' => $project->id,
            'framework_key' => 'ppc',
            'status' => Site::STATUS_STARTED,
        ]);

        $form = CustomFormHelper::generateFakeForm('site', 'ppc');

        $answers = [];

        foreach ($form->sections()->first()->questions as $question) {
            if ($question->input_type == 'conditional') {
                foreach ($question->children as $child) {
                    $answers1[$child->uuid] = '* testing conditional *';
                    $fragment[$child->uuid] = '* testing conditional *';
                }
            }

            if ($question->linked_field_key == 'site-name') {
                $answers[$question->uuid] = '* testing name updated *';
            }
        }

        $payload = ['answers' => $answers];
        $uri = '/api/v2/forms/sites/' . $site->uuid;

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

        $updated = $site->fresh();

        $this->assertEquals($updated->name, '* testing name updated *');
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
        ]);

        $site = Site::factory()->create([
            'project_id' => $project->id,
            'framework_key' => 'ppc',
            'status' => Site::STATUS_APPROVED,
        ]);

        $form = CustomFormHelper::generateFakeForm('site', 'ppc');

        $answers = [];

        foreach ($form->sections()->first()->questions as $question) {
            if ($question->input_type == 'conditional') {
                foreach ($question->children as $child) {
                    $answers1[$child->uuid] = '* testing conditional *';
                    $fragment[$child->uuid] = '* testing conditional *';
                }
            }

            if ($question->linked_field_key == 'site-name') {
                $answers[$question->uuid] = '* testing name updated *';
            }
        }

        $uri = '/api/v2/forms/sites/' . $site->uuid;

        $this->actingAs($owner)
            ->putJson($uri, ['answers' => $answers])
            ->assertSuccessful()
            ->assertJsonFragment($answers);
    }
}
