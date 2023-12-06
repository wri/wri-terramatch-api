<?php

namespace Tests\V2\TreeSpecies;

use App\Models\User;
use App\Models\V2\Organisation;
use App\Models\V2\TreeSpecies\TreeSpecies;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class UpdateTreeSpeciesControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_tree_species(): void
    {
        $user = User::factory()->create();
        $treeSpecies = TreeSpecies::factory()->create([
            'speciesable_type' => Organisation::class,
            'speciesable_id' => $user->organisation_id,
        ]);

        $payload = [
            'amount' => 100,
            'name' => 'tree species',
        ];

        $this->actingAs($user)
            ->patchJson('/api/v2/tree-species/' . $treeSpecies->uuid, $payload)
            ->assertStatus(200)
            ->assertJsonFragment([
                'amount' => 100,
                'name' => 'tree species',
            ]);
    }

    public function test_user_cannot_update_tree_species_for_other_organisation(): void
    {
        $user = User::factory()->create();
        $organisation = Organisation::factory()->create();
        $treeSpecies = TreeSpecies::factory()->create([
            'speciesable_type' => Organisation::class,
            'speciesable_id' => $organisation->id,
        ]);

        $payload = [
            'amount' => 100,
            'name' => 'tree species',
        ];

        $this->actingAs($user)
            ->patchJson('/api/v2/tree-species/' . $treeSpecies->uuid, $payload)
            ->assertStatus(403);
    }
}
