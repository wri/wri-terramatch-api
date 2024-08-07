<?php

namespace Tests\V2\Nurseries;

use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Nurseries\NurseryReport;
use App\Models\V2\Projects\Project;
use App\Models\V2\User;
use App\StateMachines\EntityStatusStateMachine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class SoftDeleteNurseryControllerTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    /**
     * @dataProvider permissionsDataProvider
     */
    public function test_project_developer_can_soft_delete_nurseries_without_reports(string $permission, string $fmKey)
    {
        Artisan::call('v2migration:roles');

        $project = Project::factory()->create(['framework_key' => $fmKey]);
        $nursery = Nursery::factory()->{$fmKey}()->create([
            'project_id' => $project->id,
        ]);
        $owner = User::factory()->create(['organisation_id' => $project->organisation_id]);
        $owner->givePermissionTo('manage-own');

        $uri = '/api/v2/nurseries/' . $nursery->uuid;

        $this->assertFalse($nursery->trashed());

        $this->actingAs($owner)
            ->delete($uri)
            ->assertSuccessful();

        $nursery->refresh();

        $this->assertTrue($nursery->trashed());
    }

    /**
     * @dataProvider permissionsDataProvider
     */
    public function test_project_developer_cant_soft_delete_nurseries_with_reports(string $permission, string $fmKey)
    {
        Artisan::call('v2migration:roles');

        $statuses = [
            EntityStatusStateMachine::APPROVED,
        ];

        $project = Project::factory()->create(['framework_key' => $fmKey]);
        $nursery = Nursery::factory()->{$fmKey}()->create([
            'project_id' => $project->id,
            'status' => $this->faker->randomElement($statuses),
        ]);

        NurseryReport::factory()->create([
            'nursery_id' => $nursery->id,
            'framework_key' => $fmKey,
        ]);

        $owner = User::factory()->create(['organisation_id' => $project->organisation_id]);
        $owner->givePermissionTo('manage-own');

        $uri = '/api/v2/nurseries/' . $nursery->uuid;

        $this->assertFalse($nursery->trashed());

        $this->actingAs($owner)
            ->delete($uri)
            ->assertStatus(406);

        $nursery->refresh();

        $this->assertFalse($nursery->trashed());
    }

    public static function permissionsDataProvider()
    {
        return [
            ['framework-terrafund', 'terrafund'],
            ['framework-ppc', 'ppc'],
        ];
    }
}
