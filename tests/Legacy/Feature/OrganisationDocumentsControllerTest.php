<?php

namespace Tests\Legacy\Feature;

use App\Models\OrganisationDocument as OrganisationDocumentModel;
use Illuminate\Support\Facades\Auth;
use Tests\Legacy\LegacyTestCase;

final class OrganisationDocumentsControllerTest extends LegacyTestCase
{
    public function testCreateAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];
        $data = [
            'upload' => $this->fakeFile(),
        ];
        $response = $this->post('/api/uploads', $data, $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(201);
        $id = json_decode($response->getContent())->data->id;
        $data = [
            'name' => 'Example Award',
            'type' => 'award',
            'document' => $id,
        ];
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $response = $this->postJson('/api/organisation_documents', $data, $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'rejected_reason',
                'rejected_reason_body',
                'approved_rejected_by',
                'approved_rejected_at',
                'status',
                'data' => [
                    'id',
                    'organisation_id',
                    'name',
                    'type',
                    'document',
                ],
            ],
            'meta' => [],
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
        $response = $this->getJson('/api/organisation_documents/1', $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'organisation_id',
                'name',
                'type',
                'document',
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
            'name' => 'Example Award 2',
        ];
        $response = $this->patchJson('/api/organisation_documents/1', $data, $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'rejected_reason',
                'rejected_reason_body',
                'approved_rejected_by',
                'approved_rejected_at',
                'status',
                'data' => [
                    'id',
                    'organisation_id',
                    'name',
                    'type',
                    'document',
                ],
            ],
            'meta' => [],
        ]);
    }

    public function testReadAllByOrganisationAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];
        $response = $this->getJson('/api/organisations/1/organisation_documents', $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                [
                    'id',
                    'organisation_id',
                    'name',
                    'type',
                    'document',
                ],
            ],
        ]);
    }

    public function testInspectByOrganisationAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];
        $response = $this->getJson('/api/organisations/1/organisation_documents/inspect', $headers);
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
                        'organisation_id',
                        'name',
                        'type',
                        'document',
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
        $response = $this->deleteJson('/api/organisation_documents/1', $headers);
        $response->assertStatus(200);
        $this->assertNull(OrganisationDocumentModel::find(1));
    }
}
