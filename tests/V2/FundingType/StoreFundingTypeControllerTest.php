<?php

namespace Tests\V2\FundingType;

use App\Models\V2\Organisation;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreFundingTypeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_funding_type()
    {
        $user = User::factory()->create();

        $payload = [
            'organisation_id' => $user->organisation->uuid,
            'amount' => 12345,
            'year' => 1998,
            'type' => 'private_grant',
        ];

        $this->actingAs($user)
            ->postJson('/api/v2/funding-type', $payload)
            ->assertStatus(201)
            ->assertJsonFragment([
                'organisation_id' => $user->organisation->uuid,
                'amount' => 12345,
                'year' => 1998,
                'type' => 'private_grant',
            ]);
    }

    public function test_user_cannot_create_funding_type_for_other_organisation()
    {
        $user = User::factory()->create();
        $organisation = Organisation::factory()->create();

        $payload = [
            'organisation_id' => $organisation->uuid,
            'amount' => 12345,
            'year' => 1998,
            'type' => 'private_grant',
        ];

        $this->actingAs($user)
            ->postJson('/api/v2/funding-type', $payload)
            ->assertStatus(403);
    }
}
