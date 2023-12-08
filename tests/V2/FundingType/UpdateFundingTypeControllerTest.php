<?php

namespace Tests\V2\FundingType;

use App\Models\User;
use App\Models\V2\FundingType;
use App\Models\V2\Organisation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateFundingTypeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_funding_type()
    {
        $user = User::factory()->create();
        $fundingType = FundingType::factory()->create([
            'organisation_id' => $user->organisation->uuid,
        ]);

        $payload = [
            'amount' => 12345,
            'year' => 1998,
            'type' => 'private_grant',
        ];

        $this->actingAs($user)
            ->patchJson('/api/v2/funding-type/' . $fundingType->uuid, $payload)
            ->assertStatus(200)
            ->assertJsonFragment([
                'amount' => 12345,
                'year' => 1998,
                'type' => 'private_grant',
            ]);
    }

    public function test_user_cannot_update_funding_type_for_other_organisation()
    {
        $user = User::factory()->create();
        $organisation = Organisation::factory()->create();
        $fundingType = FundingType::factory()->create([
            'organisation_id' => $organisation->uuid,
        ]);

        $payload = [
            'amount' => 12345,
            'year' => 1998,
            'type' => 'private_grant',
        ];

        $this->actingAs($user)
            ->patchJson('/api/v2/funding-type/' . $fundingType->uuid, $payload)
            ->assertStatus(403);
    }
}
