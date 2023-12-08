<?php

namespace Tests\Legacy\Feature;

use Illuminate\Support\Facades\Auth;
use Tests\Legacy\LegacyTestCase;

final class OrganisationFileControllerTest extends LegacyTestCase
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

    public function testReadByOrganisationAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'terrafund@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->getJson('/api/organisations/1/files', $headers)
        ->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonFragment([
            'id' => 1,
            'organisation_id' => 1,
            'type' => 'financial_statement',
        ]);
    }

    public function testCreateActionAsLetterOfReference(): void
    {
        $token = Auth::attempt([
            'email_address' => 'terrafund@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $uploadId = $this->uploadFile($token, $this->fakeFile());

        $this->postJson('/api/organisations/file', [
            'organisation_id' => 1,
            'upload' => $uploadId,
            'type' => 'letter_of_reference',
        ], $headers)
        ->assertStatus(201)
        ->assertJsonFragment([
            'organisation_id' => 1,
            'type' => 'letter_of_reference',
        ]);
    }

    public function testReadByOrganisationActionRequiresBeingInOrganisation(): void
    {
        $token = Auth::attempt([
            'email_address' => 'andrew@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->getJson('/api/organisations/1/files', $headers)
        ->assertStatus(403);
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

        $uploadId = $this->uploadFile($token, $this->fakeFile());

        $this->postJson('/api/organisations/file', [
            'organisation_id' => 1,
            'upload' => $uploadId,
            'type' => 'financial_statement',
        ], $headers)
        ->assertStatus(201)
        ->assertJsonFragment([
            'organisation_id' => 1,
            'type' => 'financial_statement',
        ]);
    }

    public function testCreateActionRequiresValidType(): void
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

        $this->postJson('/api/organisations/file', [
            'organisation_id' => 1,
            'upload' => $uploadId,
            'type' => 'not_a_valid_type',
        ], $headers)
        ->assertStatus(422);
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

        $this->postJson('/api/organisations/file', [
            'organisation_id' => 1,
            'upload' => $uploadId,
            'type' => 'financial_statement',
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

        $this->deleteJson('/api/organisations/file/1', $headers)
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

        $this->deleteJson('/api/organisations/file/1', $headers)
        ->assertStatus(403);
    }
}
