<?php

namespace Tests\Legacy\Feature\Terrafund;

use Illuminate\Support\Facades\Auth;
use Tests\Legacy\LegacyTestCase;

final class TerrafundCsvImportControllerTest extends LegacyTestCase
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

        $response = $this->post('/api/uploads', [
            'upload' => $this->fakeValidCsv(),
        ], $headers);

        $this->postJson('/api/terrafund/tree_species/csv', [
            'treeable_type' => 'programme',
            'treeable_id' => 1,
            'upload_id' => $response->json('data.id'),
        ], $headers)
        ->assertStatus(200);
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

        $response = $this->post('/api/uploads', [
            'upload' => $this->fakeValidCsv(),
        ], $headers);

        $this->postJson('/api/terrafund/tree_species/csv', [
            'treeable_type' => 'nursery',
            'treeable_id' => 1,
            'upload_id' => $response->json('data.id'),
        ], $headers)
        ->assertStatus(200);
    }

    public function testCreateActionFileRequiresHeaders(): void
    {
        $token = Auth::attempt([
            'email_address' => 'terrafund@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $response = $this->post('/api/uploads', [
            'upload' => $this->fakeNoHeadersCsv(),
        ], $headers);

        $this->postJson('/api/terrafund/tree_species/csv', [
            'treeable_type' => 'programme',
            'treeable_id' => 1,
            'upload_id' => $response->json('data.id'),
        ], $headers)
        ->assertStatus(422);
    }

    public function testCreateActionFileIsRequired(): void
    {
        $token = Auth::attempt([
            'email_address' => 'terrafund@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->postJson('/api/terrafund/tree_species/csv', [
            'treeable_type' => 'programme',
            'treeable_id' => 1,
        ], $headers)
        ->assertStatus(422);
    }

    public function testCreateActionRequiresBelongingToProgramme(): void
    {
        $token = Auth::attempt([
            'email_address' => 'jane@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $response = $this->post('/api/uploads', [
            'upload' => $this->fakeValidCsv(),
        ], $headers);

        $this->postJson('/api/terrafund/tree_species/csv', [
            'treeable_type' => 'programme',
            'treeable_id' => 1,
            'upload_id' => $response->json('data.id'),
        ], $headers)
        ->assertStatus(200);
    }
}
