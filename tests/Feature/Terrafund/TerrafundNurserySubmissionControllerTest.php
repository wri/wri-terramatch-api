<?php

namespace Tests\Feature\Terrafund;

use App\Models\Terrafund\TerrafundNursery;
use App\Models\V2\User;
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
