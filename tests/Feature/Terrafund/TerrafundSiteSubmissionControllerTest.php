<?php

namespace Tests\Feature\Terrafund;

use App\Models\Terrafund\TerrafundProgramme;
use App\Models\Terrafund\TerrafundSite;
use App\Models\Terrafund\TerrafundSiteSubmission;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class TerrafundSiteSubmissionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testCreateAction(): void
    {
        $user = User::factory()->create();
        $programme = TerrafundProgramme::factory()->create();
        $user->terrafundProgrammes()->attach($programme->id);
        $site = TerrafundSite::factory()->create([
            'terrafund_programme_id' => $programme->id,
        ]);

        $this->actingAs($user)
            ->postJson(
                '/api/terrafund/site/submission',
                [
                    'terrafund_site_id' => $site->id,
                ],
            )
            ->assertStatus(201)
            ->assertJsonFragment([
                'terrafund_site_id' => $site->id,
            ]);
    }

    public function testCreateActionAsTerrafundAdmin(): void
    {
        $user = User::factory()->terrafundAdmin()->create();
        $site = TerrafundSite::factory()->create();

        $this->postJson(
            '/api/terrafund/site/submission',
            [
                'terrafund_site_id' => $site->id,
            ],
            $this->getHeadersForUser($user->email_address)
        )
            ->assertStatus(201)
            ->assertJsonFragment([
                'terrafund_site_id' => $site->id,
            ]);
    }

    public function testCreateActionRequiresBeingPartOfSiteProgramme(): void
    {
        $user = User::factory()->create();
        $site = TerrafundSite::factory()->create();

        $this->postJson(
            '/api/terrafund/site/submission',
            [
                'terrafund_site_id' => $site->id,
            ],
            $this->getHeadersForUser($user->email_address)
        )
            ->assertStatus(403);
    }

    public function testUpdateAction(): void
    {
        $user = User::factory()->create();
        $programme = TerrafundProgramme::factory()->create();
        $user->terrafundProgrammes()->attach($programme->id);
        $site = TerrafundSite::factory()->create([
            'terrafund_programme_id' => $programme->id,
        ]);
        $siteSubmission = TerrafundSiteSubmission::factory()->create([
            'terrafund_site_id' => $site->id,
        ]);

        $this->actingAs($user)
            ->patchJson(
                '/api/terrafund/site/submission/' . $siteSubmission->id,
                [
                    'shared_drive_link' => 'https://www.google.com/',
                ],
            )
            ->assertStatus(200)
            ->assertJsonFragment([
                'terrafund_site_id' => $site->id,
                'shared_drive_link' => 'https://www.google.com/',
            ]);
    }

    public function testUpdateActionAsTerrafundAdmin(): void
    {
        $user = User::factory()->terrafundAdmin()->create();
        $programme = TerrafundProgramme::factory()->create();
        $site = TerrafundSite::factory()->create([
            'terrafund_programme_id' => $programme->id,
        ]);
        $siteSubmission = TerrafundSiteSubmission::factory()->create([
            'terrafund_site_id' => $site->id,
        ]);

        $this->actingAs($user)
            ->patchJson(
                '/api/terrafund/site/submission/' . $siteSubmission->id,
                [
                    'shared_drive_link' => 'https://www.google.com/',
                ],
            )
            ->assertStatus(200)
            ->assertJsonFragment([
                'terrafund_site_id' => $site->id,
                'shared_drive_link' => 'https://www.google.com/',
            ]);
    }

    public function testUpdateActionRequiresBeingPartOfSiteProgramme(): void
    {
        $user = User::factory()->create();
        $programme = TerrafundProgramme::factory()->create();
        $site = TerrafundSite::factory()->create([
            'terrafund_programme_id' => $programme->id,
        ]);
        $siteSubmission = TerrafundSiteSubmission::factory()->create([
            'terrafund_site_id' => $site->id,
        ]);

        $this->actingAs($user)
            ->patchJson(
                '/api/terrafund/site/submission/' . $siteSubmission->id,
                [
                    'shared_drive_link' => 'https://www.google.com/',
                ],
            )
            ->assertStatus(403);
    }

    public function testReadAction(): void
    {
        $user = User::factory()->create();
        $terrafundProgramme = TerrafundProgramme::factory()->create();
        $user->frameworks()->attach($terrafundProgramme->framework_id);
        $user->terrafundProgrammes()->attach($terrafundProgramme->id);
        $site = TerrafundSite::factory()->create([
            'terrafund_programme_id' => $terrafundProgramme->id,
        ]);
        $siteSubmission = TerrafundSiteSubmission::factory()->create([
            'terrafund_site_id' => $site->id,
        ]);

        $this->actingAs($user)
            ->getJson('/api/terrafund/site/submission/' . $siteSubmission->id)
            ->assertStatus(200)
            ->assertJsonFragment([
                'id' => $siteSubmission->id,
            ]);
    }

    public function testReadActionRequiresBeingPartOfSiteProgramme(): void
    {
        $user = User::factory()->create();
        $terrafundProgramme = TerrafundProgramme::factory()->create();
        $user->frameworks()->attach($terrafundProgramme->framework_id);
        $site = TerrafundSite::factory()->create([
            'terrafund_programme_id' => $terrafundProgramme->id,
        ]);
        $siteSubmission = TerrafundSiteSubmission::factory()->create([
            'terrafund_site_id' => $site->id,
        ]);

        $this->actingAs($user)
            ->getJson('/api/terrafund/site/submission/' . $siteSubmission->id)
            ->assertStatus(403);
    }

    public function testReadActionAsTerrafundAdmin(): void
    {
        $user = User::factory()->terrafundAdmin()->create();
        $terrafundProgramme = TerrafundProgramme::factory()->create();
        $user->frameworks()->attach($terrafundProgramme->framework_id);
        $site = TerrafundSite::factory()->create([
            'terrafund_programme_id' => $terrafundProgramme->id,
        ]);
        $siteSubmission = TerrafundSiteSubmission::factory()->create([
            'terrafund_site_id' => $site->id,
        ]);

        $this->actingAs($user)
            ->getJson('/api/terrafund/site/submission/' . $siteSubmission->id)
            ->assertStatus(200)
            ->assertJsonFragment([
                'id' => $siteSubmission->id,
            ]);
    }

    public function testFilterAction(): void
    {
        $user = User::factory()->create();
        $terrafundProgramme = TerrafundProgramme::factory()->create();
        $user->frameworks()->attach($terrafundProgramme->framework_id);
        $user->terrafundProgrammes()->attach($terrafundProgramme->id);
        $site = TerrafundSite::factory()->create([
            'terrafund_programme_id' => $terrafundProgramme->id,
        ]);
        $siteSubmission = TerrafundSiteSubmission::factory()->create([
            'terrafund_site_id' => $site->id,
        ]);

        $this->actingAs($user)
            ->postJson(
                '/api/terrafund/site/submission/filter',
                [
                    'start_date' => Carbon::now()->subMonths(3)->toDateString(),
                    'end_date' => Carbon::now()->toDateString(),
                ]
            )
            ->assertStatus(200)
            ->assertJsonPath('data.0.id', $siteSubmission->id);
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
        $site = TerrafundSite::factory()->create([
            'terrafund_programme_id' => $terrafundProgramme->id,
        ]);
        $siteSubmission = TerrafundSiteSubmission::factory()->create([
            'terrafund_site_id' => $site->id,
        ]);

        $this->actingAs($user)
            ->postJson(
                '/api/terrafund/site/submission/filter',
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

    public function testReadAllSiteSubmissions(): void
    {
        $user = User::factory()->create();
        $terrafundProgramme = TerrafundProgramme::factory()->create();
        $user->frameworks()->attach($terrafundProgramme->framework_id);
        $user->terrafundProgrammes()->attach($terrafundProgramme->id);

        $terrafundSite = TerrafundSite::factory()->create([
            'terrafund_programme_id' => $terrafundProgramme->id,
        ]);

        $siteSubmissions = TerrafundSiteSubmission::factory()->count(3)->create([
            'terrafund_site_id' => $terrafundSite->id,
        ]);

        $response = $this->actingAs($user)
            ->getJson("/api/terrafund/site/$terrafundSite->id/submissions")
            ->assertJsonCount(3, 'data')
            ->assertStatus(200);
    }
}
