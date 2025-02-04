<?php

namespace Tests\V2\Workdays;

use App\Models\V2\Demographics\Demographic;
use App\Models\V2\Demographics\DemographicCollections;
use App\Models\V2\Demographics\DemographicEntry;
use App\Models\V2\Organisation;
use App\Models\V2\Projects\Project;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;
use App\Models\V2\User;
use App\StateMachines\EntityStatusStateMachine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class GetWorkdaysForEntityControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_empty_workdays_response()
    {
        $organisation = Organisation::factory()->create();
        $owner = User::factory()->create(['organisation_id' => $organisation->id]);

        $user = User::factory()->create();

        $project = Project::factory()->create([
            'organisation_id' => $organisation->id,
            'framework_key' => 'ppc',
        ]);

        $site = Site::factory()->create([
            'project_id' => $project->id,
            'framework_key' => 'ppc',
            'status' => EntityStatusStateMachine::STARTED,
        ]);

        $report = SiteReport::factory()->create([
            'site_id' => $site->id,
            'framework_key' => 'ppc',
            'status' => EntityStatusStateMachine::STARTED,
        ]);

        $uri = '/api/v2/workdays/site-report/' . $report->uuid;

        $this->actingAs($user)
            ->getJson($uri)
            ->assertStatus(403);

        // The endpoint should return a workday for each collection with empty demographics for each
        $response = $this->actingAs($owner)
            ->getJson($uri)
            ->assertSuccessful()
            ->assertJsonCount(count(DemographicCollections::WORKDAYS_SITE_COLLECTIONS), 'data')
            ->decodeResponseJson();
        foreach ($response['data'] as $workday) {
            $this->assertCount(0, $workday['demographics']);
        }
    }

    public function test_populated_workdays()
    {
        $organisation = Organisation::factory()->create();
        $owner = User::factory()->create(['organisation_id' => $organisation->id]);

        $project = Project::factory()->create([
            'organisation_id' => $organisation->id,
            'framework_key' => 'ppc',
        ]);

        $site = Site::factory()->create([
            'project_id' => $project->id,
            'framework_key' => 'ppc',
            'status' => EntityStatusStateMachine::STARTED,
        ]);

        $report = SiteReport::factory()->create([
            'site_id' => $site->id,
            'framework_key' => 'ppc',
            'status' => EntityStatusStateMachine::STARTED,
        ]);

        $workday = Demographic::factory()->create([
            'demographical_id' => $report->id,
        ]);
        $femaleCount = DemographicEntry::factory()->gender()->create([
            'demographic_id' => $workday->id,
            'subtype' => 'female',
        ])->amount;
        $nonBinaryCount = DemographicEntry::factory()->gender()->create([
            'demographic_id' => $workday->id,
            'subtype' => 'non-binary',
        ])->amount;
        $youthCount = DemographicEntry::factory()->age()->create([
            'demographic_id' => $workday->id,
            'subtype' => 'youth',
        ])->amount;
        $otherAgeCount = DemographicEntry::factory()->age()->create([
            'demographic_id' => $workday->id,
            'subtype' => 'other',
        ])->amount;
        $indigenousCount = DemographicEntry::factory()->ethnicity()->create([
            'demographic_id' => $workday->id,
            'subtype' => 'indigenous',
            'name' => 'Ohlone',
        ])->amount;

        $uri = '/api/v2/workdays/site-report/' . $report->uuid;

        $response = $this->actingAs($owner)
            ->getJson($uri)
            ->assertSuccessful()
            ->assertJsonCount(count(DemographicCollections::WORKDAYS_SITE_COLLECTIONS), 'data')
            ->decodeResponseJson();
        $foundCollection = false;
        Log::info('response: ' . json_encode($response['data'], JSON_PRETTY_PRINT));
        foreach ($response['data'] as $workdayData) {
            $demographics = $workdayData['demographics'];
            if ($workdayData['collection'] != $workday->collection) {
                $this->assertCount(0, $demographics);

                continue;
            }

            $foundCollection = true;
            $this->assertCount(5, $demographics);

            // They should be in creation order
            $expected = [
                ['type' => 'gender', 'subtype' => 'female', 'name' => null, 'amount' => $femaleCount],
                ['type' => 'gender', 'subtype' => 'non-binary', 'name' => null, 'amount' => $nonBinaryCount],
                ['type' => 'age', 'subtype' => 'youth', 'name' => null, 'amount' => $youthCount],
                ['type' => 'age', 'subtype' => 'other', 'name' => null, 'amount' => $otherAgeCount],
                ['type' => 'ethnicity', 'subtype' => 'indigenous', 'name' => 'Ohlone', 'amount' => $indigenousCount],
            ];
            $this->assertEquals($expected, $demographics);
        }

        $this->assertTrue($foundCollection);
    }
}
