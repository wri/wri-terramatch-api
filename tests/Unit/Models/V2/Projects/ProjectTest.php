<?php

namespace Tests\Unit\Models\V2\Projects;

use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectMonitoring;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;
use App\Models\V2\Workdays\Workday;
use App\Models\V2\Workdays\WorkdayDemographic;
use App\StateMachines\EntityStatusStateMachine;
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

    public function test_workday_count()
    {
        $project = Project::factory()->ppc()->create();

        // The amounts are all prime so that it's easy to tell what got counted and what didn't when there's an error

        // Unapproved site (doesn't count toward workday count)
        $site = Site::factory()->ppc()->create(['project_id' => $project->id, 'status' => EntityStatusStateMachine::AWAITING_APPROVAL]);
        $report = SiteReport::factory()->ppc()->create(['site_id' => $site->id, 'status' => EntityStatusStateMachine::APPROVED]);
        $workday = Workday::factory()->create(['workdayable_id' => $report->id]);
        WorkdayDemographic::factory()->create(['workday_id' => $workday->id, 'amount' => 3]);

        // Approved site
        $site = Site::factory()->ppc()->create(['project_id' => $project->id, 'status' => EntityStatusStateMachine::APPROVED]);
        $report = SiteReport::factory()->ppc()->create(['site_id' => $site->id, 'status' => EntityStatusStateMachine::APPROVED]);
        $workday = Workday::factory()->create(['workdayable_id' => $report->id]);
        WorkdayDemographic::factory()->create(['workday_id' => $workday->id, 'amount' => 5]);
        $report = SiteReport::factory()->ppc()->create(['site_id' => $site->id, 'status' => EntityStatusStateMachine::AWAITING_APPROVAL]);
        $workday = Workday::factory()->create(['workdayable_id' => $report->id]);
        WorkdayDemographic::factory()->create(['workday_id' => $workday->id, 'amount' => 7]);
        // Unsubmitted report (doesn't count toward workday count)
        $report = SiteReport::factory()->ppc()->create(['site_id' => $site->id, 'status' => EntityStatusStateMachine::STARTED]);
        $workday = Workday::factory()->create(['workdayable_id' => $report->id]);
        WorkdayDemographic::factory()->create(['workday_id' => $workday->id, 'amount' => 11]);

        $report = ProjectReport::factory()->ppc()->create(['project_id' => $project->id, 'status' => EntityStatusStateMachine::APPROVED]);
        $workday = Workday::factory()->projectReport()->create(['workdayable_id' => $report->id]);
        WorkdayDemographic::factory()->create(['workday_id' => $workday->id, 'amount' => 13]);
        $report = ProjectReport::factory()->ppc()->create(['project_id' => $project->id, 'status' => EntityStatusStateMachine::AWAITING_APPROVAL]);
        $workday = Workday::factory()->projectReport()->create(['workdayable_id' => $report->id]);
        WorkdayDemographic::factory()->create(['workday_id' => $workday->id, 'amount' => 17]);
        // Unsubmitted report (doesn't count toward workday count)
        $report = ProjectReport::factory()->ppc()->create(['project_id' => $project->id, 'status' => EntityStatusStateMachine::STARTED]);
        $workday = Workday::factory()->projectReport()->create(['workdayable_id' => $report->id]);
        WorkdayDemographic::factory()->create(['workday_id' => $workday->id, 'amount' => 19]);

        // 42 = 5 and 7 from the approved site's reports and 13 and 17 from the project reports
        $this->assertEquals(42, $project->workday_count);
    }

    public static function permissionsDataProvider()
    {
        return [
            ['framework-terrafund', 'terrafund'],
            ['framework-ppc', 'ppc'],
        ];
    }
}
