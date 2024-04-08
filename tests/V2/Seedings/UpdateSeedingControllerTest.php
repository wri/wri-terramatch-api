<?php

namespace Tests\V2\Seedings;

use App\Models\User;
use App\Models\V2\Organisation;
use App\Models\V2\Projects\Project;
use App\Models\V2\Seeding;
use App\Models\V2\Sites\Site;
use App\StateMachines\EntityStatusStateMachine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class UpdateSeedingControllerTest extends TestCase
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
    public function test_user_can_update_a_seeding(string $fmKey)
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

        $seeding = Seeding::factory()->create([
            'seedable_type' => Site::class,
            'seedable_id' => $site->id,
        ]);

        $payload = [
            'name' => 'updated test name',
            'weight_of_sample' => 100,
            'seeds_in_sample' => 1000,
        ];

        $uri = '/api/v2/seedings/' . $seeding->uuid;

        $this->actingAs($user)
            ->patchJson($uri, $payload)
            ->assertStatus(403);

        $this->actingAs($owner)
            ->patchJson($uri, $payload)
            ->assertSuccessful()
            ->assertJsonFragment([
                'name' => 'updated test name',
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
