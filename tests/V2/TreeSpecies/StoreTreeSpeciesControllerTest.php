<?php

namespace Tests\V2\TreeSpecies;

use App\Models\User;
use App\Models\V2\Organisation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class StoreTreeSpeciesControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_tree_species(): void
    {
        $user = User::factory()->create();

        $payload = [
            'model_type' => 'organisation',
            'model_uuid' => $user->organisation->uuid,
            'amount' => 100,
            'name' => 'tree species',
        ];

        $this->actingAs($user)
            ->postJson('/api/v2/tree-species', $payload)
            ->assertStatus(201)
            ->assertJsonFragment([
                'amount' => 100,
                'name' => 'tree species',
            ]);
    }

    public function test_user_cannot_create_tree_species_for_other_organisation(): void
    {
        $user = User::factory()->create();
        $organisation = Organisation::factory()->create();

        $payload = [
            'model_type' => 'organisation',
            'model_uuid' => $organisation->uuid,
            'amount' => 100,
            'name' => 'tree species',
        ];

        $this->actingAs($user)
            ->postJson('/api/v2/tree-species', $payload)
            ->assertStatus(403);
    }
}
