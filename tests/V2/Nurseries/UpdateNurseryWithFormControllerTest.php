<?php

namespace Tests\V2\Nurseries;

use App\Helpers\CustomFormHelper;
use App\Models\User;
use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Organisation;
use App\Models\V2\Projects\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
//use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class UpdateNurseryWithFormControllerTest extends TestCase
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

        $nursery = Nursery::factory()->create([
            'project_id' => $project->id,
            'framework_key' => 'ppc',
            'status' => Nursery::STATUS_STARTED,
        ]);

        $form = CustomFormHelper::generateFakeForm('nursery', 'ppc');

        $answers = [];

        foreach ($form->sections()->first()->questions as $question) {
            if ($question->input_type == 'conditional') {
                foreach ($question->children as $child) {
                    $answers1[$child->uuid] = '* testing conditional *';
                    $fragment[$child->uuid] = '* testing conditional *';
                }
            }

            if ($question->linked_field_key == 'nur-name') {
                $answers[$question->uuid] = '* testing name updated *';
            }
        }

        $payload = ['answers' => $answers];
        $uri = '/api/v2/forms/nurseries/' . $nursery->uuid;

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

        $updated = $nursery->fresh();

        $this->assertEquals($updated->name, '* testing name updated *');
    }

    public function test_nursery_update_request()
    {
        //        Artisan::call('v2migration:roles --fresh');

        $organisation = Organisation::factory()->create();
        $owner = User::factory()->create(['organisation_id' => $organisation->id]);
        $owner->givePermissionTo('manage-own');

        $project = Project::factory()->create([
            'organisation_id' => $organisation->id,
            'framework_key' => 'ppc',
        ]);

        $nursery = Nursery::factory()->create([
            'project_id' => $project->id,
            'framework_key' => 'ppc',
            'status' => Nursery::STATUS_APPROVED,
        ]);

        $form = CustomFormHelper::generateFakeForm('nursery', 'ppc');

        $uri = '/api/v2/forms/nurseries/' . $nursery->uuid;

        $answers1 = [];
        $answers2 = [];
        $fragment = [];
        foreach ($form->sections()->first()->questions as $question) {
            if ($question->input_type == 'conditional') {
                foreach ($question->children as $child) {
                    $answers1[$child->uuid] = '* testing conditional *';
                    $fragment[$child->uuid] = '* testing conditional *';
                }
            }

            if ($question->linked_field_key == 'nur-name') {
                $answers1[$question->uuid] = '* testing name updated *';
                $answers2[$question->uuid] = '* testing an further name update *';
                $fragment[$question->uuid] = '* testing an further name update *';
            }
            if ($question->linked_field_key == 'nur-planting_contribution') {
                $answers1[$question->uuid] = 8001;
                $fragment[$question->uuid] = 8001;
            }
            if ($question->linked_field_key == 'nur-seedling_grown') {
                $answers2[$question->uuid] = 123456;
                $fragment[$question->uuid] = 123456;
            }
        }

        $this->actingAs($owner)
            ->putJson($uri, ['answers' => $answers1])
            ->assertSuccessful();

        foreach ($form->sections()->first()->questions as $question) {
            if ($question->linked_field_key == 'nur-name') {
                $answers[$question->uuid] = '* testing an further name update *';
            }
        }

        $this->actingAs($owner)
            ->putJson($uri, ['answers' => $answers2])
            ->assertSuccessful()
            ->assertJsonFragment($fragment);
    }
}
