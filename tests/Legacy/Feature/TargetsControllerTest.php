<?php

namespace Tests\Legacy\Feature;

use Illuminate\Support\Facades\Auth;
use Tests\Legacy\LegacyTestCase;

final class TargetsControllerTest extends LegacyTestCase
{
    public function testCreateAction(): void
    {
        $data = [
            'monitoring_id' => 1,
            'start_date' => '2020-01-01',
            'finish_date' => '2021-01-01',
            'funding_amount' => 1000000,
            'land_geojson' => '{"type":"Polygon","coordinates":[[[-1.864006519317627,50.7219083651253],[-1.8627190589904783,50.7219083651253],[-1.8627190589904783,50.72276418262861],[-1.864006519317627,50.72276418262861],[-1.864006519317627,50.7219083651253]]]}',
            'data' => [
                'trees_planted' => 123,
                'non_trees_planted' => 456,
                'survival_rate' => 75,
                'land_size_planted' => 7.5,
                'land_size_restored' => 7.5,
            ],
        ];
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];
        $response = $this->postJson('/api/targets', $data, $headers);
        $response->assertStatus(201);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure([
            'data' => [
                'id',
                'monitoring_id',
                'negotiator',
                'start_date',
                'finish_date',
                'funding_amount',
                'land_geojson',
                'data' => [
                    'trees_planted',
                    'non_trees_planted',
                    'survival_rate',
                    'land_size_planted',
                    'land_size_restored',
                ],
                'created_at',
                'created_by',
                'updated_at',
                'accepted_at',
                'accepted_by',
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
        $response = $this->getJson('/api/targets/1', $headers);
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure([
            'data' => [
                'id',
                'monitoring_id',
                'negotiator',
                'start_date',
                'finish_date',
                'funding_amount',
                'land_geojson',
                'data' => [
                    'trees_planted',
                    'non_trees_planted',
                    'survival_rate',
                    'land_size_planted',
                    'land_size_restored',
                ],
                'created_at',
                'created_by',
                'updated_at',
                'accepted_at',
                'accepted_by',
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
        ];
        $response = $this->getJson('/api/monitorings/1/targets', $headers);
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure([
            'data' => [
                [
                    'id',
                    'monitoring_id',
                    'negotiator',
                    'start_date',
                    'finish_date',
                    'funding_amount',
                    'land_geojson',
                    'data' => [
                        'trees_planted',
                        'non_trees_planted',
                        'survival_rate',
                        'land_size_planted',
                        'land_size_restored',
                    ],
                    'created_at',
                    'created_by',
                    'updated_at',
                    'accepted_at',
                    'accepted_by',
                ],
            ],
        ]);
    }

    public function testAcceptAction(): void
    {
        $this->callAcceptActionAsValidUser();
        $this->callAcceptActionAsInvalidUser();
    }

    public function callAcceptActionAsValidUser()
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];
        $response = $this->patchJson('/api/targets/2/accept', $headers);
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure([
            'data' => [
                'id',
                'monitoring_id',
                'negotiator',
                'start_date',
                'finish_date',
                'funding_amount',
                'land_geojson',
                'data' => [
                    'trees_planted',
                    'non_trees_planted',
                    'survival_rate',
                    'land_size_planted',
                    'land_size_restored',
                ],
                'created_at',
                'created_by',
                'updated_at',
                'accepted_at',
                'accepted_by',
            ],
        ]);
        $response = $this->getJson('/api/monitorings/1', $headers);
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJson([
            'data' => [
                'stage' => 'accepted_targets',
            ],
        ]);
    }

    public function callAcceptActionAsInvalidUser()
    {
        $token = Auth::attempt([
            'email_address' => 'andrew@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];
        $response = $this->patchJson('/api/targets/2/accept', $headers);
        $response->assertStatus(403);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure([
            'errors' => [],
        ]);
    }

    public function testReadAcceptedByMonitoringAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];
        $this->patchJson('/api/targets/2/accept', $headers);
        $response = $this->getJson('/api/monitorings/1/targets/accepted', $headers);
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure([
            'data' => [
                'id',
                'monitoring_id',
                'negotiator',
                'start_date',
                'finish_date',
                'funding_amount',
                'land_geojson',
                'data' => [
                    'trees_planted',
                    'non_trees_planted',
                    'survival_rate',
                    'land_size_planted',
                    'land_size_restored',
                ],
                'created_at',
                'created_by',
                'updated_at',
                'accepted_at',
                'accepted_by',
            ],
        ]);
    }
}
