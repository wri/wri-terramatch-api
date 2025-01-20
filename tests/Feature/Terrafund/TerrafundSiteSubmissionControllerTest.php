<?php

namespace Tests\Feature\Terrafund;

use App\Models\Terrafund\TerrafundSite;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TerrafundSiteSubmissionControllerTest extends TestCase
{
    use RefreshDatabase;

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
}
