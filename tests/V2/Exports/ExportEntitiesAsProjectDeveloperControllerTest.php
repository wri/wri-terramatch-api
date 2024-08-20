<?php

namespace Tests\V2\Exports;

use App\Helpers\CustomFormHelper;
use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Nurseries\NurseryReport;
use App\Models\V2\Organisation;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;
use App\Models\V2\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ExportEntitiesAsProjectDeveloperControllerTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    /**
     * @dataProvider permissionsDataProvider
     */
    public function test_an_user_can_export_all_sites_data_for_a_given_project(string $permission, string $fmKey)
    {
        $organisation = Organisation::factory()->create();
        $owner = User::factory()->create(['organisation_id' => $organisation->id]);

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
    public function test_an_user_can_export_all_nurseries_data_for_a_given_project(string $permission, string $fmKey)
    {
        $organisation = Organisation::factory()->create();
        $owner = User::factory()->create(['organisation_id' => $organisation->id]);

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

    /**
     * @dataProvider permissionsDataProvider
     */
    public function test_an_user_can_export_nursery_reports_data(string $permission, string $fmKey)
    {
        $organisation = Organisation::factory()->create();
        $owner = User::factory()->create(['organisation_id' => $organisation->id]);

        $project = Project::factory()->create([
            'framework_key' => $fmKey,
            'organisation_id' => $organisation->id,
        ]);

        $nursery = Nursery::factory()->create([
            'framework_key' => $fmKey,
            'project_id' => $project->id,
        ]);

        $report = NurseryReport::factory()->create([
            'framework_key' => $fmKey,
            'nursery_id' => $nursery->id,
        ]);

        CustomFormHelper::generateFakeForm('nursery-report', $fmKey);

        $uri = '/api/v2/nursery-reports/' . $report->uuid . '/export';

        $this->actingAs($owner)
            ->get($uri)
            ->assertSuccessful();
    }

    /**
     * @dataProvider permissionsDataProvider
     */
    public function test_an_user_can_export_site_reports_data(string $permission, string $fmKey)
    {
        $organisation = Organisation::factory()->create();
        $owner = User::factory()->create(['organisation_id' => $organisation->id]);

        $project = Project::factory()->create([
            'framework_key' => $fmKey,
            'organisation_id' => $organisation->id,
        ]);

        $site = Site::factory()->create([
            'framework_key' => $fmKey,
            'project_id' => $project->id,
        ]);

        $report = SiteReport::factory()->create([
            'framework_key' => $fmKey,
            'site_id' => $site->id,
        ]);

        CustomFormHelper::generateFakeForm('site-report', $fmKey);

        $uri = '/api/v2/site-reports/' . $report->uuid . '/export';

        $this->actingAs($owner)
            ->get($uri)
            ->assertSuccessful();
    }

    /**
     * @dataProvider permissionsDataProvider
     */
    public function test_an_user_can_export_project_reports_data(string $permission, string $fmKey)
    {
        $organisation = Organisation::factory()->create();
        $owner = User::factory()->create(['organisation_id' => $organisation->id]);

        $project = Project::factory()->create([
            'framework_key' => $fmKey,
            'organisation_id' => $organisation->id,
        ]);

        $report = ProjectReport::factory()->create([
            'framework_key' => $fmKey,
            'project_id' => $project->id,
        ]);

        CustomFormHelper::generateFakeForm('project-report', $fmKey);

        $uri = '/api/v2/project-reports/' . $report->uuid . '/export';

        $this->actingAs($owner)
            ->get($uri)
            ->assertSuccessful();
    }

    /**
     * @dataProvider permissionsDataProvider
     */
    public function test_an_user_can_export_all_project_data(string $permission, string $fmKey)
    {
        Carbon::setTestNow(now());

        $organisation = Organisation::factory()->create();
        $owner = User::factory()->create(['organisation_id' => $organisation->id]);

        $project = Project::factory()->create([
            'framework_key' => $fmKey,
            'organisation_id' => $organisation->id,
        ]);

        ProjectReport::factory()->count(5)->create([
            'framework_key' => $fmKey,
            'project_id' => $project->id,
        ]);

        $nurseries = Nursery::factory()->count(5)->create([
            'framework_key' => $fmKey,
            'project_id' => $project->id,
        ]);

        foreach ($nurseries as $nursery) {
            NurseryReport::factory()->count(5)->create([
                'framework_key' => $fmKey,
                'nursery_id' => $nursery->id,
            ]);
        }

        $sites = Site::factory()->count(5)->create([
            'framework_key' => $fmKey,
            'project_id' => $project->id,
        ]);

        foreach ($sites as $site) {
            SiteReport::factory()->count(5)->create([
                'framework_key' => $fmKey,
                'site_id' => $site->id,
            ]);
        }

        CustomFormHelper::generateFakeForm('project', $fmKey);
        CustomFormHelper::generateFakeForm('project-report', $fmKey);
        CustomFormHelper::generateFakeForm('nursery', $fmKey);
        CustomFormHelper::generateFakeForm('nursery-report', $fmKey);
        CustomFormHelper::generateFakeForm('site', $fmKey);
        CustomFormHelper::generateFakeForm('site-report', $fmKey);

        $uri = '/api/v2/projects/' . $project->uuid . '/export';

        $this->actingAs($owner)
            ->get($uri)
            ->assertDownload($project->name . ' full export - ' . now() . '.zip')
            ->assertSuccessful();
    }

    /**
     * @dataProvider permissionsDataProvider
     */
    public function test_an_user_can_export_all_site_data(string $permission, string $fmKey)
    {
        Carbon::setTestNow(now());

        $organisation = Organisation::factory()->create();
        $owner = User::factory()->create(['organisation_id' => $organisation->id]);

        $project = Project::factory()->create([
            'framework_key' => $fmKey,
            'organisation_id' => $organisation->id,
        ]);

        $site = Site::factory()->create([
            'framework_key' => $fmKey,
            'project_id' => $project->id,
        ]);


        SiteReport::factory()->count(5)->create([
            'framework_key' => $fmKey,
            'site_id' => $site->id,
        ]);

        CustomFormHelper::generateFakeForm('site', $fmKey);
        CustomFormHelper::generateFakeForm('site-report', $fmKey);

        $uri = '/api/v2/sites/' . $site->uuid . '/export';

        $this->actingAs($owner)
            ->get($uri)
            ->assertDownload($site->name . ' export - ' . now() . '.zip')
            ->assertSuccessful();
    }

    /**
     * @dataProvider permissionsDataProvider
     */
    public function test_an_user_can_export_all_nursey_data(string $permission, string $fmKey)
    {
        Carbon::setTestNow(now());

        $organisation = Organisation::factory()->create();
        $owner = User::factory()->create(['organisation_id' => $organisation->id]);

        $project = Project::factory()->create([
            'framework_key' => $fmKey,
            'organisation_id' => $organisation->id,
        ]);

        $nursery = Nursery::factory()->create([
            'framework_key' => $fmKey,
            'project_id' => $project->id,
        ]);


        NurseryReport::factory()->count(5)->create([
            'framework_key' => $fmKey,
            'nursery_id' => $nursery->id,
        ]);

        CustomFormHelper::generateFakeForm('nursery', $fmKey);
        CustomFormHelper::generateFakeForm('nursery-report', $fmKey);

        $uri = '/api/v2/nurseries/' . $nursery->uuid . '/export';

        $this->actingAs($owner)
            ->get($uri)
            ->assertDownload($nursery->name . ' export - ' . now() . '.zip')
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
