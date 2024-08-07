<?php

namespace Tests\V2\Seedings;

use App\Models\V2\Organisation;
use App\Models\V2\Projects\Project;
use App\Models\V2\Seeding;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;
use App\Models\V2\User;
use App\StateMachines\EntityStatusStateMachine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class GetSeedingsForEntityControllerTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        Artisan::call('v2migration:roles');
    }

    /**
     * @dataProvider frameworksDataProvider
     */
    public function test_it_can_list_seedings_for_a_site(string $fmKey)
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
            'status' => EntityStatusStateMachine::STARTED,
        ]);

        Seeding::factory()->count(3)->create([
            'seedable_type' => Site::class,
            'seedable_id' => $site->id,
        ]);

        $uri = '/api/v2/seedings/sites/' . $site->uuid;

        $this->actingAs($user)
            ->getJson($uri)
            ->assertStatus(403);

        $this->actingAs($owner)
            ->getJson($uri)
            ->assertSuccessful();
    }

    /**
     * @dataProvider frameworksDataProvider
     */
    public function test_it_can_list_seedings_for_a_site_report(string $fmKey)
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
            'status' => EntityStatusStateMachine::STARTED,
        ]);

        $siteReport = SiteReport::factory()->create([
            'site_id' => $site->id,
            'framework_key' => $fmKey,
            'status' => EntityStatusStateMachine::STARTED,
        ]);

        Seeding::factory()->create([
            'seedable_type' => SiteReport::class,
            'seedable_id' => $siteReport->id,
        ]);

        $uri = '/api/v2/seedings/site-reports/' . $siteReport->uuid;

        $this->actingAs($user)
            ->getJson($uri)
            ->assertStatus(403);

        $this->actingAs($owner)
            ->getJson($uri)
            ->assertSuccessful();
    }

    public static function frameworksDataProvider()
    {
        return [
            ['terrafund'],
            ['ppc'],
        ];
    }
}
