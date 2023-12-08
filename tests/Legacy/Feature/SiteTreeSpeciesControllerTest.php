<?php

namespace Tests\Legacy\Feature;

use Illuminate\Support\Facades\Auth;
use Tests\Legacy\LegacyTestCase;

final class SiteTreeSpeciesControllerTest extends LegacyTestCase
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
            'site_id' => 1,
            'name' => 'test tree species name',
            'amount' => 5,
        ];

        $response = $this->postJson('/api/site/1/tree_species/manual', $data, $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJsonStructure([
                'data' => [
                    'id',
                    'site_id',
                    'name',
                    'amount',
                    'created_at',
                ],
            ]);
    }

    public function testCreateBulkAction()
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
            'site_submission_id' => 1,
            'tree_species' => [
                [
                    'name' => 'tree 1',
                ], [
                    'name' => 'tree 2',
                ],
            ],
        ];

        $this->assertDatabaseCount('site_tree_species', 3); // 3 total

        $this->postJson('/api/site/1/tree_species/bulk', $data, $headers)
            ->assertStatus(200);

        $this->assertDatabaseCount('site_tree_species', 4); // 2 elsewhere, 1 cleared from this submission, plus 2 from this call
    }

    public function testDeleteAction()
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);

        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->deleteJson('/api/site/tree_species/1', $headers)
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

        $this->deleteJson('/api/site/tree_species/1', $headers)
        ->assertStatus(403);
    }

    public function testReadAllAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $response = $this->getJson('/api/sites/tree_species', $headers);
        $response->assertStatus(200);
    }

    public function testReadAllBySiteAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->getJson('/api/site/1/tree_species', $headers)
            ->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function testAdminReadAllBySiteAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'jane@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->getJson('/api/site/1/tree_species', $headers)
            ->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }
}
