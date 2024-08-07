<?php

namespace Tests\V2\Sites\Monitoring;

use App\Models\V2\User;
use App\Models\V2\Organisation;
use App\Models\V2\Projects\Project;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteMonitoring;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCreateSiteMonitoringControllerTest extends TestCase
{
    use RefreshDatabase;
    private $admin;

    private $site;

    public function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();

        $organisation = Organisation::factory()->create();
        $project = Project::factory()->create([
            'organisation_id' => $organisation->id,
        ]);
        $this->site = Site::factory()->create([
            'project_id' => $project->id,
        ]);
    }

    public function test_invoke_action()
    {
        $this->assertDatabaseCount('v2_site_monitorings', 0);

        $user = User::factory()->create();

        $payload = [
            'site_uuid' => $this->site->uuid,
            'tree_count' => 12.22,
            'tree_cover' => 12.22,
            'field_tree_count' => 12.22,
            'measurement_date' => now()->toDate()->format('Y-m-d'),
        ];

        $this->actingAs($user)
            ->postJson('/api/v2/admin/site-monitorings', $payload)
            ->assertStatus(403);

        $this->actingAs($this->admin)
            ->postJson('/api/v2/admin/site-monitorings', $payload)
            ->assertStatus(201)
            ->assertJsonFragment([
                'status' => SiteMonitoring::STATUS_ACTIVE,
                'tree_count' => $payload['tree_count'],
                'tree_cover' => $payload['tree_cover'],
                'field_tree_count' => $payload['field_tree_count'],
                'measurement_date' => $payload['measurement_date'],
            ]);

        $siteMonitor = $this->site->monitoring()->first();

        $this->assertDatabaseCount('v2_site_monitorings', 1);
        $this->assertDatabaseHas('v2_site_monitorings', [
            'uuid' => $siteMonitor->uuid,
            'site_id' => $this->site->id,
            'status' => $siteMonitor->status,
            'tree_count' => $siteMonitor->tree_count,
            'tree_cover' => $siteMonitor->tree_cover,
            'field_tree_count' => $siteMonitor->field_tree_count,
            'measurement_date' => $siteMonitor->measurement_date,
        ]);
    }
}
