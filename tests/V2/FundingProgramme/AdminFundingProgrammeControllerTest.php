<?php

namespace Tests\V2\FundingProgramme;

use App\Models\V2\FundingProgramme;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminFundingProgrammeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testStoreAction()
    {
        $user = User::factory()->admin()->create();

        $payload = [
            'name' => 'funding programme',
            'description' => 'description',
            'status' => 'active',
            'read_more_url' => 'https://this.link/',
            'location' => 'USA',
            'organisation_types' => [
                'for-profit-organization',
            ],
        ];

        $this->actingAs($user)
            ->postJson('/api/v2/admin/funding-programme', $payload)
            ->assertStatus(201)
            ->assertJsonFragment([
                'name' => 'funding programme',
                'description' => 'description',
                'status' => 'active',
                'read_more_url' => 'https://this.link/',
                'location' => 'USA',
                'deadline_at' => null,
                'organisation_types' => [
                    'for-profit-organization',
                ],
            ]);
    }

    public function testStoreActionCannotBePerformedByNonAdmin()
    {
        $user = User::factory()->create();

        $payload = [
            'name' => 'funding programme',
            'description' => 'description',
            'status' => 'active',
        ];

        $this->actingAs($user)
            ->postJson('/api/v2/admin/funding-programme', $payload)
            ->assertStatus(403);
    }

    public function testUpdateAction()
    {
        $user = User::factory()->admin()->create();
        $fundingProgramme = FundingProgramme::factory()->create();

        $payload = [
            'name' => 'new name',
            'read_more_url' => 'https://this.link/',
            'location' => 'Bosnia',
            'organisation_types' => [
                'non-profit-organization',
                'government-agency',
            ],
        ];

        $this->actingAs($user)
            ->putJson('/api/v2/admin/funding-programme/' . $fundingProgramme->uuid, $payload)
            ->assertStatus(200)
            ->assertJsonFragment([
                'name' => 'new name',
                'read_more_url' => 'https://this.link/',
                'location' => 'Bosnia',
                'organisation_types' => [
                    'non-profit-organization',
                    'government-agency',
                ],
            ]);
    }

    public function testUpdateActionCannotBePerformedByNonAdmin()
    {
        $user = User::factory()->create();
        $fundingProgramme = FundingProgramme::factory()->create();

        $payload = [
            'name' => 'new name',
        ];

        $this->actingAs($user)
            ->putJson('/api/v2/admin/funding-programme/' . $fundingProgramme->uuid, $payload)
            ->assertStatus(403);
    }
}
