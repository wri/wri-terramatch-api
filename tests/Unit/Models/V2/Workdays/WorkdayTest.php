<?php

namespace Tests\Unit\Models\V2\Workdays;

use App\Models\V2\Sites\SiteReport;
use App\Models\V2\Workdays\Workday;
use Tests\TestCase;

class WorkdayTest extends TestCase
{
    public function test_sync_relation()
    {
        $siteReport = SiteReport::factory()->create();

        // First, test adding workdays to an empty set
        $data = [
            [
                'collection' => Workday::COLLECTION_SITE_VOLUNTEER_PLANTING,
                'demographics' => [
                    ['type' => 'age', 'name' => 'youth', 'amount' => 20],
                    ['type' => 'gender', 'name' => 'non-binary', 'amount' => 20],
                    ['type' => 'ethnicity', 'subtype' => 'other', 'amount' => 20],
                ],
            ],
        ];
        Workday::syncRelation($siteReport, 'workdaysVolunteerPlanting', $data, false);

        $workday = $siteReport->workdaysVolunteerPlanting()->first();
        $this->assertEquals(3, $workday->demographics()->count());
        $this->assertEquals(20, $workday->demographics()->isAge('youth')->first()->amount);
        $this->assertEquals(20, $workday->demographics()->isGender('non-binary')->first()->amount);
        $this->assertEquals(20, $workday->demographics()->isEthnicity('other')->first()->amount);

        // Test modifying an existing demographic collection
        $data[0]['demographics'] = [
            ['type' => 'age', 'name' => 'youth', 'amount' => 40],
            ['type' => 'gender', 'name' => 'non-binary', 'amount' => 20],
            ['type' => 'gender', 'name' => 'female', 'amount' => 20],
            ['type' => 'ethnicity', 'subtype' => 'indigenous', 'name' => 'Ohlone', 'amount' => 40],
        ];
        Workday::syncRelation($siteReport->fresh(), 'workdaysVolunteerPlanting', $data, false);
        $workday->refresh();
        $this->assertEquals(4, $workday->demographics()->count());
        $this->assertEquals(40, $workday->demographics()->isAge('youth')->first()->amount);
        $this->assertEquals(20, $workday->demographics()->isGender('non-binary')->first()->amount);
        $this->assertEquals(20, $workday->demographics()->isGender('female')->first()->amount);
        $this->assertEquals(40, $workday->demographics()->isEthnicity('indigenous', 'Ohlone')->first()->amount);

        // Test remove demographics
        $data[0]['demographics'] = [];
        Workday::syncRelation($siteReport->fresh(), 'workdaysVolunteerPlanting', $data, false);
        $workday->refresh();
        $this->assertEquals(0, $workday->demographics()->count());

        // Test duplicate rows in the incoming data set
        $data = [
            [
                'collection' => Workday::COLLECTION_SITE_VOLUNTEER_PLANTING,
                'demographics' => [
                    ['type' => 'age', 'name' => 'youth', 'amount' => 20],
                    ['type' => 'age', 'name' => 'youth', 'amount' => 40],
                    ['type' => 'gender', 'name' => 'non-binary', 'amount' => 20],
                    ['type' => 'gender', 'name' => 'non-binary', 'amount' => 40],
                    ['type' => 'ethnicity', 'subtype' => 'other', 'amount' => 20],
                    ['type' => 'ethnicity', 'subtype' => 'other', 'amount' => 40],
                ],
            ],
        ];
        $siteReport = SiteReport::factory()->create();
        Workday::syncRelation($siteReport, 'workdaysVolunteerPlanting', $data, false);

        $workday = $siteReport->workdaysVolunteerPlanting()->first();
        $this->assertEquals(3, $workday->demographics()->count());
        $this->assertEquals(40, $workday->demographics()->isAge('youth')->first()->amount);
        $this->assertEquals(40, $workday->demographics()->isGender('non-binary')->first()->amount);
        $this->assertEquals(40, $workday->demographics()->isEthnicity('other')->first()->amount);
    }
}
