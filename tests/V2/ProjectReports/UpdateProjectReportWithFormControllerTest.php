<?php

namespace Tests\V2\ProjectReports;

use App\Helpers\CustomFormHelper;
use App\Models\V2\Organisation;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\User;
use App\StateMachines\EntityStatusStateMachine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class UpdateProjectReportWithFormControllerTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function test_invoke_action()
    {
        Artisan::call('v2migration:roles');
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

        $report = ProjectReport::factory()->create([
            'project_id' => $project->id,
            'framework_key' => 'ppc',
            'status' => EntityStatusStateMachine::STARTED,
        ]);

        $form = CustomFormHelper::generateFakeForm('project-report', 'ppc', true);

        $answers = [];

        foreach ($form->sections()->first()->questions as $question) {
            if ($question->input_type == 'conditional') {
                foreach ($question->children as $child) {
                    $answers1[$child->uuid] = '* testing conditional *';
                    $fragment[$child->uuid] = '* testing conditional *';
                }
            }

            if ($question->linked_field_key == 'pro-rep-title') {
                $answers[$question->uuid] = '* testing title updated *';
            }
            if ($question->linked_field_key == 'pro-rep-rel-paid-project-management') {
                $answers[$question->uuid] = [[
                    'collection' => 'paid-project-management',
                    'demographics' => [[
                        'type' => 'gender',
                        'name' => 'male',
                        'amount' => 24,
                    ], [
                        'type' => 'age',
                        'name' => 'youth',
                        'amount' => 24,
                    ], [
                        'type' => 'ethnicity',
                        'subtype' => 'indigenous',
                        'name' => 'Ohlone',
                        'amount' => 24,
                    ]],
                ]];
            }
        }

        $payload = ['answers' => $answers];
        $uri = '/api/v2/forms/project-reports/' . $report->uuid;

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
        $this->assertEquals($updated->workdays_paid, 24);
    }

    public function test_update_request()
    {
        Artisan::call('v2migration:roles');
        $organisation = Organisation::factory()->create();
        $owner = User::factory()->create(['organisation_id' => $organisation->id]);
        $owner->givePermissionTo('manage-own');

        $project = Project::factory()->create([
            'organisation_id' => $organisation->id,
            'framework_key' => 'ppc',
        ]);

        $report = ProjectReport::factory()->create([
            'project_id' => $project->id,
            'framework_key' => 'ppc',
            'status' => EntityStatusStateMachine::APPROVED,
        ]);

        $form = CustomFormHelper::generateFakeForm('project-report', 'ppc');

        $answers = [];

        foreach ($form->sections()->first()->questions as $question) {
            if ($question->input_type == 'conditional') {
                foreach ($question->children as $child) {
                    $answers1[$child->uuid] = '* testing conditional *';
                    $fragment[$child->uuid] = '* testing conditional *';
                }
            }

            if ($question->linked_field_key == 'pro-rep-title') {
                $answers[$question->uuid] = '* testing title updated *';
            }
        }

        $uri = '/api/v2/forms/project-reports/' . $report->uuid;

        $this->actingAs($owner)
            ->putJson($uri, ['answers' => $answers])
            ->assertSuccessful()
            ->assertJsonFragment($answers);
    }
}
