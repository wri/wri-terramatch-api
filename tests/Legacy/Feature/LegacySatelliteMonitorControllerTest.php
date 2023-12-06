<?php

namespace Tests\Legacy\Feature;

use App\Models\Programme;
use Illuminate\Support\Facades\Auth;
use Tests\Legacy\LegacyTestCase;

final class LegacySatelliteMonitorControllerTest extends LegacyTestCase
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
            'satellite_monitorable_type' => Programme::class,
            'satellite_monitorable_id' => 1,
            'map' => $id,
            'alt_text' => 'Lorem ipsum dolor sit amet',
        ];
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $response = $this->postJson('/api/satellite_monitor', $data, $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'satellite_monitorable_type',
                'satellite_monitorable_id',
                'map',
                'alt_text',
                'created_at',
            ],
        ]);
        $response->assertJson([
            'data' => [
                'map' => null,
            ],
        ]);
    }

    public function testCreateActionUsingStringAsModel(): void
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
            'satellite_monitorable_type' => 'site',
            'satellite_monitorable_id' => 1,
            'map' => $id,
            'alt_text' => 'Lorem ipsum dolor sit amet',
        ];
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $response = $this->postJson('/api/satellite_monitor', $data, $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'satellite_monitorable_type',
                'satellite_monitorable_id',
                'map',
                'alt_text',
                'created_at',
            ],
        ]);
        $response->assertJson([
            'data' => [
                'map' => null,
            ],
        ]);
    }

    public function testReadByProgrammeAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $response = $this->getJson('/api/satellite_monitor/programme/1', $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => 3,
            'satellite_monitorable_type' => \App\Models\Programme::class,
            'satellite_monitorable_id' => 1,
        ]);
        $response->assertJsonFragment([
            'id' => 3,
            'satellite_monitorable_type' => \App\Models\Programme::class,
            'satellite_monitorable_id' => 1,
        ]);
    }

    public function testReadLatestByProgrammeAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'jane@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $response = $this->getJson('/api/satellite_monitor/programme/1/latest', $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => 3,
            'satellite_monitorable_type' => \App\Models\Programme::class,
            'satellite_monitorable_id' => 1,
        ]);
    }

    public function testReadBySiteAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'jane@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $response = $this->getJson('/api/satellite_monitor/site/1', $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => 2,
            'satellite_monitorable_type' => \App\Models\Site::class,
            'satellite_monitorable_id' => 1,
        ]);
        $response->assertJsonFragment([
            'id' => 4,
            'satellite_monitorable_type' => \App\Models\Site::class,
            'satellite_monitorable_id' => 1,
        ]);
    }

    public function testReadLatestBySiteAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'jane@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $response = $this->getJson('/api/satellite_monitor/site/1/latest', $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => 4,
            'satellite_monitorable_type' => \App\Models\Site::class,
            'satellite_monitorable_id' => 1,
        ]);
    }
}
