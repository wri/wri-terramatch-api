<?php

namespace Tests\Legacy\Feature;

use Illuminate\Support\Facades\Auth;
use Tests\Legacy\LegacyTestCase;

final class PitchDocumentVersionsControllerTest extends LegacyTestCase
{
    public function testReadAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];
        $response = $this->getJson('/api/pitch_document_versions/1', $headers);
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
                    'id',
                    'pitch_id',
                    'name',
                    'type',
                    'document',
                ],
            ],
        ]);
    }

    public function testApproveAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'jane@example.com',
            'password' => 'Password123',
        ]);
        $data = [];
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $response = $this->patchJson('/api/pitch_document_versions/2/approve', $data, $headers);
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
                    'id',
                    'pitch_id',
                    'name',
                    'type',
                    'document',
                ],
            ],
        ]);
        $response->assertJson([
            'data' => [
                'status' => 'approved',
            ],
        ]);
    }

    public function testRejectAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'jane@example.com',
            'password' => 'Password123',
        ]);
        $data = [
            'rejected_reason' => 'cannot_verify',
            'rejected_reason_body' => 'Lorem ipsum dolor sit amet',
        ];
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $response = $this->patchJson('/api/pitch_document_versions/2/reject', $data, $headers);
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
                    'id',
                    'pitch_id',
                    'name',
                    'type',
                    'document',
                ],
            ],
        ]);
        $response->assertJson([
            'data' => [
                'status' => 'rejected',
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
        ];
        $response = $this->deleteJson('/api/pitch_document_versions/2', $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJson([
            'data' => [],
        ]);
    }

    public function testReadAllByPitchDocumentAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];
        $response = $this->getJson('/api/pitch_documents/1/pitch_document_versions', $headers);
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
                        'type',
                        'document',
                    ],
                ],
            ],
        ]);
    }

    public function testReviveAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'jane@example.com',
            'password' => 'Password123',
        ]);
        $data = [];
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $response = $this->patchJson('/api/pitch_document_versions/3/revive', $data, $headers);
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
                    'id',
                    'pitch_id',
                    'name',
                    'type',
                    'document',
                ],
            ],
        ]);
        $response->assertJson([
            'data' => [
                'status' => 'approved',
            ],
        ]);
    }
}
