<?php

namespace Tests\Legacy\Feature;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Tests\Legacy\LegacyTestCase;

final class DevicesControllerTest extends LegacyTestCase
{
    public function testCreateAction(): void
    {
        $data = [
            'os' => 'ios',
            'uuid' => Str::random(16),
            'push_token' => Str::random(16),
        ];
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $response = $this->postJson('/api/devices', $data, $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'user_id',
                'os',
                'uuid',
                'push_token',
                'created_at',
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
        $response = $this->getJson('/api/devices/1', $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'user_id',
                'os',
                'uuid',
                'push_token',
                'created_at',
            ],
            'meta' => [],
        ]);
    }

    public function testReadAllAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];
        $response = $this->getJson('/api/devices', $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                [
                    'id',
                    'user_id',
                    'os',
                    'uuid',
                    'push_token',
                    'created_at',
                ],
            ],
            'meta' => [],
        ]);
    }

    public function testUpdateAction(): void
    {
        $data = [
            'push_token' => Str::random(16),
        ];
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $response = $this->patchJson('/api/devices/1', $data, $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'user_id',
                'os',
                'uuid',
                'push_token',
                'created_at',
            ],
            'meta' => [],
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
        $response = $this->deleteJson('/api/devices/1', $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => []]);
    }
}
