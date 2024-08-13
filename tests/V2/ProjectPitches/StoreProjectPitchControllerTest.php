<?php

namespace Tests\V2\ProjectPitches;

use App\Models\V2\FundingProgramme;
use App\Models\V2\Organisation;
use App\Models\V2\ProjectPitch;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreProjectPitchControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_project_pitch(): void
    {
        $organisation = Organisation::factory()->create();
        $fundingProgramme = FundingProgramme::factory()->create();
        $user = User::factory()->create([
            'organisation_id' => $organisation,
        ]);

        $payload = ProjectPitch::factory()->make([
            'project_name' => 'Alpha Project Pitch',
            'organisation_id' => $organisation->uuid,
            'funding_programme_id' => $fundingProgramme->uuid,
        ])->toArray();

        $this->actingAs($user)
            ->postJson('/api/v2/project-pitches', $payload)
            ->assertStatus(201)
            ->assertJsonFragment([
                'project_name' => 'Alpha Project Pitch',
                'organisation_id' => $organisation->uuid,
            ]);
    }

    public function test_user_cannot_create_project_pitch_for_another_organisation(): void
    {
        $user = User::factory()->create();
        $organisation = Organisation::factory()->create();
        $fundingProgramme = FundingProgramme::factory()->create();

        $payload = [
            'organisation_id' => $organisation->uuid,
            'funding_programme_id' => $fundingProgramme->uuid,
            'project_name' => 'project name',
            'project_objectives' => 'objectives here',
            'project_country' => 'CZ',
            'project_county_district' => 'district',
            'restoration_intervention_types' => ['mangrove_tree_restoration'],
            'capacity_building_needs' => ['site_selection'],
            'total_hectares' => 12345,
            'total_trees' => 65432,
        ];

        $this->actingAs($user)
            ->postJson('/api/v2/project-pitches', $payload)
            ->assertStatus(403);
    }
}
