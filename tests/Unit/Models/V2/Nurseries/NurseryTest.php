<?php

namespace Tests\Unit\Models\V2\Nurseries;

use App\Models\V2\Nurseries\NurseryReport;
use Tests\TestCase;

class NurseryTest extends TestCase
{
    /**
     * @dataProvider permissionsDataProvider
     */
    public function test_it_deletes_its_own_nursery_reports(string $permission, string $fmKey)
    {
        $nursery = \App\Models\V2\Nurseries\Nursery::factory()->{$fmKey}()->create();
        $reports = NurseryReport::factory()->count(5)->{$fmKey}()->for($nursery)->create();

        $this->assertFalse($nursery->trashed());

        foreach ($reports as $report) {
            $this->assertFalse($report->trashed());
        }

        $nursery->delete();

        $this->assertTrue($nursery->trashed());

        foreach ($reports as $report) {
            $report->refresh();
            $this->assertTrue($report->trashed());
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
