<?php

namespace Tests\V2\Seedings;

use App\Models\User;
use App\Models\V2\Organisation;
use App\Models\V2\Projects\Project;
use App\Models\V2\Seeding;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteSeedingControllerTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        //        Artisan::call('v2migration:roles --fresh');
    }

    /**
     * @dataProvider frameworksDataProvider
     */
    public function test_it_can_delete_a_seeding_for_a_site(string $fmKey)
    {
        $organisation = Organisation::factory()->create();

        $owner = User::factory()->create(['organisation_id' => $organisation->id]);
        $owner->givePermissionTo('manage-own');

        $user = User::factory()->create();

        $project = Project::factory()->create([
            'organisation_id' => $organisation->id,
            'framework_key' => $fmKey,
        ]);

        $site = Site::factory()->create([
            'project_id' => $project->id,
            'framework_key' => $fmKey,
            'status' => Site::STATUS_STARTED,
        ]);

        $seeding = Seeding::factory()->create([
            'seedable_type' => Site::class,
            'seedable_id' => $site->id,
        ]);

        $uri = '/api/v2/seedings/' . $seeding->uuid;

        $this->actingAs($user)
            ->deleteJson($uri)
            ->assertStatus(403);

        $this->actingAs($owner)
            ->deleteJson($uri)
            ->assertSuccessful();

        $this->assertSoftDeleted('v2_seedings', ['uuid' => $seeding->uuid]);
    }

    /**
     * @dataProvider frameworksDataProvider
     */
    public function test_it_can_delete_a_seeding_for_a_site_report(string $fmKey)
    {
        $organisation = Organisation::factory()->create();

        $owner = User::factory()->create(['organisation_id' => $organisation->id]);
        $owner->givePermissionTo('manage-own');

        $user = User::factory()->create();

        $project = Project::factory()->create([
            'organisation_id' => $organisation->id,
            'framework_key' => $fmKey,
        ]);

        $site = Site::factory()->create([
            'project_id' => $project->id,
            'framework_key' => $fmKey,
            'status' => Site::STATUS_STARTED,
        ]);

        $siteReport = SiteReport::factory()->create([
            'site_id' => $site->id,
            'framework_key' => $fmKey,
            'status' => SiteReport::STATUS_STARTED,
        ]);

        $seeding = Seeding::factory()->create([
            'seedable_type' => SiteReport::class,
            'seedable_id' => $siteReport->id,
        ]);

        $uri = '/api/v2/seedings/' . $seeding->uuid;

        $this->actingAs($user)
            ->deleteJson($uri)
            ->assertStatus(403);

        $this->actingAs($owner)
            ->deleteJson($uri)
            ->assertSuccessful();

        $this->assertSoftDeleted('v2_seedings', ['uuid' => $seeding->uuid]);
    }

    public static function frameworksDataProvider()
    {
        return [
            ['terrafund'],
            ['ppc'],
        ];
    }
}
