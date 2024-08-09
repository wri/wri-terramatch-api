<?php

namespace Tests\V2\SiteReports;

use App\Helpers\CustomFormHelper;
use App\Models\V2\Organisation;
use App\Models\V2\Projects\Project;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;
use App\Models\V2\User;
use App\StateMachines\EntityStatusStateMachine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UpdateSiteReportWithFormControllerTest extends TestCase
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

        $site = Site::factory()->create([
            'project_id' => $project->id,
            'framework_key' => 'ppc',
        ]);

        $report = SiteReport::factory()->create([
            'site_id' => $site->id,
            'framework_key' => 'ppc',
            'status' => EntityStatusStateMachine::STARTED,
        ]);

        $form = CustomFormHelper::generateFakeForm('site-report', 'ppc');

        $answers = [];

        foreach ($form->sections()->first()->questions as $question) {
            if ($question->input_type == 'conditional') {
                foreach ($question->children as $child) {
                    $answers1[$child->uuid] = '* testing conditional *';
                    $fragment[$child->uuid] = '* testing conditional *';
                }
            }

            if ($question->linked_field_key == 'site-rep-title') {
                $answers[$question->uuid] = '* testing title updated *';
            }
        }

        $payload = ['answers' => $answers];
        $uri = '/api/v2/forms/site-reports/' . $report->uuid;

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

        $updated = $report->fresh();

        $this->assertEquals($updated->title, '* testing title updated *');
    }

    public function test_update_request()
    {
        $organisation = Organisation::factory()->create();
        $owner = User::factory()->create(['organisation_id' => $organisation->id]);

        $project = Project::factory()->create([
            'organisation_id' => $organisation->id,
            'framework_key' => 'ppc',
        ]);

        $site = Site::factory()->create([
            'project_id' => $project->id,
            'framework_key' => 'ppc',
        ]);

        $report = SiteReport::factory()->create([
            'site_id' => $site->id,
            'framework_key' => 'ppc',
            'status' => EntityStatusStateMachine::APPROVED,
        ]);

        $form = CustomFormHelper::generateFakeForm('site-report', 'ppc');

        $answers = [];

        foreach ($form->sections()->first()->questions as $question) {
            if ($question->input_type == 'conditional') {
                foreach ($question->children as $child) {
                    $answers1[$child->uuid] = '* testing conditional *';
                    $fragment[$child->uuid] = '* testing conditional *';
                }
            }

            if ($question->linked_field_key == 'site-rep-title') {
                $answers[$question->uuid] = '* testing title updated *';
            }
        }
        $uri = '/api/v2/forms/site-reports/' . $report->uuid;

        $this->actingAs($owner)
            ->putJson($uri, ['answers' => $answers])
            ->assertSuccessful()
            ->assertJsonFragment($answers);
    }
}
