<?php

namespace Tests\Unit\Models\V2\Sites;

use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteMonitoring;
use App\Models\V2\Sites\SiteReport;
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

    public static function permissionsDataProvider()
    {
        return [
            ['framework-terrafund', 'terrafund'],
            ['framework-ppc', 'ppc'],
        ];
    }
}
