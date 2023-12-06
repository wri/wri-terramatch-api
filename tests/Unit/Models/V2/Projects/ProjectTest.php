<?php

namespace Models\V2\Projects;

use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectMonitoring;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\Site;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    /**
     * @dataProvider permissionsDataProvider
     */
    public function test_it_deletes_its_own_project_reports(string $permission, string $fmKey)
    {
        $project = Project::factory()->{$fmKey}()->create();
        $reports = ProjectReport::factory()->count(5)->{$fmKey}()->for($project)->create();

        $this->assertFalse($project->trashed());

        foreach ($reports as $report) {
            $this->assertFalse($report->trashed());
        }

        $project->delete();

        $this->assertTrue($project->trashed());

        foreach ($reports as $report) {
            $report->refresh();
            $this->assertTrue($report->trashed());
        }
    }

    /**
     * @dataProvider permissionsDataProvider
     */
    public function test_it_deletes_its_nested_nurseries(string $permission, string $fmKey)
    {
        $project = Project::factory()->{$fmKey}()->create();
        $nurseries = Nursery::factory()->count(5)->{$fmKey}()->for($project)->create();

        $this->assertFalse($project->trashed());

        foreach ($nurseries as $nursery) {
            $this->assertFalse($nursery->trashed());
        }

        $project->delete();

        $this->assertTrue($project->trashed());

        foreach ($nurseries as $nursery) {
            $nursery->refresh();
            $this->assertTrue($nursery->trashed());
        }
    }

    /**
     * @dataProvider permissionsDataProvider
     */
    public function test_it_deletes_its_nested_sites(string $permission, string $fmKey)
    {
        $project = Project::factory()->{$fmKey}()->create();
        $sites = Site::factory()->count(5)->{$fmKey}()->for($project)->create();

        $this->assertFalse($project->trashed());

        foreach ($sites as $site) {
            $this->assertFalse($site->trashed());
        }

        $project->delete();

        $this->assertTrue($project->trashed());

        foreach ($sites as $site) {
            $site->refresh();
            $this->assertTrue($site->trashed());
        }
    }

    /**
     * @dataProvider permissionsDataProvider
     */
    public function test_it_deletes_its_own_project_monitorings(string $permission, string $fmKey)
    {
        $project = Project::factory()->{$fmKey}()->create();
        $monitorings = ProjectMonitoring::factory()->count(5)->{$fmKey}()->for($project)->create();

        $this->assertFalse($project->trashed());

        foreach ($monitorings as $monitoring) {
            $this->assertFalse($monitoring->trashed());
        }

        $project->delete();

        $this->assertTrue($project->trashed());

        foreach ($monitorings as $monitoring) {
            $monitoring->refresh();
            $this->assertTrue($monitoring->trashed());
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
