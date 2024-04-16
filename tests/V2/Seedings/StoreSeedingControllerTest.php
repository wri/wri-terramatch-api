<?php

namespace Tests\V2\Seedings;

use App\Models\User;
use App\Models\V2\Organisation;
use App\Models\V2\Projects\Project;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;
use App\StateMachines\EntityStatusStateMachine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class StoreSeedingControllerTest extends TestCase
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
    public function test_user_can_create_seedings_for_sites(string $fmKey)
    {
        $organisation = Organisation::factory()->create();
        $owner = User::factory()->create(['organisation_id' => $organisation->id]);
        $owner->givePermissionTo('manage-own');

        $user = User::factory()->create();

        $project = Project::factory()->create([
            'organisation_id' => $organisation->id,
            'framework_key' => 'ppc',
        ]);

        $site = Site::factory()->create([
            'project_id' => $project->id,
            'framework_key' => 'ppc',
            'status' => EntityStatusStateMachine::STARTED,
        ]);

        $payload = [
            'model_type' => 'site',
            'model_uuid' => $site->uuid,
            'name' => 'test name',
            'weight_of_sample' => 100,
            'seeds_in_sample' => 1000,
        ];

        $uri = '/api/v2/seedings';

        $this->actingAs($user)
            ->postJson($uri, $payload)
            ->assertStatus(403);

        $this->actingAs($owner)
            ->postJson($uri, $payload)
            ->assertStatus(201)
            ->assertJsonFragment([
                'name' => 'test name',
                'weight_of_sample' => 100,
                'seeds_in_sample' => 1000,
            ]);
    }

    /**
     * @dataProvider frameworksDataProvider
     */
    public function test_user_can_create_seedings_for_site_reports(string $fmKey)
    {
        $organisation = Organisation::factory()->create();
        $owner = User::factory()->create(['organisation_id' => $organisation->id]);
        $owner->givePermissionTo('manage-own');

        $user = User::factory()->create();

        $project = Project::factory()->create([
            'organisation_id' => $organisation->id,
            'framework_key' => 'ppc',
        ]);

        $site = Site::factory()->create([
            'project_id' => $project->id,
            'framework_key' => 'ppc',
            'status' => EntityStatusStateMachine::STARTED,
        ]);

        $siteReport = SiteReport::factory()->create([
            'site_id' => $site->id,
            'framework_key' => $fmKey,
            'status' => EntityStatusStateMachine::STARTED,
        ]);

        $payload = [
            'model_type' => 'site-report',
            'model_uuid' => $siteReport->uuid,
            'name' => 'test name',
            'weight_of_sample' => 100,
            'seeds_in_sample' => 1000,
        ];

        $uri = '/api/v2/seedings';

        $this->actingAs($user)
            ->postJson($uri, $payload)
            ->assertStatus(403);

        $this->actingAs($owner)
            ->postJson($uri, $payload)
            ->assertStatus(201)
            ->assertJsonFragment([
                'name' => 'test name',
                'weight_of_sample' => 100,
                'seeds_in_sample' => 1000,
            ]);
    }

    public static function frameworksDataProvider()
    {
        return [
            ['terrafund'],
            ['ppc'],
        ];
    }
}
