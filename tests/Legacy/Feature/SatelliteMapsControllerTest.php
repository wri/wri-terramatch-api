<?php

namespace Tests\Legacy\Feature;

use Illuminate\Support\Facades\Auth;
use Tests\Legacy\LegacyTestCase;

final class SatelliteMapsControllerTest extends LegacyTestCase
{
    public function testCreateAction(): void
    {
        $data = [
            'upload' => $this->fakeMap(),
        ];
        $token = Auth::attempt([
            'email_address' => 'jane@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];
        $response = $this->post('/api/uploads', $data, $headers);
        $id = json_decode($response->getContent())->data->id;
        $data = [
            'monitoring_id' => 2,
            'map' => $id,
            'alt_text' => 'Lorem ipsum dolor sit amet',
        ];
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $response = $this->postJson('/api/satellite_maps', $data, $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'monitoring_id',
                'map',
                'alt_text',
                'created_at',
                'created_by',
            ],
        ]);
        $response->assertJson([
            'data' => [
                'map' => null,
            ],
        ]);
    }

    public function testReadAllByMonitoringAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $response = $this->getJson('/api/monitorings/2/satellite_maps', $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                [
                    'id',
                    'monitoring_id',
                    'map',
                    'alt_text',
                    'created_at',
                    'created_by',
                ],
            ],
        ]);
    }

    public function testReadLatestByMonitoringAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $response = $this->getJson('/api/monitorings/2/satellite_maps/latest', $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'monitoring_id',
                'map',
                'alt_text',
                'created_at',
                'created_by',
            ],
        ]);
        $map = json_decode($response->getContent())->data->map;
        $this->assertIsString($map);
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
        $response = $this->getJson('/api/satellite_maps/1', $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'monitoring_id',
                'map',
                'alt_text',
                'created_at',
                'created_by',
            ],
        ]);
        $map = json_decode($response->getContent())->data->map;
        $this->assertIsString($map);
    }
}
