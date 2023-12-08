<?php

namespace Tests\V2\ProjectPitches;

use App\Models\User;
use App\Models\V2\ProjectPitch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class UpdateProjectPitchControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_can_update_forms(): void
    {
        $user = User::factory()->admin()->create();
        $projectPitch = ProjectPitch::factory()->create([
            'organisation_id' => $user->organisation->uuid,
        ]);

        $payload = [
            'project_name' => 'project name',
            'project_objectives' => 'objectives here',
            'project_country' => 'CZ',
            'project_county_district' => 'district',
            'restoration_intervention_types' => ['mangrove_tree_restoration'],
            'total_hectares' => 12345,
            'total_trees' => 65432,
            'capacity_building_needs' => ['site_selection'],
        ];

        $this->actingAs($user)
            ->patchJson('/api/v2/project-pitches/' . $projectPitch->uuid, $payload)
            ->assertStatus(200)
            ->assertJsonFragment($payload);
    }

    public function test_users_cannot_update_pitches_that_do_not_belong_to_them(): void
    {
        $user = User::factory()->create();
        $projectPitch = ProjectPitch::factory()->create();

        $payload = [
            'project_name' => 'project name',
            'project_objectives' => 'objectives here',
            'project_country' => 'CZ',
            'project_county_district' => 'district',
            'restoration_intervention_types' => ['mangrove_tree_restoration'],
            'total_hectares' => 12345,
            'total_trees' => 65432,
            'capacity_building_needs' => ['site_selection'],
        ];

        $this->actingAs($user)
            ->patchJson('/api/v2/project-pitches/' . $projectPitch->uuid, $payload)
            ->assertStatus(403);
    }
}
