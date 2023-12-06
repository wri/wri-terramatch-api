<?php

namespace Tests\Legacy\Feature;

use Illuminate\Support\Facades\Auth;
use Tests\Legacy\LegacyTestCase;

final class SiteTreeSpeciesCsvControllerTest extends LegacyTestCase
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

        $response = $this->post('/api/uploads', [
            'upload' => $this->fakeValidCsv(),
        ], $headers);


        $this->postJson('/api/site/1/tree_species/csv', [
            'upload_id' => $response->json('data.id'),
        ], $headers)
        ->assertStatus(200);
    }

    public function testCreateActionFileRequiresHeaders(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $response = $this->post('/api/uploads', [
            'upload' => $this->fakeNoHeadersCsv(),
        ], $headers);

        $this->postJson('/api/site/1/tree_species/csv', [
            'upload_id' => $response->json('data.id'),
        ], $headers)
        ->assertStatus(422);
    }

    public function testCreateActionFileIsRequired(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->postJson('/api/site/1/tree_species/csv', $headers)
        ->assertStatus(422);
    }

    public function testCreateActionRequiresBelongingToSiteProgramme(): void
    {
        $token = Auth::attempt([
            'email_address' => 'sue@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->postJson('/api/site/1/tree_species/csv', $headers)
        ->assertStatus(403);
    }

    public function testReadAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $this->getJson('/api/site/tree_species/csv/1', $headers)
            ->assertStatus(200)
            ->assertJsonFragment([
                'id' => 1,
                'site_id' => 1,
                'total_rows' => 10,
                'has_failed' => false,
            ]);
    }

    public function testReadTreeSpeciesAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->getJson('/api/site/tree_species/csv/1/trees', $headers)
            ->assertStatus(200)
            ->assertJsonFragment([
                'id' => 2,
                'name' => 'A tree species',
                'site_id' => 1,
                'amount' => 500,
            ]);
    }

    public function testDownloadCsvTemplateAction(): void
    {
        $headers = [
            'Content-Type' => 'application/json',
        ];

        $this->getJson('/api/site/tree_species/template/csv', $headers)
            ->assertStatus(200);
    }
}
