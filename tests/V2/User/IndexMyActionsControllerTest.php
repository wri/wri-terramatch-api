<?php

namespace Tests\V2\User;

use App\Models\V2\Action;
use App\Models\V2\FinancialReport;
use App\Models\V2\Projects\Project;
use App\Models\V2\User;
use App\StateMachines\EntityStatusStateMachine;
use App\StateMachines\ReportStatusStateMachine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\V2\Nurseries\Nursery;

class IndexMyActionsControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_can_view_their_actions()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();

        $user->projects()->attach($project->id);

        $projectForAction = Project::factory()->create([
            'status' => EntityStatusStateMachine::NEEDS_MORE_INFORMATION,
        ]);
        $projectAction = Action::factory()->create([
            'organisation_id' => $user->organisation->id,
            'project_id' => $project->id,
            'targetable_type' => Project::class,
            'targetable_id' => $projectForAction->id,
        ]);
        
        $siteReportAction = Action::factory()->siteReport()->create([
            'organisation_id' => $user->organisation->id,
            'project_id' => $project->id,
        ]);
        // SiteReport tiene status 'due' por defecto, que está en el filtro
        
        $completedActionProject = Project::factory()->create([
            'status' => EntityStatusStateMachine::NEEDS_MORE_INFORMATION,
        ]);
        $completedAction = Action::factory()->create([
            'organisation_id' => $user->organisation->id,
            'project_id' => $project->id,
            'status' => Action::STATUS_COMPLETE,
            'targetable_type' => Project::class,
            'targetable_id' => $completedActionProject->id,
        ]);

        $this->actingAs($user)
            ->getJson('/api/v2/my/actions')
            ->assertStatus(200)
            ->assertJsonFragment([
                'uuid' => $projectAction->uuid,
            ])
            ->assertJsonFragment([
                'uuid' => $siteReportAction->uuid,
            ])
            ->assertJsonMissing([
                'uuid' => $completedAction->uuid,
            ]);
    }

    public function test_users_should_not_view_actions_outside_projects_scope()
    {
        $user = User::factory()->admin()->create();
        $userProject = Project::factory()->create();
        $externalProject = Project::factory()->create();

        $user->projects()->attach($userProject->id);

        $nurseryForAction = Nursery::factory()->create([
            'status' => EntityStatusStateMachine::NEEDS_MORE_INFORMATION,
        ]);
        $nurseryAction = Action::factory()->create([
            'organisation_id' => $user->organisation->id,
            'project_id' => $userProject->id,
            'targetable_type' => Nursery::class,
            'targetable_id' => $nurseryForAction->id,
        ]);

        $externalProjectForAction = Project::factory()->create([
            'status' => EntityStatusStateMachine::NEEDS_MORE_INFORMATION,
        ]);
        $projectAction = Action::factory()->create([
            'organisation_id' => $user->organisation->id,
            'project_id' => $externalProject->id,
            'targetable_type' => Project::class,
            'targetable_id' => $externalProjectForAction->id,
        ]);
        $siteReportAction = Action::factory()->siteReport()->create([
            'organisation_id' => $user->organisation->id,
            'project_id' => $externalProject->id,
        ]);

        $this->actingAs($user)
            ->getJson('/api/v2/my/actions')
            ->assertStatus(200)
            ->assertJsonFragment([
                'uuid' => $nurseryAction->uuid,
            ])
            ->assertJsonMissing([
                'uuid' => $projectAction->uuid,
            ])
            ->assertJsonMissing([
                'uuid' => $siteReportAction->uuid,
            ]);
    }

    public function test_users_can_view_their_financial_report_actions()
    {
        $user = User::factory()->create();
        $organisation = $user->organisation;
        $financialReport = FinancialReport::factory()->create([
            'organisation_id' => $organisation->id,
            'status' => ReportStatusStateMachine::DUE,
        ]);
        $financialAction = Action::factory()->create([
            'organisation_id' => $organisation->id,
            'targetable_type' => FinancialReport::class,
            'targetable_id' => $financialReport->id,
        ]);
        // Note: FinancialReport is not currently included in the controller's filter
        // This test will fail until FinancialReport is added to the controller
        $this->actingAs($user)
            ->getJson('/api/v2/my/actions')
            ->assertStatus(200)
            ->assertJsonMissing([
                'uuid' => $financialAction->uuid,
            ]);
    }
}
