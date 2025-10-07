<?php

namespace Tests\V2\User;

use App\Models\V2\Action;
use App\Models\V2\FinancialReport;
use App\Models\V2\Projects\Project;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexMyActionsControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_can_view_their_actions()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();

        $user->projects()->attach($project->id);

        $projectAction = Action::factory()->project()->create([
            'organisation_id' => $user->organisation->id,
            'project_id' => $project->id,
        ]);
        $siteReportAction = Action::factory()->siteReport()->create([
            'organisation_id' => $user->organisation->id,
            'project_id' => $project->id,
        ]);
        $completedAction = Action::factory()->project()->complete()->create([
            'organisation_id' => $user->organisation->id,
            'project_id' => $project->id,
        ]);

        $response = $this->actingAs($user)
            ->getJson('/api/v2/my/actions')
            ->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'message',
                    'job_uuid',
                ],
            ]);

        $responseData = $response->json('data');
        $this->assertNotNull($responseData['job_uuid']);
        $this->assertDatabaseHas('delayed_jobs', [
            'uuid' => $responseData['job_uuid'],
        ]);
    }

    public function test_users_should_not_view_actions_outside_projects_scope()
    {
        $user = User::factory()->admin()->create();
        $userProject = Project::factory()->create();
        $externalProject = Project::factory()->create();

        $user->projects()->attach($userProject->id);

        $nurseryAction = Action::factory()->nursery()->create([
            'organisation_id' => $user->organisation->id,
            'project_id' => $userProject->id,
        ]);

        $projectAction = Action::factory()->project()->create([
            'organisation_id' => $user->organisation->id,
            'project_id' => $externalProject->id,
        ]);
        $siteReportAction = Action::factory()->siteReport()->create([
            'organisation_id' => $user->organisation->id,
            'project_id' => $externalProject->id,
        ]);

        $response = $this->actingAs($user)
            ->getJson('/api/v2/my/actions')
            ->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'message',
                    'job_uuid',
                ],
            ]);

        $responseData = $response->json('data');
        $this->assertNotNull($responseData['job_uuid']);
        $this->assertDatabaseHas('delayed_jobs', [
            'uuid' => $responseData['job_uuid'],
        ]);
    }

    public function test_users_can_view_their_financial_report_actions()
    {
        $user = User::factory()->create();
        $organisation = $user->organisation;
        $financialReport = FinancialReport::factory()->create([
            'organisation_id' => $organisation->id,
        ]);
        $financialAction = Action::factory()->create([
            'organisation_id' => $organisation->id,
            'targetable_type' => FinancialReport::class,
            'targetable_id' => $financialReport->id,
        ]);

        $response = $this->actingAs($user)
            ->getJson('/api/v2/my/actions')
            ->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'message',
                    'job_uuid',
                ],
            ]);

        $responseData = $response->json('data');
        $this->assertNotNull($responseData['job_uuid']);
        $this->assertDatabaseHas('delayed_jobs', [
            'uuid' => $responseData['job_uuid'],
        ]);
    }
}
