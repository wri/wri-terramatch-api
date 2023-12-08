<?php

namespace Tests\Legacy\Feature;

use Illuminate\Support\Facades\Auth;
use Tests\Legacy\LegacyTestCase;

final class SeedDetailControllerTest extends LegacyTestCase
{
    public function testCreateAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $data = [
            'name' => 'test name',
            'weight_of_sample' => 45.2001,
            'seeds_in_sample' => 63728,
        ];

        $this->postJson('/api/site/1/seeds', $data, $headers)
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(201)
            ->assertJsonFragment([
                'name' => 'test name',
                'weight_of_sample' => '45.2001',
                'seeds_in_sample' => 63728,
                'seeds_per_kg' => 1409.9084,
                'site_id' => 1,
            ]);
    }

    public function testCreateBulkAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $data = [
            'collection' => [
                [
                    'name' => 'unique name',
                    'weight_of_sample' => 10,
                    'seeds_in_sample' => 10,
                ], [
                    'name' => 'non-unique name',
                    'weight_of_sample' => 10,
                    'seeds_in_sample' => 10,
                ], [
                    'name' => 'non-unique name',
                    'weight_of_sample' => 10,
                    'seeds_in_sample' => 10,
                ],
            ],
        ];

        $this->postJson('/api/site/1/seeds/bulk', $data, $headers)
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(201);

        $this->assertDatabaseHas('seed_details', [
            'site_id' => 1,
            'name' => 'unique name',
            'weight_of_sample' => 10,
            'seeds_in_sample' => 10,
        ]);

        $this->assertDatabaseHas('seed_details', [
            'site_id' => 1,
            'name' => 'non-unique name',
            'weight_of_sample' => 20,
            'seeds_in_sample' => 20,
        ]);
    }
}
