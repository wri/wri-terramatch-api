<?php

namespace Exports;

use App\Helpers\CustomFormHelper;
use App\Models\User;
use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Organisation;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\Site;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ExportProjectEntityAsProjectDeveloperControllerTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        Artisan::call('v2migration:roles');
    }

    /**
     * @dataProvider permissionsDataProvider
     */
    public function test_an_user_can_export_all_sites_establishment_data_for_a_given_project(string $permission, string $fmKey)
    {
        //        Artisan::call('v2migration:roles');

        $organisation = Organisation::factory()->create();
        $owner = User::factory()->create(['organisation_id' => $organisation->id]);
        $owner->givePermissionTo('manage-own');

        $project = Project::factory()->create([
            'framework_key' => $fmKey,
            'organisation_id' => $organisation->id,
        ]);

        Site::factory()->count(5)->create([
            'framework_key' => $fmKey,
            'project_id' => $project->id,
        ]);


        CustomFormHelper::generateFakeForm('site', $fmKey);

        $uri = '/api/v2/projects/' . $project->uuid . '/sites/export';

        $this->actingAs($owner)
            ->get($uri)
            ->assertSuccessful();
    }

    /**
     * @dataProvider permissionsDataProvider
     */
    public function test_an_user_can_export_all_project_reports_establishment_data_for_a_given_project(string $permission, string $fmKey)
    {
        $organisation = Organisation::factory()->create();
        $owner = User::factory()->create(['organisation_id' => $organisation->id]);
        $owner->givePermissionTo('manage-own');

        $project = Project::factory()->create([
            'framework_key' => $fmKey,
            'organisation_id' => $organisation->id,
        ]);

        ProjectReport::factory()->count(5)->create([
            'framework_key' => $fmKey,
            'project_id' => $project->id,
        ]);


        CustomFormHelper::generateFakeForm('project-report', $fmKey);

        $uri = '/api/v2/projects/' . $project->uuid . '/project-reports/export';

        $this->actingAs($owner)
            ->get($uri)
            ->assertSuccessful();
    }

    /**
     * @dataProvider permissionsDataProvider
     */
    public function test_an_user_can_export_all_nurseries_establishment_data_for_a_given_project(string $permission, string $fmKey)
    {
        $organisation = Organisation::factory()->create();
        $owner = User::factory()->create(['organisation_id' => $organisation->id]);
        $owner->givePermissionTo('manage-own');

        $project = Project::factory()->create([
            'framework_key' => $fmKey,
            'organisation_id' => $organisation->id,
        ]);

        Nursery::factory()->count(5)->create([
            'framework_key' => $fmKey,
            'project_id' => $project->id,
        ]);


        CustomFormHelper::generateFakeForm('nursery', $fmKey);
        $uri = '/api/v2/projects/' . $project->uuid . '/nurseries/export';

        $this->actingAs($owner)
            ->get($uri)
            ->assertSuccessful();
    }

    public static function permissionsDataProvider()
    {
        return [
            ['framework-terrafund', 'terrafund'],
            ['framework-ppc', 'ppc'],
        ];
    }
}
