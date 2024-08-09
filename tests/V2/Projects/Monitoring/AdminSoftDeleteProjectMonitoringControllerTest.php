<?php

namespace Tests\V2\Projects\Monitoring;

use App\Models\V2\User;
use App\Models\V2\Organisation;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectMonitoring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSoftDeleteProjectMonitoringControllerTest extends TestCase
{
    use RefreshDatabase;

    private $owner;

    private $projectMonitoring;

    public function setUp(): void
    {
        parent::setUp();

        $organisation = Organisation::factory()->create();

        $this->owner = User::factory()->admin()->create(['organisation_id' => $organisation->id]);

        $project = Project::factory()->create([
            'organisation_id' => $organisation->id,
        ]);
        $this->projectMonitoring = ProjectMonitoring::factory()->create([
            'project_id' => $project->id,
        ]);
    }

    public function test_invoke_action()
    {
        $this->assertDatabaseCount('v2_project_monitorings', 1);

        $user = User::factory()->create();
        $admin = User::factory()->admin()->create();

        $this->actingAs($user)
            ->delete('/api/v2/admin/project-monitorings/' . $this->projectMonitoring->uuid)
            ->assertStatus(403);

        $this->actingAs($this->owner)
            ->delete('/api/v2/admin/project-monitorings/' . $this->projectMonitoring->uuid)
            ->assertSuccessful();

        $this->projectMonitoring->refresh();

        $this->assertTrue($this->projectMonitoring->trashed());
        $this->assertDatabaseCount('v2_project_monitorings', 1);
    }
}
