<?php

namespace Tests\V2\UpdateRequests;

use App\Helpers\CustomFormHelper;
use App\Models\V2\Organisation;
use App\Models\V2\Projects\Project;
use App\Models\V2\Sites\Site;
use App\Models\V2\UpdateRequests\UpdateRequest;
use App\Models\V2\User;
use App\StateMachines\EntityStatusStateMachine;
use App\StateMachines\UpdateRequestStatusStateMachine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminStatusUpdateRequestControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoke_action_permissions(): void
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
            'status' => UpdateRequestStatusStateMachine::AWAITING_APPROVAL,
        ]);

        $owner = User::factory()->create(['organisation_id' => $organisation->id]);
        $random = User::factory()->create();
        $tfAdmin = User::factory()->terrafundAdmin()->create();
        $ppcAdmin = User::factory()->ppcAdmin()->create();

        $payload = ['feedback' => 'testing more information', 'feedback_fields' => []];
        $uri = '/api/v2/admin/update-requests/' . $updateRequest->uuid . '/moreinfo';

        $this->actingAs($random)
            ->putJson($uri, $payload)
            ->assertStatus(403);

        $this->actingAs($tfAdmin)
            ->putJson($uri, $payload)
            ->assertStatus(403);

        $this->actingAs($owner)
            ->putJson($uri, $payload)
            ->assertStatus(403);

        $this->actingAs($ppcAdmin)
            ->putJson($uri, $payload)
            ->assertSuccessful();
    }

    public function test_flow(): void
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
            'status' => UpdateRequestStatusStateMachine::AWAITING_APPROVAL,
        ]);

        $ppcAdmin = User::factory()->ppcAdmin()->create();

        $uri = '/api/v2/admin/update-requests/' . $updateRequest->uuid;

        $this->actingAs($ppcAdmin)
            ->putJson($uri . '/moreinfo', ['feedback' => 'blah blah blah', 'feedback_fields' => []])
            ->assertSuccessful()
            ->assertJsonFragment(['status' => UpdateRequestStatusStateMachine::NEEDS_MORE_INFORMATION]);
    }

    public function test_approve_updates(): void
    {
        $organisation = Organisation::factory()->create();
        $owner = User::factory()->create(['organisation_id' => $organisation->id]);

        $ppcAdmin = User::factory()->ppcAdmin()->create();

        $project = Project::factory()->create([
            'organisation_id' => $organisation->id,
            'framework_key' => 'ppc',
        ]);

        $site = Site::factory()->create([
            'project_id' => $project->id,
            'framework_key' => 'ppc',
            'status' => EntityStatusStateMachine::APPROVED,
        ]);

        $form = CustomFormHelper::generateFakeForm('site', 'ppc');

        $answers = [];

        foreach ($form->sections()->first()->questions as $question) {
            if ($question->linked_field_key == 'site-name') {
                $answers[$question->uuid] = '* testing name updated *';
            }
        }

        $updateRequest = UpdateRequest::factory()->create([
            'framework_key' => 'ppc',
            'organisation_id' => $organisation->id,
            'project_id' => $project->id,
            'updaterequestable_type' => Site::class,
            'updaterequestable_id' => $site->id,
            'status' => UpdateRequestStatusStateMachine::AWAITING_APPROVAL,
            'content' => $answers,
        ]);

        $this->actingAs($ppcAdmin)
            ->putJson('/api/v2/admin/update-requests/' . $updateRequest->uuid . '/approve', [])
            ->assertSuccessful()
            ->assertJsonFragment(['status' => UpdateRequestStatusStateMachine::APPROVED]);

        //        $updated = Site::find($site->id);
        //        $this->assertEquals('* testing name updated *', $updated->name);
    }
}
