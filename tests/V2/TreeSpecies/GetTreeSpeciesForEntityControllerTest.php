<?php

namespace TreeSpecies;

use App\Models\User;
use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Nurseries\NurseryReport;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;
use App\Models\V2\TreeSpecies\TreeSpecies;
// use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetTreeSpeciesForEntityControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @dataProvider permissionsDataProvider
     */
    public function test_admin_can_fetch_all_tree_species_for_a_given_entity(string $permission, string $fmKey)
    {
        // Artisan::call('v2migration:roles');

        $user = User::factory()->admin()->create();
        $user->givePermissionTo($permission);

        $testCases = [
            'project' => Project::factory()->create(['framework_key' => $fmKey, 'status' => 'started']),
            'site' => Site::factory()->create(['framework_key' => $fmKey, 'status' => 'started']),
            'nursery' => Nursery::factory()->create(['framework_key' => $fmKey, 'status' => 'started']),
            'project-report' => ProjectReport::factory()->create(['framework_key' => $fmKey, 'status' => 'started']),
            'site-report' => SiteReport::factory()->create(['framework_key' => $fmKey, 'status' => 'started']),
            'nursery-report' => NurseryReport::factory()->create(['framework_key' => $fmKey, 'status' => 'started']),
        ];

        foreach ($testCases as $entity => $model) {
            $treeSpecies = TreeSpecies::factory()->create([
                'speciesable_type' => get_class($model),
                'speciesable_id' => $model->id,
            ]);

            $this->actingAs($user)
                ->getJson('/api/v2/tree-species/' . $entity . '/' . $model->uuid)
                ->assertJsonCount(1, 'data')
                ->assertJsonFragment([
                    'uuid' => $treeSpecies->uuid,
                ])
                ->assertStatus(200);
        }
    }

    public static function permissionsDataProvider()
    {
        return [
            ['framework-terrafund', 'terrafund'],
            ['framework-ppc', 'ppc'],
        ];
    }
}
