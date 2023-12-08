<?php

namespace Tests\Legacy\Feature;

use App\Models\CarbonCertification as CarbonCertificationModel;
use Illuminate\Support\Facades\Auth;
use Tests\Legacy\LegacyTestCase;

final class CarbonCertificationsControllerTest extends LegacyTestCase
{
    public function testCreateAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $data = [
            'pitch_id' => 1,
            'type' => 'other',
            'other_value' => 'foo bar baz',
            'link' => 'http://www.example.com/carbon_certification.pdf',
        ];
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $response = $this->postJson('/api/carbon_certifications', $data, $headers);
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
                    'id',
                    'pitch_id',
                    'type',
                    'other_value',
                    'link',
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
        $response = $this->getJson('/api/carbon_certifications/1', $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'pitch_id',
                'type',
                'other_value',
                'link',
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
            'type' => 'plan_vivo',
            'other_value' => null,
        ];
        $response = $this->patchJson('/api/carbon_certifications/1', $data, $headers);
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
                    'type',
                    'other_value',
                    'link',
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
        $response = $this->getJson('/api/pitches/1/carbon_certifications', $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                [
                    'id',
                    'pitch_id',
                    'type',
                    'other_value',
                    'link',
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
        $response = $this->getJson('/api/pitches/1/carbon_certifications/inspect', $headers);
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
                        'type',
                        'other_value',
                        'link',
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
        $response = $this->deleteJson('/api/carbon_certifications/1', $headers);
        $response->assertStatus(200);
        $this->assertNull(CarbonCertificationModel::find(1));
    }
}
