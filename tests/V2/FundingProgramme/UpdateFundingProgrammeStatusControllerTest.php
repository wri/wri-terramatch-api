<?php

namespace Tests\V2\FundingProgramme;

use App\Models\User;
use App\Models\V2\FundingProgramme;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class UpdateFundingProgrammeStatusControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testAction(): void
    {
        $user = User::factory()->admin()->create();
        $fundingProgramme = FundingProgramme::factory()->create([
            'status' => 'active',
        ]);

        $payload = [
            'status' => 'inactive',
        ];

        $this->actingAs($user)
            ->patchJson('/api/v2/admin/funding-programme/' . $fundingProgramme->uuid, $payload)
            ->assertStatus(200)
            ->assertJsonFragment([
                'status' => 'inactive',
            ]);
    }

    public function testActionCannotBePerformedByNonAdmin(): void
    {
        $user = User::factory()->create();
        $fundingProgramme = FundingProgramme::factory()->create([
            'status' => 'active',
        ]);

        $payload = [
            'status' => 'inactive',
        ];

        $this->actingAs($user)
            ->patchJson('/api/v2/admin/funding-programme/' . $fundingProgramme->uuid, $payload)
            ->assertStatus(403);
    }
}
