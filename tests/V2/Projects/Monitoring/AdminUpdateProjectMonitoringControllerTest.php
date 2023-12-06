<?php

namespace Tests\V2\Projects\Monitoring;

use App\Models\User;
use App\Models\V2\Organisation;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectMonitoring;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class AdminUpdateProjectMonitoringControllerTest extends TestCase
{
    use RefreshDatabase;

    private $owner;

    private $projectMonitoring;

    private $project;

    public function setUp(): void
    {
        parent::setUp();

        $organisation = Organisation::factory()->create();

        Artisan::call('v2migration:roles --fresh');
        $this->owner = User::factory()->admin()->create(['organisation_id' => $organisation->id]);
        $this->owner->givePermissionTo('manage-own');

        $this->project = Project::factory()->create([
            'organisation_id' => $organisation->id,
        ]);
        $this->projectMonitoring = ProjectMonitoring::factory()->create([
            'project_id' => $this->project->id,
        ]);
    }

    public function test_invoke_action()
    {
        $this->assertDatabaseCount('v2_project_monitorings', 1);

        $user = User::factory()->create();
        $admin = User::factory()->admin()->create();

        $payload = [
            'status' => 'deactivated',
            'tree_count' => 12.22,
            'tree_cover' => 13.22,
            'field_tree_count' => 113.31,
            'field_tree_regenerated' => 114.31,
            'start_date' => now()->subDay()->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
        ];

        $this->actingAs($user)
            ->putJson('/api/v2/admin/project-monitorings/' . $this->projectMonitoring->uuid, $payload)
            ->assertStatus(403);

        $this->actingAs($admin)
            ->putJson('/api/v2/admin/project-monitorings/' . $this->projectMonitoring->uuid, $payload)
            ->assertStatus(403);

        $this->actingAs($this->owner)
            ->putJson('/api/v2/admin/project-monitorings/' . $this->projectMonitoring->uuid, $payload)
            ->assertStatus(200)
            ->assertJsonFragment([
                'status' => $payload['status'],
                'tree_count' => $payload['tree_count'],
                'tree_cover' => $payload['tree_cover'],
                'field_tree_count' => $payload['field_tree_count'],
                'field_tree_regenerated' => $payload['field_tree_regenerated'],
            ]);

        $this->assertDatabaseCount('v2_project_monitorings', 1);
        $this->assertDatabaseHas('v2_project_monitorings', [
            'id' => $this->projectMonitoring->id,
            'uuid' => $this->projectMonitoring->uuid,
            'project_id' => $this->project->id,
            'status' => $payload['status'],
            'tree_count' => $payload['tree_count'],
            'tree_cover' => $payload['tree_cover'],
            'field_tree_count' => $payload['field_tree_count'],
            'field_tree_regenerated' => $payload['field_tree_regenerated'],
        ]);
    }
}
