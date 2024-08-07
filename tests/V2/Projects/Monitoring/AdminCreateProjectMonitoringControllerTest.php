<?php

namespace Tests\V2\Projects\Monitoring;

use App\Models\V2\Organisation;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectMonitoring;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCreateProjectMonitoringControllerTest extends TestCase
{
    use RefreshDatabase;

    private $admin;

    private $project;

    public function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();

        $organisation = Organisation::factory()->create();
        $this->project = Project::factory()->create([
            'organisation_id' => $organisation->id,
        ]);
    }

    public function test_invoke_action()
    {
        $this->assertDatabaseCount('v2_project_monitorings', 0);

        $user = User::factory()->create();

        $payload = [
            'project_uuid' => $this->project->uuid,
            'tree_count' => 12.22,
            'tree_cover' => 12.22,
            'field_tree_count' => 112.312,
            'field_tree_regenerated' => 112.312,
            'start_date' => now()->subDay()->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
        ];

        $this->actingAs($user)
            ->postJson('/api/v2/admin/project-monitorings', $payload)
            ->assertStatus(403);

        $this->actingAs($this->admin)
            ->postJson('/api/v2/admin/project-monitorings', $payload)
            ->assertStatus(201)
            ->assertJsonFragment([
                'status' => ProjectMonitoring::STATUS_ACTIVE,
                'tree_count' => $payload['tree_count'],
                'tree_cover' => $payload['tree_cover'],
                'field_tree_count' => $payload['field_tree_count'],
                'field_tree_regenerated' => $payload['field_tree_regenerated'],
            ]);

        $projectMonitoring = $this->project->monitoring()->first();

        $this->assertDatabaseCount('v2_project_monitorings', 1);
        $this->assertDatabaseHas('v2_project_monitorings', [
            'uuid' => $projectMonitoring->uuid,
            'project_id' => $this->project->id,
            'status' => $projectMonitoring->status,
            'tree_count' => $projectMonitoring->tree_count,
            'tree_cover' => $projectMonitoring->tree_cover,
            'field_tree_count' => $projectMonitoring->field_tree_count,
            'field_tree_regenerated' => $projectMonitoring->field_tree_regenerated,
        ]);
    }
}
