<?php

namespace Tests\V2\Sites\Monitoring;

use App\Models\User;
use App\Models\V2\Organisation;
use App\Models\V2\Projects\Project;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteMonitoring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUpdateSiteMonitoringControllerTest extends TestCase
{
    use RefreshDatabase;

    private $owner;

    private $siteMonitoring;

    private $site;

    public function setUp(): void
    {
        parent::setUp();

        $organisation = Organisation::factory()->create();

        Artisan::call('v2migration:roles');
        $this->owner = User::factory()->admin()->create(['organisation_id' => $organisation->id]);
        $this->owner->givePermissionTo('manage-own');

        $project = Project::factory()->create([
            'organisation_id' => $organisation->id,
        ]);
        $this->site = Site::factory()->create([
            'project_id' => $project->id,
        ]);
        $this->siteMonitoring = SiteMonitoring::factory()->create([
            'site_id' => $this->site->id,
        ]);
    }

    public function test_invoke_action()
    {
        $this->assertDatabaseCount('v2_site_monitorings', 1);

        $user = User::factory()->create();
        $admin = User::factory()->admin()->create();

        $payload = [
            'status' => 'deactivated',
            'tree_count' => 12.22,
            'tree_cover' => 12.22,
            'field_tree_count' => 12.22,
            'measurement_date' => now()->toDate()->format('Y-m-d'),
        ];

        $this->actingAs($user)
            ->putJson('/api/v2/admin/site-monitorings/' . $this->siteMonitoring->uuid, $payload)
            ->assertStatus(403);

        $this->actingAs($admin)
            ->putJson('/api/v2/admin/site-monitorings/' . $this->siteMonitoring->uuid, $payload)
            ->assertStatus(403);

        $this->actingAs($this->owner)
            ->putJson('/api/v2/admin/site-monitorings/' . $this->siteMonitoring->uuid, $payload)
            ->assertStatus(200)
            ->assertJsonFragment([
                'status' => $payload['status'],
                'tree_count' => $payload['tree_count'],
                'tree_cover' => $payload['tree_cover'],
                'field_tree_count' => $payload['field_tree_count'],
                'measurement_date' => $payload['measurement_date'],
            ]);

        $this->assertDatabaseCount('v2_site_monitorings', 1);
        $this->assertDatabaseHas('v2_site_monitorings', [
            'uuid' => $this->siteMonitoring->uuid,
            'site_id' => $this->site->id,
            'status' => $payload['status'],
            'tree_count' => $payload['tree_count'],
            'tree_cover' => $payload['tree_cover'],
            'field_tree_count' => $payload['field_tree_count'],
            'measurement_date' => $payload['measurement_date'],
        ]);
    }
}
