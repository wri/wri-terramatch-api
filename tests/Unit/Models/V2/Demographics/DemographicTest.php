<?php

namespace Tests\Unit\Models\V2\Demographics;

use App\Exceptions\DemographicsException;
use App\Models\V2\Demographics\Demographic;
use App\Models\V2\Demographics\DemographicCollections;
use App\Models\V2\Demographics\DemographicEntry;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
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
                    ['type' => 'age', 'subtype' => 'youth', 'amount' => 20],
                    ['type' => 'gender', 'subtype' => 'non-binary', 'amount' => 20],
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
            ['type' => 'age', 'subtype' => 'youth', 'amount' => 40],
            ['type' => 'gender', 'subtype' => 'non-binary', 'amount' => 20],
            ['type' => 'gender', 'subtype' => 'female', 'amount' => 20],
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
                    ['type' => 'age', 'subtype' => 'youth', 'amount' => 20],
                    ['type' => 'age', 'subtype' => 'youth', 'amount' => 40],
                    ['type' => 'gender', 'subtype' => 'non-binary', 'amount' => 20],
                    ['type' => 'gender', 'subtype' => 'non-binary', 'amount' => 40],
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

    public function test_relations_and_attributes()
    {
        $report = ProjectReport::factory()->create();
        $paidNurseryOpsWorkday = Demographic::factory()->projectReportWorkdays()->create([
            'demographical_id' => $report->id,
            'collection' => DemographicCollections::PAID_NURSERY_OPERATIONS,
        ]);
        $paidTotal = DemographicEntry::factory()->create([
            'demographic_id' => $paidNurseryOpsWorkday->id,
            'type' => 'gender',
            'subtype' => 'male',
        ])->amount;
        DemographicEntry::factory()->create([
            'demographic_id' => $paidNurseryOpsWorkday->id,
            'type' => 'age',
            'subtype' => 'adult',
        ]);
        $paidOtherWorkday = Demographic::factory()->projectReportWorkdays()->create([
            'demographical_id' => $report->id,
            'collection' => DemographicCollections::PAID_OTHER,
        ]);
        $paidTotal += DemographicEntry::factory()->create([
            'demographic_id' => $paidOtherWorkday->id,
            'type' => 'gender',
            'subtype' => 'unknown',
        ])->amount;
        $directWorkday = Demographic::factory()->projectReportWorkdays()->create([
            'demographical_id' => $report->id,
            'collection' => DemographicCollections::DIRECT,
        ]);
        $paidDirectTotal = DemographicEntry::factory()->create([
            'demographic_id' => $directWorkday->id,
            'type' => 'gender',
            'subtype' => 'female',
        ])->amount;

        $directIncomeRP = Demographic::factory()->projectReportRestorationPartners()->create([
            'demographical_id' => $report->id,
            'collection' => DemographicCollections::DIRECT_INCOME,
        ]);
        $directRPTotal = DemographicEntry::factory()->create([
            'demographic_id' => $directIncomeRP->id,
            'type' => 'gender',
            'subtype' => 'non-binary',
        ])->amount;
        DemographicEntry::factory()->create([
            'demographic_id' => $directIncomeRP->id,
            'type' => 'age',
            'subtype' => 'youth',
        ]);
        $directProductivityRP = Demographic::factory()->projectReportRestorationPartners()->create([
            'demographical_id' => $report->id,
            'collection' => DemographicCollections::DIRECT_PRODUCTIVITY,
        ]);
        $directRPTotal += DemographicEntry::factory()->create([
            'demographic_id' => $directProductivityRP->id,
            'type' => 'gender',
            'subtype' => 'female',
        ])->amount;
        $indirectTrainingRP = Demographic::factory()->projectReportRestorationPartners()->create([
            'demographical_id' => $report->id,
            'collection' => DemographicCollections::INDIRECT_TRAINING,
        ]);
        $indirectRPTotal = DemographicEntry::factory()->create([
            'demographic_id' => $indirectTrainingRP->id,
            'type' => 'gender',
            'subtype' => 'unknown',
        ])->amount;

        $this->assertEquals($paidTotal, $report->workdays_paid);
        $this->assertEquals($paidDirectTotal, $report->workdays_direct_total);
        $this->assertEquals($directRPTotal, $report->direct_restoration_partners);
        $this->assertEquals($indirectRPTotal, $report->indirect_restoration_partners);

        $this->assertEquals($report->workdaysPaidNurseryOperations()->first()->id, $paidNurseryOpsWorkday->id);
        $this->assertEquals($report->workdaysPaidOtherActivities()->first()->id, $paidOtherWorkday->id);
        $this->assertEquals($report->workdaysDirect()->first()->id, $directWorkday->id);
        $this->assertNull($report->workdaysVolunteerOtherActivities()->first());
        $this->assertEquals($report->restorationPartnersDirectIncome()->first()->id, $directIncomeRP->id);
        $this->assertEquals($report->restorationPartnersDirectProductivity()->first()->id, $directProductivityRP->id);
        $this->assertEquals($report->restorationPartnersIndirectTraining()->first()->id, $indirectTrainingRP->id);
        $this->assertNull($report->restorationPartnersDirectLivelihoods()->first());

        $paidOtherWorkday->refresh();
        $this->assertNull($paidOtherWorkday->description);
        $this->assertNull($report->workdaysVolunteerOtherActivities()->first());
        $this->assertNull($report->restorationPartnersDirectOther()->first());
        $this->assertNull($report->restorationPartnersIndirectOther()->first());
        $this->assertNull($report->other_workdays_description);
        $this->assertNull($report->other_restoration_partners_description);
        $report->update([
            'other_workdays_description' => 'Workday Description',
            'other_restoration_partners_description' => 'Restoration Partner Description',
        ]);
        $this->assertEquals('Workday Description', $report->other_workdays_description);
        $paidOtherWorkday->refresh();
        $this->assertEquals('Workday Description', $paidOtherWorkday->description);
        $this->assertEquals('Workday Description', $report->workdaysVolunteerOtherActivities()->first()->description);
        $this->assertEquals('Restoration Partner Description', $report->other_restoration_partners_description);
        $this->assertEquals('Restoration Partner Description', $report->restorationPartnersDirectOther()->first()->description);
        $this->assertEquals('Restoration Partner Description', $report->restorationPartnersIndirectOther()->first()->description);
    }

    public function test_aggregate_attributes()
    {
        $project = Project::factory()->create();
        $this->assertEquals(0, $project->volunteersAggregate);

        $demographic = Demographic::factory()->projectVolunteers()->create(['demographical_id' => $project->id]);
        DemographicEntry::factory()->create([
            'demographic_id' => $demographic->id,
            'type' => 'gender',
            'subtype' => 'female',
        ]);

        $this->expectException(DemographicsException::class);
        $this->expectExceptionMessage('non-integer value.');
        $project->volunteersAggregate = 'foo';
        $demographic->delete();

        $project->volunteersAggregate = 3;
        $this->assertEquals(3, $project->volunteersAggregate);
        $this->assertEquals(1, $project->demographics()->count());
        $this->assertEquals(2, $project->demographics()->first()->entries()->count());

        $project->demographics()->first()->update(['hidden' => true]);
        $project->volunteersAggregate = 5;
        $this->assertFalse($project->demographics()->first()->hidden);
        $this->assertEquals(5, $project->volunteersAggregate);
        $this->assertEquals(5, $project->demographics()->first()->entries()->gender()->sum('amount'));
        $this->assertEquals(5, $project->demographics()->first()->entries()->age()->sum('amount'));
        $this->assertFalse($project->demographics()->first()->entries()->whereNot('subtype', 'unknown')->exists());
    }
}
