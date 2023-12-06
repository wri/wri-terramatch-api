<?php

namespace Tests\V2\Sites;

use App\Models\User;
use App\Models\V2\Organisation;
use App\Models\V2\Projects\Project;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteMonitoring;
//use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ViewASitesMonitoringsControllerTest extends TestCase
{
    use RefreshDatabase;

    private $owner;

    private $siteMonitoring;

    private $site;

    public function setUp(): void
    {
        parent::setUp();

        $organisation = Organisation::factory()->create();

//        Artisan::call('v2migration:roles --fresh');
        $this->owner = User::factory()->admin()->create(['organisation_id' => $organisation->id]);
        $this->owner->givePermissionTo('manage-own');

        $project = Project::factory()->ppc()->create([
            'organisation_id' => $organisation->id,
        ]);
        $this->site = Site::factory()->ppc()->create([
            'project_id' => $project->id,
        ]);
        $this->siteMonitoring = SiteMonitoring::factory()
            ->ppc()
            ->count(16)
            ->create(['site_id' => $this->site->id,]);
    }

    public function test_invoke_action()
    {
        $user = User::factory()->create();

        $tfAdmin = User::factory()->admin()->create();
        $tfAdmin->givePermissionTo('framework-terrafund');

        $this->actingAs($user)
            ->getJson('/api/v2/sites/' . $this->site->uuid . '/monitorings')
            ->assertStatus(403);

        $this->actingAs($tfAdmin)
            ->getJson('/api/v2/sites/' . $this->site->uuid . '/monitorings')
            ->assertStatus(403);

        $this->actingAs($this->owner)
            ->getJson('/api/v2/sites/' . $this->site->uuid . '/monitorings')
            ->assertSuccessful()
            ->assertJsonCount(15, 'data')
            ->assertJsonFragment([
                'uuid' => $this->siteMonitoring[0]->uuid,
                'status' => $this->siteMonitoring[0]->status,
                'tree_count' => $this->siteMonitoring[0]->tree_count,
                'tree_cover' => $this->siteMonitoring[0]->tree_cover,
                'field_tree_count' => $this->siteMonitoring[0]->field_tree_count,
                'measurement_date' => $this->siteMonitoring[0]->measurement_date,
            ]);

        $this->actingAs($this->owner)
            ->getJson('/api/v2/sites/' . $this->site->uuid . '/monitorings?page=2')
            ->assertSuccessful()
            ->assertJsonCount(1, 'data');
    }
}
