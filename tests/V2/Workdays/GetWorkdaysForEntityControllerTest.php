<?php

namespace Tests\V2\Workdays;

use App\Models\V2\Demographics\Demographic;
use App\Models\V2\Organisation;
use App\Models\V2\Projects\Project;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;
use App\Models\V2\User;
use App\Models\V2\Workdays\Workday;
use App\StateMachines\EntityStatusStateMachine;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
            ->assertJsonCount(count(Workday::SITE_COLLECTIONS), 'data')
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

        $workday = Workday::factory()->create([
            'workdayable_id' => $report->id,
        ]);
        $femaleCount = Demographic::factory()->gender()->create([
            'demographical_id' => $workday->id,
            'name' => 'female',
        ])->amount;
        $nonBinaryCount = Demographic::factory()->gender()->create([
            'demographical_id' => $workday->id,
            'name' => 'non-binary',
        ])->amount;
        $youthCount = Demographic::factory()->age()->create([
            'demographical_id' => $workday->id,
            'name' => 'youth',
        ])->amount;
        $otherAgeCount = Demographic::factory()->age()->create([
            'demographical_id' => $workday->id,
            'name' => 'other',
        ])->amount;
        $indigenousCount = Demographic::factory()->ethnicity()->create([
            'demographical_id' => $workday->id,
            'subtype' => 'indigenous',
            'name' => 'Ohlone',
        ])->amount;

        $uri = '/api/v2/workdays/site-report/' . $report->uuid;

        $response = $this->actingAs($owner)
            ->getJson($uri)
            ->assertSuccessful()
            ->assertJsonCount(count(Workday::SITE_COLLECTIONS), 'data')
            ->decodeResponseJson();
        $foundCollection = false;
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
                ['type' => 'gender', 'subtype' => null, 'name' => 'female', 'amount' => $femaleCount],
                ['type' => 'gender', 'subtype' => null, 'name' => 'non-binary', 'amount' => $nonBinaryCount],
                ['type' => 'age', 'subtype' => null, 'name' => 'youth', 'amount' => $youthCount],
                ['type' => 'age', 'subtype' => null, 'name' => 'other', 'amount' => $otherAgeCount],
                ['type' => 'ethnicity', 'subtype' => 'indigenous', 'name' => 'Ohlone', 'amount' => $indigenousCount],
            ];
            $this->assertEquals($expected, $demographics);
        }

        $this->assertTrue($foundCollection);
    }
}
