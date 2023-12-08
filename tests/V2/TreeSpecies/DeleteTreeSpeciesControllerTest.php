<?php

namespace Tests\V2\TreeSpecies;

use App\Models\User;
use App\Models\V2\Organisation;
use App\Models\V2\TreeSpecies\TreeSpecies;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class DeleteTreeSpeciesControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_delete_tree_species(): void
    {
        $user = User::factory()->admin()->create();
        $treeSpecies = TreeSpecies::factory()->create([
            'speciesable_type' => Organisation::class,
            'speciesable_id' => $user->organisation_id,
        ]);

        $this->actingAs($user)
            ->deleteJson('/api/v2/tree-species/' . $treeSpecies->uuid)
            ->assertStatus(200);
    }

    public function test_user_cannot_delete_tree_species_for_other_organisation(): void
    {
        $user = User::factory()->create();
        $organisation = Organisation::factory()->create();
        $treeSpecies = TreeSpecies::factory()->create([
            'speciesable_type' => Organisation::class,
            'speciesable_id' => $organisation->id,
        ]);

        $this->actingAs($user)
            ->deleteJson('/api/v2/tree-species/' . $treeSpecies->uuid)
            ->assertStatus(403);
    }
}
