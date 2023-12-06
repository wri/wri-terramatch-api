<?php

namespace Tests\V2\BaselineMonitoring;

use App\Models\Programme;
use App\Models\Site;
use App\Models\Terrafund\TerrafundProgramme;
use App\Models\Terrafund\TerrafundSite;
use App\Models\User;
use App\Models\V2\BaselineMonitoring\SiteMetric;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

final class BaselineMonitoringSiteControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function testIndexAction(): void
    {
        $user = User::factory()->admin()->create();
        SiteMetric::factory()->count(3)->create();

        $this->actingAs($user)
            ->getJson('/api/v2/site-metrics')
            ->assertStatus(200)
            ->assertJsonCount(3 , 'data');
    }

    public function testCreateAction(): void
    {
        $user = User::factory()->admin()->create();
        $site = TerrafundSite::factory()->create();

        $payload = [
            'monitorable_type' => 'terrafund_site',
            'monitorable_id' => $site->id
        ];

        $this->actingAs($user)
            ->postJson('/api/v2/site-metrics', $payload)
            ->assertStatus(201)
            ->assertJsonFragment([
                'tree_count' => null,
                'tree_cover' => null,
                'field_tree_count' => null
            ]);
    }

    public function testViewAction(): void
    {
        $user = User::factory()->admin()->create();
        $metrics = SiteMetric::factory()->create();

        $this->actingAs($user)
            ->getJson('/api/v2/site-metrics/' . $metrics->uuid)
            ->assertStatus(200)
            ->assertJsonFragment([
                'uuid' => $metrics->uuid,
                'tree_count' => $metrics->tree_count,
                'tree_cover' => $metrics->tree_cover,
                'field_tree_count' => $metrics->field_tree_count
            ]);
    }

    public function testUpdateAction(): void
    {
        $user = User::factory()->admin()->create();
        $metrics = SiteMetric::factory()->create(['tree_cover' => 64]);

        $payload = [
            'tree_cover' => 82,
            'field_tree_count' => 568,
        ];

        $this->actingAs($user)
            ->putJson('/api/v2/site-metrics/' . $metrics->uuid, $payload)
            ->assertStatus(201)
            ->assertJsonFragment([
                'tree_cover' => 82,
                'tree_count' => $metrics->tree_count,
                'field_tree_count' => 568
            ]);
    }

    public function testDeleteAction(): void
    {
        $user = User::factory()->admin()->create();
        $metrics = SiteMetric::factory()->create();
        $uuid =  $metrics->uuid;

        $this->assertEquals(1, SiteMetric::isUuid($uuid)->count());

        $this->actingAs($user)
            ->deleteJson('/api/v2/site-metrics/' . $metrics->uuid)
            ->assertStatus(202);

        $this->assertEquals(0, SiteMetric::isUuid($uuid)->count());
    }

    public function testGetSiteMetricsByProjectAction(): void
    {
        $admin = User::factory()->admin()->create();

        $tfProject = TerrafundProgramme::factory()->create();
        $tfSites = TerrafundSite::factory()->count(3)->create(['terrafund_programme_id' => $tfProject->id]);

        foreach($tfSites as $site){
            SiteMetric::factory()->create([
                'monitorable_type' => TerrafundSite::class,
                'monitorable_id' => $site->id]
            );
        }

        $this->actingAs($admin)
            ->getJson('/api/terrafund/programme/' . $tfProject->id .'/site-metrics')
            ->assertStatus(200)
            ->assertJsonCount(3, 'data');

        $ppcProject = Programme::factory()->create();
        $ppcSites = Site::factory()->count(3)->create(['programme_id' => $ppcProject->id]);

        foreach($ppcSites as $site){
            SiteMetric::factory()->create([
                    'monitorable_type' => Site::class,
                    'monitorable_id' => $site->id]
            );
        }

        $this->actingAs($admin)
            ->getJson('/api/programme/' . $ppcProject->id .'/site-metrics')
            ->assertStatus(200)
            ->assertJsonCount(3, 'data');

    }
}
