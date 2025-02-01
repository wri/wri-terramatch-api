<?php

namespace Tests\Unit\Models\V2\Demographics;

use App\Models\V2\Demographics\Demographic;
use App\Models\V2\Demographics\DemographicCollections;
use App\Models\V2\Sites\SiteReport;
use Tests\TestCase;

class DemographicTest extends TestCase
{
    public function test_sync_relation()
    {
        $siteReport = SiteReport::factory()->create();

        // First, test adding workdays to an empty set
        $data = [
            [
                'type' => Demographic::WORKDAY_TYPE,
                'collection' => DemographicCollections::VOLUNTEER_PLANTING,
                'demographics' => [
                    ['type' => 'age', 'name' => 'youth', 'amount' => 20],
                    ['type' => 'gender', 'name' => 'non-binary', 'amount' => 20],
                    ['type' => 'ethnicity', 'subtype' => 'other', 'amount' => 20],
                ],
            ],
        ];
        Demographic::syncRelation($siteReport, 'workdaysVolunteerPlanting', 'workdays', $data, false);

        /** @var Demographic $workday */
        $workday = $siteReport->workdaysVolunteerPlanting()->first();
        $this->assertEquals(3, $workday->entries()->count());
        $this->assertEquals(20, $workday->entries()->isAge('youth')->first()->amount);
        $this->assertEquals(20, $workday->entries()->isGender('non-binary')->first()->amount);
        $this->assertEquals(20, $workday->entries()->isEthnicity('other')->first()->amount);

        // Test modifying an existing demographic collection
        $data[0]['demographics'] = [
            ['type' => 'age', 'name' => 'youth', 'amount' => 40],
            ['type' => 'gender', 'name' => 'non-binary', 'amount' => 20],
            ['type' => 'gender', 'name' => 'female', 'amount' => 20],
            ['type' => 'ethnicity', 'subtype' => 'indigenous', 'name' => 'Ohlone', 'amount' => 40],
        ];
        Demographic::syncRelation($siteReport->fresh(), 'workdaysVolunteerPlanting', 'workdays', $data, false);
        $workday->refresh();
        $this->assertEquals(4, $workday->entries()->count());
        $this->assertEquals(40, $workday->entries()->isAge('youth')->first()->amount);
        $this->assertEquals(20, $workday->entries()->isGender('non-binary')->first()->amount);
        $this->assertEquals(20, $workday->entries()->isGender('female')->first()->amount);
        $this->assertEquals(40, $workday->entries()->isEthnicity('indigenous', 'Ohlone')->first()->amount);

        // Test remove demographics
        $data[0]['demographics'] = [];
        Demographic::syncRelation($siteReport->fresh(), 'workdaysVolunteerPlanting', 'workdays', $data, false);
        $workday->refresh();
        $this->assertEquals(0, $workday->entries()->count());

        // Test duplicate rows in the incoming data set
        $data = [
            [
                'type' => Demographic::WORKDAY_TYPE,
                'collection' => DemographicCollections::VOLUNTEER_PLANTING,
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
        Demographic::syncRelation($siteReport, 'workdaysVolunteerPlanting', 'workdays', $data, false);
        Demographic::syncRelation($siteReport, 'workdaysVolunteerPlanting', 'workdays', $data, false);

        /** @var Demographic $workday */
        $workday = $siteReport->workdaysVolunteerPlanting()->first();
        $this->assertEquals(3, $workday->entries()->count());
        $this->assertEquals(3, $workday->entries()->withTrashed()->count());
        $this->assertEquals(40, $workday->entries()->isAge('youth')->first()->amount);
        $this->assertEquals(40, $workday->entries()->isGender('non-binary')->first()->amount);
        $this->assertEquals(40, $workday->entries()->isEthnicity('other')->first()->amount);
    }
}
