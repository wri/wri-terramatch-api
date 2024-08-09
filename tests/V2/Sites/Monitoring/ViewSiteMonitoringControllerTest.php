<?php

namespace Tests\V2\Sites\Monitoring;

use App\Models\V2\User;
use App\Models\V2\Organisation;
use App\Models\V2\Projects\Project;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteMonitoring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ViewSiteMonitoringControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoke_action()
    {
        $tfAdmin = User::factory()->terrafundAdmin()->create();
        $ppcAdmin = User::factory()->ppcAdmin()->create();

        $organisation = Organisation::factory()->create();
        $owner = User::factory()->create(['organisation_id' => $organisation->id]);

        $user = User::factory()->create();

        $project = Project::factory()->ppc()->create(['organisation_id' => $organisation->id]);

        $site = Site::factory()->ppc()->create(['project_id' => $project->id]);

        $siteMonitoring = SiteMonitoring::factory()->ppc()->create(['site_id' => $site->id]);

        $this->actingAs($user)
            ->getJson('/api/v2/site-monitorings/' . $siteMonitoring->uuid)
            ->assertStatus(403);

        $this->actingAs($tfAdmin)
            ->getJson('/api/v2/site-monitorings/' . $siteMonitoring->uuid)
            ->assertStatus(403);

        $this->actingAs($owner)
            ->getJson('/api/v2/site-monitorings/' . $siteMonitoring->uuid)
            ->assertSuccessful()
            ->assertJsonFragment([
                'uuid' => $siteMonitoring->uuid,
                'status' => $siteMonitoring->status,
                'tree_count' => $siteMonitoring->tree_count,
                'tree_cover' => $siteMonitoring->tree_cover,
                'field_tree_count' => $siteMonitoring->field_tree_count,
                'measurement_date' => $siteMonitoring->measurement_date,
            ]);

        $this->actingAs($ppcAdmin)
            ->getJson('/api/v2/site-monitorings/' . $siteMonitoring->uuid)
            ->assertSuccessful()
            ->assertJsonFragment([
                'uuid' => $siteMonitoring->uuid,
                'status' => $siteMonitoring->status,
                'tree_count' => $siteMonitoring->tree_count,
                'tree_cover' => $siteMonitoring->tree_cover,
                'field_tree_count' => $siteMonitoring->field_tree_count,
                'measurement_date' => $siteMonitoring->measurement_date,
            ]);
    }
}
