<?php

namespace Tests\Legacy\Feature\Terrafund;

use App\Models\Terrafund\TerrafundNursery;
use App\Models\Terrafund\TerrafundProgramme;
use Illuminate\Support\Facades\Auth;
use Tests\Legacy\LegacyTestCase;

final class LegacyTerrafundTreeSpeciesControllerTest extends LegacyTestCase
{
    public function testCreateAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'terrafund@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->postJson('/api/terrafund/tree_species', [
            'treeable_type' => 'programme',
            'treeable_id' => 1,
            'name' => 'tree species',
            'amount' => 5,
        ], $headers)
        ->assertHeader('Content-Type', 'application/json')
        ->assertStatus(201)
        ->assertJsonFragment([
            'treeable_type' => TerrafundProgramme::class,
            'treeable_id' => 1,
            'name' => 'tree species',
            'amount' => 5,
        ]);
    }

    public function testCreateActionRequiresBeingPartOfProgramme(): void
    {
        $token = Auth::attempt([
            'email_address' => 'sue@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->postJson('/api/terrafund/tree_species', [
            'treeable_type' => 'programme',
            'treeable_id' => 1,
            'name' => 'tree species',
            'amount' => 5,
        ], $headers)
        ->assertHeader('Content-Type', 'application/json')
        ->assertStatus(403);
    }

    public function testCreateActionAsNursery(): void
    {
        $token = Auth::attempt([
            'email_address' => 'terrafund@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->postJson('/api/terrafund/tree_species', [
            'treeable_type' => 'nursery',
            'treeable_id' => 1,
            'name' => 'tree species',
            'amount' => 5,
        ], $headers)
        ->assertHeader('Content-Type', 'application/json')
        ->assertStatus(201)
        ->assertJsonFragment([
            'treeable_type' => TerrafundNursery::class,
            'treeable_id' => 1,
            'name' => 'tree species',
            'amount' => 5,
        ]);
    }

    public function testCreateActionWithArrayRequiresBeingPartOfNursery(): void
    {
        $token = Auth::attempt([
            'email_address' => 'sue@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->postJson('/api/terrafund/tree_species', [
            'treeable_type' => 'nursery',
            'treeable_id' => 1,
            'name' => 'tree species',
            'amount' => 5,
        ], $headers)
        ->assertHeader('Content-Type', 'application/json')
        ->assertStatus(403);
    }

    public function testDeleteAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'terrafund@example.com',
            'password' => 'Password123',
        ]);

        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->deleteJson('/api/terrafund/tree_species/1', $headers)
        ->assertStatus(200);
    }

    public function testDeleteActionRequiresBeingPartOfProgramme(): void
    {
        $token = Auth::attempt([
            'email_address' => 'sue@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->deleteJson('/api/terrafund/tree_species/1', $headers)
        ->assertStatus(403);
    }
}
