<?php

namespace Tests\Unit\Models\V2\Sites;

use App\Models\V2\Demographics\Demographic;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteMonitoring;
use App\Models\V2\Sites\SiteReport;
use App\Models\V2\Workdays\Workday;
use App\StateMachines\EntityStatusStateMachine;
use Tests\TestCase;

class SiteTest extends TestCase
{
    /**
     * @dataProvider permissionsDataProvider
     */
    public function test_it_deletes_its_own_site_reports(string $permission, string $fmKey)
    {
        $site = Site::factory()->{$fmKey}()->create();
        $reports = SiteReport::factory()->count(5)->{$fmKey}()->for($site)->create();

        $this->assertFalse($site->trashed());

        foreach ($reports as $report) {
            $this->assertFalse($report->trashed());
        }

        $site->delete();

        $this->assertTrue($site->trashed());

        foreach ($reports as $report) {
            $report->refresh();
            $this->assertTrue($report->trashed());
        }
    }

    /**
     * @dataProvider permissionsDataProvider
     */
    public function test_it_deletes_its_own_site_monitorings(string $permission, string $fmKey)
    {
        $site = Site::factory()->{$fmKey}()->create();
        $monitorings = SiteMonitoring::factory()->count(5)->{$fmKey}()->for($site)->create();

        $this->assertFalse($site->trashed());

        foreach ($monitorings as $monitoring) {
            $this->assertFalse($monitoring->trashed());
        }

        $site->delete();

        $this->assertTrue($site->trashed());

        foreach ($monitorings as $monitoring) {
            $monitoring->refresh();
            $this->assertTrue($monitoring->trashed());
        }
    }

    public function test_workday_count()
    {
        $site = Site::factory()->ppc()->create();

        $report = SiteReport::factory()->ppc()->create(['site_id' => $site->id, 'status' => EntityStatusStateMachine::APPROVED]);
        $workday = Workday::factory()->create(['workdayable_id' => $report->id]);
        Demographic::factory()->create(['demographical_id' => $workday->id, 'amount' => 3]);

        $report = SiteReport::factory()->ppc()->create(['site_id' => $site->id, 'status' => EntityStatusStateMachine::AWAITING_APPROVAL]);
        $workday = Workday::factory()->create(['workdayable_id' => $report->id]);
        Demographic::factory()->create(['demographical_id' => $workday->id, 'amount' => 5]);

        // Unsubmitted report (doesn't count toward workday count)
        $report = SiteReport::factory()->ppc()->create(['site_id' => $site->id, 'status' => EntityStatusStateMachine::STARTED]);
        $workday = Workday::factory()->create(['workdayable_id' => $report->id]);
        Demographic::factory()->create(['demographical_id' => $workday->id, 'amount' => 7]);

        $this->assertEquals(8, $site->workday_count);
    }

    public static function permissionsDataProvider()
    {
        return [
            ['framework-terrafund', 'terrafund'],
            ['framework-ppc', 'ppc'],
        ];
    }
}
