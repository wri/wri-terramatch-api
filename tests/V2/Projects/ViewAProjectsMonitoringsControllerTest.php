<?php

namespace Tests\V2\Projects;

use App\Models\V2\User;
use App\Models\V2\Organisation;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectMonitoring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ViewAProjectsMonitoringsControllerTest extends TestCase
{
    use RefreshDatabase;
    private $owner;

    private $projectMonitoring;

    private $project;

    public function setUp(): void
    {
        parent::setUp();

        $organisation = Organisation::factory()->create();

        Artisan::call('v2migration:roles');
        $this->owner = User::factory()->admin()->create(['organisation_id' => $organisation->id]);
        $this->owner->givePermissionTo('manage-own');

        $this->project = Project::factory()->ppc()->create([
            'organisation_id' => $organisation->id,
        ]);
        $this->projectMonitoring = ProjectMonitoring::factory()
            ->ppc()
            ->count(16)
            ->create(['project_id' => $this->project->id,]);
    }

    public function test_invoke_action()
    {
        $user = User::factory()->create();

        $tfAdmin = User::factory()->admin()->create();
        $tfAdmin->givePermissionTo('framework-terrafund');

        $this->actingAs($user)
            ->getJson('/api/v2/projects/' . $this->project->uuid . '/monitorings')
            ->assertStatus(403);

        $this->actingAs($tfAdmin)
            ->getJson('/api/v2/projects/' . $this->project->uuid . '/monitorings')
            ->assertStatus(403);

        $this->actingAs($this->owner)
            ->getJson('/api/v2/projects/' . $this->project->uuid . '/monitorings')
            ->assertSuccessful()
            ->assertJsonCount(15, 'data')
            ->assertJsonFragment([
                'uuid' => $this->projectMonitoring[0]->uuid,
                'status' => $this->projectMonitoring[0]->status,
                'tree_count' => $this->projectMonitoring[0]->tree_count,
                'tree_cover' => $this->projectMonitoring[0]->tree_cover,
                'field_tree_count' => $this->projectMonitoring[0]->field_tree_count,
                'field_tree_regenerated' => $this->projectMonitoring[0]->field_tree_regenerated,
            ]);

        $this->actingAs($this->owner)
            ->getJson('/api/v2/projects/' . $this->project->uuid . '/monitorings?page=2')
            ->assertSuccessful()
            ->assertJsonCount(1, 'data');
    }
}
