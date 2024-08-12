<?php

namespace Tests\V2\FundingProgramme;

use App\Models\V2\FundingProgramme;
use App\Models\V2\Organisation;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class FundingProgrammeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testIndexAction(): void
    {
        $organisation = Organisation::factory()->create([
            'type' => Organisation::TYPE_GOVERNMENT,
        ]);
        $user = User::factory()->admin()->create([
            'organisation_id' => $organisation->id,
        ]);
        FundingProgramme::factory()->count(3)->create([
            'organisation_types' => [
                Organisation::TYPE_NON_PROFIT,
            ],
        ]);
        FundingProgramme::factory()->create([
            'organisation_types' => [
                Organisation::TYPE_GOVERNMENT,
            ],
        ]);

        $this->actingAs($user)
            ->getJson('/api/v2/funding-programme')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }
}
