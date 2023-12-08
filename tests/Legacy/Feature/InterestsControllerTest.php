<?php

namespace Tests\Legacy\Feature;

use Illuminate\Support\Facades\Auth;
use Tests\Legacy\LegacyTestCase;

final class InterestsControllerTest extends LegacyTestCase
{
    public function testCreateAction(): void
    {
        $data = [
            'initiator' => 'offer',
            'offer_id' => 3,
            'pitch_id' => 1,
        ];
        $token = Auth::attempt([
            'email_address' => 'andrew@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $response = $this->postJson('/api/interests', $data, $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'organisation_id',
                'offer_id',
                'pitch_id',
                'initiator',
                'matched',
                'created_at',
            ],
            'meta' => [],
        ]);
    }

    public function testReadAllByTypeAction(): void
    {
        $this->callReadAllActionAsInitiated();
        $this->callReadAllActionAsReceived();
    }

    public function callReadAllActionAsInitiated()
    {
        $token = Auth::attempt([
            'email_address' => 'andrew@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];
        $response = $this->getJson('/api/interests/initiated', $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                [
                    'id',
                    'organisation_id',
                    'offer_id',
                    'pitch_id',
                    'initiator',
                    'matched',
                    'created_at',
                ],
            ],
        ]);
        foreach ($response->json('data') as $interest) {
            $this->assertSame(2, $interest['organisation_id']);
        }
    }

    public function callReadAllActionAsReceived()
    {
        $token = Auth::attempt([
            'email_address' => 'andrew@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];
        $response = $this->getJson('/api/interests/received', $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                [
                    'id',
                    'organisation_id',
                    'offer_id',
                    'pitch_id',
                    'initiator',
                    'matched',
                    'created_at',
                ],
            ],
        ]);
        foreach ($response->json('data') as $interest) {
            $this->assertNotSame(2, $interest['organisation_id']);
        }
    }

    public function testDeleteAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'andrew@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];
        $response = $this->deleteJson('/api/interests/1', $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJson(['data' => []]);
    }

    public function testDeleteActionMonitoringExistsException(): void
    {
        $token = Auth::attempt([
            'email_address' => 'andrew@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];
        $response = $this->deleteJson('/api/interests/4', $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(422);
        $response->assertJson(['errors' => []]);
    }
}
