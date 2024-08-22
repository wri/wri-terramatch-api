<?php

namespace Tests\Feature\Terrafund;

use App\Models\Terrafund\TerrafundNursery;
use App\Models\Terrafund\TerrafundNurserySubmission;
use App\Models\Terrafund\TerrafundProgramme;
use App\Models\Terrafund\TerrafundProgrammeSubmission;
use App\Models\Terrafund\TerrafundSite;
use App\Models\Terrafund\TerrafundSiteSubmission;
use App\Models\V2\User;
use Carbon\Carbon;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class TerrafundProgrammeSubmissionControllerTest extends TestCase
{
    private function programmeSubmissionData($overrides = [])
    {
        return array_merge(
            [
                'landscape_community_contribution' => 'option_1',
                'top_three_successes' => 'the top three successes are 1, 2, 3',
                'maintenance_and_monitoring_activities' => 'these are the maintenance and monitoring activities',
                'significant_change' => 'a significant change',
                'percentage_survival_to_date' => 25,
                'survival_calculation' => 'calculation method',
                'survival_comparison' => 'comparison of survival',
                'ft_women' => 100,
                'ft_men' => 100,
                'ft_youth' => 100,
                'ft_total' => 500,
                'pt_women' => 100,
                'pt_men' => 100,
                'pt_youth' => 100,
                'pt_total' => 500,
                'volunteer_women' => 100,
                'volunteer_men' => 100,
                'volunteer_youth' => 100,
                'volunteer_total' => 500,
                'people_annual_income_increased' => 200,
                'people_knowledge_skills_increased' => 300,
            ],
            $overrides
        );
    }

    public function testCreateAction(): void
    {
        $user = User::factory()->create();
        $programme = TerrafundProgramme::factory()->create();
        $user->terrafundProgrammes()->attach($programme->id);

        $this->actingAs($user)
            ->postJson(
                '/api/terrafund/programme/submission',
                $this->programmeSubmissionData([
                    'terrafund_programme_id' => $programme->id,
                ])
            )
            ->assertStatus(201)
            ->assertJsonFragment(
                $this->programmeSubmissionData([
                    'terrafund_programme_id' => $programme->id,
                ])
            );
    }

    public function testUpdateAction(): void
    {
        $user = User::factory()->create();
        $programme = TerrafundProgramme::factory()->create();
        $user->terrafundProgrammes()->attach($programme->id);
        $submission = TerrafundProgrammeSubmission::factory()->create([
            'terrafund_programme_id' => $programme->id,
        ]);

        $this->actingAs($user)
            ->patchJson(
                '/api/terrafund/programme/submission/' . $submission->id,
                $this->programmeSubmissionData()
            )
            ->assertStatus(200)
            ->assertJsonFragment(
                $this->programmeSubmissionData()
            );
    }

    public function testUpdateActionRequiresAccessToProgramme(): void
    {
        $user = User::factory()->create();
        $programme = TerrafundProgramme::factory()->create();
        $user->terrafundProgrammes()->attach($programme->id);
        $submission = TerrafundProgrammeSubmission::factory()->create();

        $this->actingAs($user)
            ->patchJson(
                '/api/terrafund/programme/submission/' . $submission->id,
                $this->programmeSubmissionData()
            )
            ->assertStatus(403);
    }

    public function testReadAction(): void
    {
        $user = User::factory()->create();
        $programme = TerrafundProgramme::factory()->create();
        $user->terrafundProgrammes()->attach($programme->id);
        $submission = TerrafundProgrammeSubmission::factory()->create([
            'terrafund_programme_id' => $programme->id,
        ]);

        $this->actingAs($user)
            ->getJson('/api/terrafund/programme/submission/' . $submission->id)
            ->assertStatus(200)
            ->assertJsonFragment([
                'id' => $submission->id,
                'landscape_community_contribution' => $submission->landscape_community_contribution,
                'top_three_successes' => $submission->top_three_successes,
                'maintenance_and_monitoring_activities' => $submission->maintenance_and_monitoring_activities,
                'significant_change' => $submission->significant_change,
                'percentage_survival_to_date' => $submission->percentage_survival_to_date,
                'survival_comparison' => $submission->survival_comparison,
                'survival_calculation' => $submission->survival_calculation,
                'ft_women' => $submission->ft_women,
                'ft_men' => $submission->ft_men,
                'ft_youth' => $submission->ft_youth,
                'ft_total' => $submission->ft_total,
                'pt_women' => $submission->pt_women,
                'pt_men' => $submission->pt_men,
                'pt_youth' => $submission->pt_youth,
                'pt_total' => $submission->pt_total,
                'volunteer_women' => $submission->volunteer_women,
                'volunteer_men' => $submission->volunteer_men,
                'volunteer_youth' => $submission->volunteer_youth,
                'volunteer_total' => $submission->volunteer_total,
                'people_annual_income_increased' => $submission->people_annual_income_increased,
                'people_knowledge_skills_increased' => $submission->people_knowledge_skills_increased,
                'terrafund_programme_id' => $submission->terrafund_programme_id,
                'created_at' => $submission->created_at,
                'updated_at' => $submission->updated_at,
            ]);
    }

    public function testReadActionRequiresProgrammeAccess(): void
    {
        $user = User::factory()->create();
        $programme = TerrafundProgramme::factory()->create();
        $user->terrafundProgrammes()->attach($programme->id);
        $submission = TerrafundProgrammeSubmission::factory()->create();

        $this->actingAs($user)
            ->getJson('/api/terrafund/programme/submission/' . $submission->id)
            ->assertStatus(403);
    }

    #[DataProvider('invalidCreateActionDataProvider')]
    public function testCreateActionValidation($dataProvider): void
    {
        $user = User::factory()->create();
        $programme = TerrafundProgramme::factory()->create();
        $user->terrafundProgrammes()->attach($programme->id);

        $this->actingAs($user)
            ->postJson(
                '/api/terrafund/programme/submission',
                $this->programmeSubmissionData(array_merge(
                    ['terrafund_programme_id' => $programme->id],
                    $dataProvider,
                ))
            )
        ->assertStatus(422);
    }

    public function testCreateActionAsTerrafundAdmin(): void
    {
        $user = User::factory()->terrafundAdmin()->create();
        $programme = TerrafundProgramme::factory()->create();

        $this->actingAs($user)
            ->postJson(
                '/api/terrafund/programme/submission',
                $this->programmeSubmissionData([
                    'terrafund_programme_id' => $programme->id,
                ]),
            )
            ->assertStatus(201)
            ->assertJsonFragment($this->programmeSubmissionData([
                'terrafund_programme_id' => $programme->id,
            ]));
    }

    public function testCreateActionRequiresBeingPartOfProgrammeProgramme(): void
    {
        $user = User::factory()->create();
        $programme = TerrafundProgramme::factory()->create();

        $this->actingAs($user)
            ->postJson(
                '/api/terrafund/programme/submission',
                $this->programmeSubmissionData([
                    'terrafund_programme_id' => $programme->id,
                ]),
            )
            ->assertStatus(403);
    }

    public function testFilterAction(): void
    {
        $user = User::factory()->create();
        $terrafundProgramme = TerrafundProgramme::factory()->create();
        $user->frameworks()->attach($terrafundProgramme->framework_id);
        $user->terrafundProgrammes()->attach($terrafundProgramme->id);

        $programmeSubmission = TerrafundProgrammeSubmission::factory()->create([
            'terrafund_programme_id' => $terrafundProgramme->id,
        ]);

        $this->actingAs($user)
            ->postJson(
                '/api/terrafund/programme/submission/filter',
                [
                    'start_date' => Carbon::now()->subMonths(3)->toDateString(),
                    'end_date' => Carbon::now()->toDateString(),
                ]
            )
            ->assertStatus(200)
            ->assertJsonPath('data.0.id', $programmeSubmission->id);
    }

    #[DataProvider('invalidDateDataProvider')]
    public function testFilterActionInvalidData(array $data): void
    {
        $knownDate = Carbon::create(1998, 1, 19, 12);
        Carbon::setTestNow($knownDate);
        $user = User::factory()->create();
        $terrafundProgramme = TerrafundProgramme::factory()->create();
        $user->frameworks()->attach($terrafundProgramme->framework_id);
        $user->terrafundProgrammes()->attach($terrafundProgramme->id);

        $programmeSubmission = TerrafundProgrammeSubmission::factory()->create([
            'terrafund_programme_id' => $terrafundProgramme->id,
        ]);

        $this->actingAs($user)
            ->postJson(
                '/api/terrafund/programme/submission/filter',
                $data
            )
            ->assertStatus(422);

        Carbon::setTestNow();
    }

    public static function invalidDateDataProvider(): array
    {
        return [
            'Start Empty' => [['start_date' => '',  'end_date' => '1998-01-10']],
            'End Empty' => [['start_date' => '1998-01-18',  'end_date' => '']],
            'End Before Start' => [['start_date' => '1998-01-19',  'end_date' => '1998-01-10']],
            'End in future ' => [['start_date' => '1998-01-17',  'end_date' => '1998-01-31']],
            'Start in dd/mm/yyyy ' => [['start_date' => '15/01/1998',  'end_date' => '1998-01-19']],
            'End in dd/mm/yyyy ' => [['start_date' => '1998-01-15',  'end_date' => '19/01/1998']],
            'Start non-string' => [['start_date' => 15011998,  'end_date' => '1998-01-19']],
            'End non-string' => [['start_date' => '1998-01-15',  'end_date' => 19011998]],
        ];
    }

    public static function invalidCreateActionDataProvider(): array
    {
        return [
            'Percentage above 100' => [['percentage_survival_to_date' => 102]],
            'Percentage below 0' => [['percentage_survival_to_date' => -10]],
            'Survival calculation above 240' => [['survival_calculation' => Str::random(5001)]],
            'Survival comparison above 240' => [['survival_comparison' => Str::random(5001)]],
        ];
    }

    public function testReadAllProgrammeSubmissions(): void
    {
        $user = User::factory()->create();
        $terrafundProgramme = TerrafundProgramme::factory()->create();
        $user->frameworks()->attach($terrafundProgramme->framework_id);
        $user->terrafundProgrammes()->attach($terrafundProgramme->id);

        $terrafundSite = TerrafundSite::factory()->create([
            'terrafund_programme_id' => $terrafundProgramme->id,
        ]);

        $terrafundNursery = TerrafundNursery::factory()->create([
            'terrafund_programme_id' => $terrafundProgramme->id,
        ]);

        TerrafundProgrammeSubmission::factory()->count(3)->create([
            'terrafund_programme_id' => $terrafundProgramme->id,
        ]);

        TerrafundSiteSubmission::factory()->count(3)->create([
            'terrafund_site_id' => $terrafundSite->id,
        ]);

        TerrafundNurserySubmission::factory()->count(3)->create([
            'terrafund_nursery_id' => $terrafundNursery->id,
        ]);

        $response = $this->actingAs($user)
            ->getJson("/api/terrafund/programme/$terrafundProgramme->id/submissions")
            ->assertJsonCount(5, 'data')
            ->assertStatus(200);
    }
}
