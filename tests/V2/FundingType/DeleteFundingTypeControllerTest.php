<?php

namespace Tests\V2\FundingType;

use App\Models\User;
use App\Models\V2\FundingType;
use App\Models\V2\Organisation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteFundingTypeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_delete_funding_type()
    {
        $user = User::factory()->admin()->create();
        $fundingType = FundingType::factory()->create([
            'organisation_id' => $user->organisation->uuid,
        ]);

        $this->actingAs($user)
            ->deleteJson('/api/v2/funding-type/' . $fundingType->uuid)
            ->assertStatus(200);
    }

    public function test_user_cannot_delete_funding_type_for_other_organisation()
    {
        $user = User::factory()->create();
        $organisation = Organisation::factory()->create();
        $fundingType = FundingType::factory()->create([
            'organisation_id' => $organisation->uuid,
        ]);

        $this->actingAs($user)
            ->deleteJson('/api/v2/funding-type/' . $fundingType->uuid)
            ->assertStatus(403);
    }
}
