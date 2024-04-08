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

class AdminSoftDeleteSiteMonitoringControllerTest extends TestCase
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

        $this->actingAs($user)
            ->delete('/api/v2/admin/site-monitorings/' . $this->siteMonitoring->uuid)
            ->assertStatus(403);

        $this->actingAs($admin)
            ->delete('/api/v2/admin/site-monitorings/' . $this->siteMonitoring->uuid)
            ->assertStatus(403);

        $this->actingAs($this->owner)
            ->delete('/api/v2/admin/site-monitorings/' . $this->siteMonitoring->uuid)
            ->assertSuccessful();

        $this->siteMonitoring->refresh();

        $this->assertTrue($this->siteMonitoring->trashed());
        $this->assertDatabaseCount('v2_site_monitorings', 1);
    }
}
