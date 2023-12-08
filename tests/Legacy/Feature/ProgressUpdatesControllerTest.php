<?php

namespace Tests\Legacy\Feature;

use Illuminate\Support\Facades\Auth;
use Tests\Legacy\LegacyTestCase;

final class ProgressUpdatesControllerTest extends LegacyTestCase
{
    public function testCreateAction(): void
    {
        $this->callCreateActionAsUser();
        $this->callCreateActionAsAdmin();
    }

    private function callCreateActionAsUser()
    {
        $data = [
            'upload' => $this->fakeImage(),
        ];
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];
        $response = $this->post('/api/uploads', $data, $headers);
        $id = json_decode($response->getContent())->data->id;
        $data = [
            'monitoring_id' => 2,
            'grouping' => 'general',
            'title' => 'Foo foo foo',
            'breakdown' => 'Bar bar bar',
            'summary' => 'Baz baz baz',
            'data' => [
                'planting_date' => '2021-01-01',
                'trees_planted' => [
                    [
                        'name' => 'maple',
                        'value' => 1,
                    ],
                    [
                        'name' => 'oak',
                        'value' => 2,
                    ],
                    [
                        'name' => 'sycamore',
                        'value' => 3,
                    ],
                ],
                'survival_rate' => 100,
                'supported_nurseries' => 123,
                'short_term_jobs_amount' => [
                    'male' => 1,
                    'female' => 2,
                ],
                'biodiversity_update' => 'Norf norf norf',
            ],
            'images' => [
                [
                    'image' => $id,
                    'caption' => 'Qux qux qux',
                ],
            ],
        ];
        $response = $this->postJson('/api/progress_updates', $data, $headers);
        $response->assertStatus(201);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure([
            'data' => [
                'id',
                'monitoring_id',
                'title',
                'breakdown',
                'summary',
                'data' => [
                    'planting_date',
                    'trees_planted' => [
                        [
                            'name',
                            'value',
                        ],
                    ],
                    'trees_planted_total',
                    'survival_rate',
                    'supported_nurseries',
                    'short_term_jobs_amount' => [
                        'male',
                        'female',
                    ],
                    'short_term_jobs_amount_total',
                    'biodiversity_update',
                ],
                'images' => [
                    [
                        'image',
                        'caption',
                        'thumbnail',
                    ],
                ],
                'created_by',
                'created_by_admin',
                'created_at',
            ],
        ]);
        $response->assertJson([
            'data' => [
                'created_by_admin' => false,
            ],
        ]);
    }

    private function callCreateActionAsAdmin()
    {
        $data = [
            'upload' => $this->fakeImage(),
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
            'grouping' => 'general',
            'title' => 'Foo foo foo',
            'breakdown' => 'Bar bar bar',
            'summary' => 'Baz baz baz',
            'data' => [
                'planting_date' => '2021-01-01',
                'trees_planted' => [
                    [
                        'name' => 'maple',
                        'value' => 1,
                    ],
                    [
                        'name' => 'oak',
                        'value' => 2,
                    ],
                    [
                        'name' => 'sycamore',
                        'value' => 3,
                    ],
                ],
                'survival_rate' => 100,
                'short_term_jobs_amount' => [
                    'male' => 1,
                    'female' => 2,
                ],
                'biodiversity_update' => 'Norf norf norf',
            ],
            'images' => [
                [
                    'image' => $id,
                    'caption' => 'Qux qux qux',
                ],
            ],
        ];
        $response = $this->postJson('/api/progress_updates', $data, $headers);
        $response->assertStatus(201);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure([
            'data' => [
                'id',
                'monitoring_id',
                'title',
                'breakdown',
                'summary',
                'data' => [
                    'planting_date',
                    'trees_planted' => [
                        [
                            'name',
                            'value',
                        ],
                    ],
                    'trees_planted_total',
                    'survival_rate',
                    'short_term_jobs_amount' => [
                        'male',
                        'female',
                    ],
                    'short_term_jobs_amount_total',
                    'biodiversity_update',
                ],
                'images' => [
                    [
                        'image',
                        'caption',
                        'thumbnail',
                    ],
                ],
                'created_by',
                'created_by_admin',
                'created_at',
            ],
        ]);
        $response->assertJson([
            'data' => [
                'created_by_admin' => true,
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
        $response = $this->getJson('/api/progress_updates/1', $headers);
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure([
            'data' => [
                'id',
                'monitoring_id',
                'title',
                'breakdown',
                'summary',
                'data' => [
                    'planting_date',
                    'trees_planted' => [
                        [
                            'name',
                            'value',
                        ],
                    ],
                    'trees_planted_total',
                    'survival_rate',
                    'short_term_jobs_amount' => [
                        'male',
                        'female',
                    ],
                    'short_term_jobs_amount_total',
                    'biodiversity_update',
                ],
                'images' => [
                    [
                        'image',
                        'caption',
                        'thumbnail',
                    ],
                ],
                'created_by',
                'created_at',
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
        $response = $this->getJson('/api/monitorings/2/progress_updates', $headers);
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure([
            'data' => [
                [
                    'id',
                    'monitoring_id',
                    'title',
                    'breakdown',
                    'summary',
                    'data' => [
                        'planting_date',
                        'trees_planted' => [
                            [
                                'name',
                                'value',
                            ],
                        ],
                        'trees_planted_total',
                        'survival_rate',
                        'short_term_jobs_amount' => [
                            'male',
                            'female',
                        ],
                        'short_term_jobs_amount_total',
                        'biodiversity_update',
                    ],
                    'images' => [
                        [
                            'image',
                            'caption',
                            'thumbnail',
                        ],
                    ],
                    'created_by',
                    'created_at',
                ],
            ],
        ]);
    }
}
