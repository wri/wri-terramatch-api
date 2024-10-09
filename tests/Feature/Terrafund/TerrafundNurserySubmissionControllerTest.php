<?php

namespace Tests\Feature\Terrafund;

use App\Models\Terrafund\TerrafundNursery;
use App\Models\Terrafund\TerrafundNurserySubmission;
use App\Models\Terrafund\TerrafundProgramme;
use App\Models\V2\User;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class TerrafundNurserySubmissionControllerTest extends TestCase
{
    private function nurserySubmissionData($overrides = [])
    {
        return array_merge(
            [
                'seedlings_young_trees' => 12345,
                'interesting_facts' => 'These are some interesting facts',
                'site_prep' => 'How the site was prepped',
                'terrafund_nursery_id' => null,
            ],
            $overrides
        );
    }

    public function testCreateAction(): void
    {
        $user = User::factory()->create();
        $programme = TerrafundProgramme::factory()->create();
        $user->terrafundProgrammes()->attach($programme->id);
        $nursery = TerrafundNursery::factory()->create([
            'terrafund_programme_id' => $programme->id,
        ]);

        $this->postJson(
            '/api/terrafund/nursery/submission',
            $this->nurserySubmissionData([
                'terrafund_nursery_id' => $nursery->id,
            ]),
            $this->getHeadersForUser($user->email_address)
        )
        ->assertStatus(201)
        ->assertJsonFragment($this->nurserySubmissionData([
            'terrafund_nursery_id' => $nursery->id,
        ]));
    }

    public function testCreateActionAsTerrafundAdmin(): void
    {
        $user = User::factory()->terrafundAdmin()->create();
        $nursery = TerrafundNursery::factory()->create();

        $this->postJson(
            '/api/terrafund/nursery/submission',
            $this->nurserySubmissionData([
                'terrafund_nursery_id' => $nursery->id,
            ]),
            $this->getHeadersForUser($user->email_address)
        )
        ->assertStatus(201)
        ->assertJsonFragment($this->nurserySubmissionData([
            'terrafund_nursery_id' => $nursery->id,
        ]));
    }

    public function testCreateActionRequiresBeingPartOfNurseryProgramme(): void
    {
        $user = User::factory()->create();
        $nursery = TerrafundNursery::factory()->create();

        $this->postJson(
            '/api/terrafund/nursery/submission',
            $this->nurserySubmissionData([
                'terrafund_nursery_id' => $nursery->id,
            ]),
            $this->getHeadersForUser($user->email_address)
        )
        ->assertStatus(403);
    }

    public function testReadAction(): void
    {
        $user = User::factory()->create();
        $terrafundProgramme = TerrafundProgramme::factory()->create();
        $user->frameworks()->attach($terrafundProgramme->framework_id);
        $user->terrafundProgrammes()->attach($terrafundProgramme->id);
        $nursery = TerrafundNursery::factory()->create([
            'terrafund_programme_id' => $terrafundProgramme->id,
        ]);
        $nurserySubmission = TerrafundNurserySubmission::factory()->create([
            'terrafund_nursery_id' => $nursery->id,
        ]);

        $this->actingAs($user)
            ->getJson('/api/terrafund/nursery/submission/' . $nurserySubmission->id)
            ->assertStatus(200)
            ->assertJsonFragment([
                'id' => $nurserySubmission->id,
            ]);
    }

    public function testReadActionRequiresBeingPartOfNurseryProgramme(): void
    {
        $user = User::factory()->create();
        $terrafundProgramme = TerrafundProgramme::factory()->create();
        $user->frameworks()->attach($terrafundProgramme->framework_id);
        $nursery = TerrafundNursery::factory()->create([
            'terrafund_programme_id' => $terrafundProgramme->id,
        ]);
        $nurserySubmission = TerrafundNurserySubmission::factory()->create([
            'terrafund_nursery_id' => $nursery->id,
        ]);

        $this->actingAs($user)
            ->getJson('/api/terrafund/nursery/submission/' . $nurserySubmission->id)
            ->assertStatus(403);
    }

    public function testReadActionAsTerrafundAdmin(): void
    {
        $user = User::factory()->terrafundAdmin()->create();
        $terrafundProgramme = TerrafundProgramme::factory()->create();
        $user->frameworks()->attach($terrafundProgramme->framework_id);
        $nursery = TerrafundNursery::factory()->create([
            'terrafund_programme_id' => $terrafundProgramme->id,
        ]);
        $nurserySubmission = TerrafundNurserySubmission::factory()->create([
            'terrafund_nursery_id' => $nursery->id,
        ]);

        $this->actingAs($user)
            ->getJson('/api/terrafund/nursery/submission/' . $nurserySubmission->id)
            ->assertStatus(200)
            ->assertJsonFragment([
                'id' => $nurserySubmission->id,
            ]);
    }

    public function testUpdateAction(): void
    {
        $user = User::factory()->create();
        $terrafundProgramme = TerrafundProgramme::factory()->create();
        $user->frameworks()->attach($terrafundProgramme->framework_id);
        $user->terrafundProgrammes()->attach($terrafundProgramme->id);
        $nursery = TerrafundNursery::factory()->create([
            'terrafund_programme_id' => $terrafundProgramme->id,
        ]);
        $nurserySubmission = TerrafundNurserySubmission::factory()->create([
            'terrafund_nursery_id' => $nursery->id,
        ]);

        $this->actingAs($user)
            ->patchJson('/api/terrafund/nursery/submission/' . $nurserySubmission->id, [
                'seedlings_young_trees' => 10,
                'interesting_facts' => 'new interesting facts',
                'site_prep' => 'new site prep',
            ])
            ->assertStatus(200)
            ->assertJsonFragment([
                'id' => $nurserySubmission->id,
                'seedlings_young_trees' => 10,
                'interesting_facts' => 'new interesting facts',
                'site_prep' => 'new site prep',
                'terrafund_nursery_id' => $nurserySubmission->terrafund_nursery_id,
            ]);
    }

    public function testUpdateActionRequiresBeingPartOfNurseryProgramme(): void
    {
        $user = User::factory()->create();
        $terrafundProgramme = TerrafundProgramme::factory()->create();
        $user->frameworks()->attach($terrafundProgramme->framework_id);
        $nursery = TerrafundNursery::factory()->create([
            'terrafund_programme_id' => $terrafundProgramme->id,
        ]);
        $nurserySubmission = TerrafundNurserySubmission::factory()->create([
            'terrafund_nursery_id' => $nursery->id,
        ]);

        $this->actingAs($user)
            ->patchJson('/api/terrafund/nursery/submission/' . $nurserySubmission->id, [
                'seedlings_young_trees' => 10,
                'interesting_facts' => 'new interesting facts',
                'site_prep' => 'new site prep',
            ])
            ->assertStatus(403);
    }

    public function testUpdateActionAsTerrafundAdmin(): void
    {
        $user = User::factory()->terrafundAdmin()->create();
        $terrafundProgramme = TerrafundProgramme::factory()->create();
        $user->frameworks()->attach($terrafundProgramme->framework_id);
        $nursery = TerrafundNursery::factory()->create([
            'terrafund_programme_id' => $terrafundProgramme->id,
        ]);
        $nurserySubmission = TerrafundNurserySubmission::factory()->create([
            'terrafund_nursery_id' => $nursery->id,
        ]);

        $this->actingAs($user)
            ->patchJson('/api/terrafund/nursery/submission/' . $nurserySubmission->id, [
                'seedlings_young_trees' => 10,
                'interesting_facts' => 'new interesting facts',
                'site_prep' => 'new site prep',
            ])
            ->assertStatus(200)
            ->assertJsonFragment([
                'id' => $nurserySubmission->id,
                'seedlings_young_trees' => 10,
                'interesting_facts' => 'new interesting facts',
                'site_prep' => 'new site prep',
                'terrafund_nursery_id' => $nurserySubmission->terrafund_nursery_id,
            ]);
    }

    public function testFilterAction(): void
    {
        $user = User::factory()->create();
        $terrafundProgramme = TerrafundProgramme::factory()->create();
        $user->frameworks()->attach($terrafundProgramme->framework_id);
        $user->terrafundProgrammes()->attach($terrafundProgramme->id);
        $nursery = TerrafundNursery::factory()->create([
            'terrafund_programme_id' => $terrafundProgramme->id,
        ]);
        $nurserySubmission = TerrafundNurserySubmission::factory()->create([
            'terrafund_nursery_id' => $nursery->id,
        ]);

        $this->actingAs($user)
            ->postJson(
                '/api/terrafund/nursery/submission/filter',
                [
                    'start_date' => Carbon::now()->subMonths(3)->toDateString(),
                    'end_date' => Carbon::now()->toDateString(),
                ]
            )
            ->assertStatus(200)
            ->assertJsonPath('data.0.id', $nurserySubmission->id);
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
        $nursery = TerrafundNursery::factory()->create([
            'terrafund_programme_id' => $terrafundProgramme->id,
        ]);
        $nurserySubmission = TerrafundNurserySubmission::factory()->create([
            'terrafund_nursery_id' => $nursery->id,
        ]);

        $this->actingAs($user)
            ->postJson(
                '/api/terrafund/nursery/submission/filter',
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

    public function testReadAllNurserySubmissions(): void
    {
        $user = User::factory()->create();
        $terrafundProgramme = TerrafundProgramme::factory()->create();
        $user->frameworks()->attach($terrafundProgramme->framework_id);
        $user->terrafundProgrammes()->attach($terrafundProgramme->id);

        $terrafundNursery = TerrafundNursery::factory()->create([
            'terrafund_programme_id' => $terrafundProgramme->id,
        ]);

        $nurserySubmissions = TerrafundNurserySubmission::factory()->count(3)->create([
            'terrafund_nursery_id' => $terrafundNursery->id,
        ]);

        $response = $this->actingAs($user)
            ->getJson("/api/terrafund/nursery/$terrafundNursery->id/submissions")
            ->assertJsonCount(3, 'data')
            ->assertStatus(200);
    }
}
