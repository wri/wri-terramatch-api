<?php

namespace Tests\Legacy\Feature;

use Illuminate\Support\Facades\Auth;
use Tests\Legacy\LegacyTestCase;

final class OrganisationPhotoControllerTest extends LegacyTestCase
{
    private function uploadFile($token, $file)
    {
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $response = $this->post('/api/uploads', [
            'upload' => $file,
        ], $headers);

        return $response->json('data.id');
    }

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

        $uploadId = $this->uploadFile($token, $this->fakeImage());

        $this->postJson('/api/organisations/photo', [
            'organisation_id' => 1,
            'upload' => $uploadId,
            'is_public' => false,
        ], $headers)
        ->assertStatus(201)
        ->assertJsonFragment([
            'organisation_id' => 1,
            'is_public' => false,
        ]);
    }

    public function testCreateActionRequiresBeingInOrganisation(): void
    {
        $token = Auth::attempt([
            'email_address' => 'andrew@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $uploadId = $this->uploadFile($token, $this->fakeImage());

        $this->postJson('/api/organisations/photo', [
            'organisation_id' => 1,
            'upload' => $uploadId,
            'is_public' => false,
        ], $headers)
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

        $this->deleteJson('/api/organisations/photo/1', $headers)
        ->assertStatus(200);
    }

    public function testDeleteActionRequiresBeingInOrganisation(): void
    {
        $token = Auth::attempt([
            'email_address' => 'andrew@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->deleteJson('/api/organisations/photo/1', $headers)
        ->assertStatus(403);
    }
}
