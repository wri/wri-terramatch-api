<?php

namespace Tests\Legacy\Feature;

use App\Models\TreeSpecies as TreeSpeciesModel;
use Illuminate\Support\Facades\Auth;
use Tests\Legacy\LegacyTestCase;

final class TreeSpeciesControllerTest extends LegacyTestCase
{
    public function testCreateAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $data = [
            'pitch_id' => 1,
            'name' => 'Oak',
            'is_native' => true,
            'count' => 100,
            'price_to_plant' => 0.50,
            'price_to_maintain' => 1.25,
            'saplings' => 10.50,
            'site_prep' => 30.35,
            'survival_rate' => 75,
            'produces_food' => null,
            'produces_firewood' => null,
            'produces_timber' => null,
            'owner' => 'community',
            'season' => 'winter',
        ];
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $response = $this->postJson('/api/tree_species', $data, $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'status',
                'approved_rejected_by',
                'rejected_reason',
                'rejected_reason_body',
                'data' => [
                    'name',
                    'is_native',
                    'count',
                    'price_to_plant',
                    'price_to_maintain',
                    'saplings',
                    'site_prep',
                    'survival_rate',
                    'produces_food',
                    'produces_firewood',
                    'produces_timber',
                    'owner',
                    'season',
                ],
            ],
        ]);
    }

    public function testReadAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];
        $response = $this->getJson('/api/tree_species/1', $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'pitch_id',
                'name',
                'is_native',
                'count',
                'price_to_plant',
                'price_to_maintain',
                'saplings',
                'site_prep',
                'survival_rate',
                'produces_food',
                'produces_firewood',
                'produces_timber',
                'owner',
                'season',
            ],
        ]);
    }

    public function testUpdateAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];
        $data = [
            'name' => 'Pine',
        ];
        $response = $this->patchJson('/api/tree_species/1', $data, $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'status',
                'approved_rejected_by',
                'rejected_reason',
                'rejected_reason_body',
                'data' => [
                    'name',
                    'is_native',
                    'count',
                    'price_to_plant',
                    'price_to_maintain',
                    'saplings',
                    'site_prep',
                    'survival_rate',
                    'produces_food',
                    'produces_firewood',
                    'produces_timber',
                    'owner',
                    'season',
                ],
            ],
        ]);
    }

    public function testReadAllByPitchAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];
        $response = $this->getJson('/api/pitches/1/tree_species', $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                [
                    'id',
                    'pitch_id',
                    'name',
                    'is_native',
                    'count',
                    'price_to_plant',
                    'price_to_maintain',
                    'saplings',
                    'site_prep',
                    'survival_rate',
                    'produces_food',
                    'produces_firewood',
                    'produces_timber',
                    'owner',
                    'season',
                ],
            ],
        ]);
    }

    public function testInspectByPitchAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];
        $response = $this->getJson('/api/pitches/1/tree_species/inspect', $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                [
                    'id',
                    'status',
                    'approved_rejected_by',
                    'approved_rejected_at',
                    'rejected_reason',
                    'rejected_reason_body',
                    'data' => [
                        'id',
                        'pitch_id',
                        'name',
                        'is_native',
                        'count',
                        'price_to_plant',
                        'price_to_maintain',
                        'saplings',
                        'site_prep',
                        'survival_rate',
                        'produces_food',
                        'produces_firewood',
                        'produces_timber',
                        'owner',
                        'season',
                    ],
                ],
            ],
        ]);
    }

    public function testDeleteAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $response = $this->deleteJson('/api/tree_species/1', $headers);
        $response->assertStatus(200);
        $this->assertNull(TreeSpeciesModel::find(1));
    }
}
