<?php

namespace Exports;

use App\Helpers\CustomFormHelper;
use App\Jobs\V2\GenerateAdminAllEntityRecordsExportJob;
use App\Models\User;
use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Nurseries\NurseryReport;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ExportAllMonitoredEntitiesControllerTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    /**
     * @dataProvider permissionsDataProvider
     */
    public function test_an_admin_user_can_export_all_monitored_entities(string $permission, string $fmKey)
    {
        //        Artisan::call('v2migration:roles');

        $user = User::factory()->admin()->create();
        $user->givePermissionTo($permission);

        $testCases = [
            'projects' => Project::factory()->count(5)->create(['framework_key' => $fmKey]),
            'sites' => Site::factory()->count(5)->create(['framework_key' => $fmKey]),
            'nurseries' => Nursery::factory()->count(5)->create(['framework_key' => $fmKey]),
            'project-reports' => ProjectReport::factory()->count(5)->create(['framework_key' => $fmKey]),
            'site-reports' => SiteReport::factory()->count(5)->create(['framework_key' => $fmKey]),
            'nursery-reports' => NurseryReport::factory()->count(5)->create(['framework_key' => $fmKey]),
        ];

        foreach ($testCases as $entity => $models) {
            CustomFormHelper::generateFakeForm($models->first()->shortName, $fmKey);

            GenerateAdminAllEntityRecordsExportJob::dispatchSync($entity, $fmKey);

            $uri = '/api/v2/admin/' . $entity . '/export/' . $fmKey;

            $this->actingAs($user)
                ->get($uri)
                ->assertSuccessful();
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
