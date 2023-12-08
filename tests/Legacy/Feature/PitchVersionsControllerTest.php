<?php

namespace Tests\Legacy\Feature;

use Illuminate\Support\Facades\Auth;
use Tests\Legacy\LegacyTestCase;

final class PitchVersionsControllerTest extends LegacyTestCase
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
        $response = $this->getJson('/api/pitch_versions/1', $headers);
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
                    'organisation_id',
                    'name',
                    'description',
                    'land_types',
                    'land_ownerships',
                    'land_size',
                    'land_continent',
                    'land_country',
                    'land_geojson',
                    'restoration_methods',
                    'restoration_goals',
                    'funding_sources',
                    'funding_amount',
                    'funding_bracket',
                    'revenue_drivers',
                    'estimated_timespan',
                    'long_term_engagement',
                    'reporting_frequency',
                    'reporting_level',
                    'sustainable_development_goals',
                    'avatar',
                    'cover_photo',
                    'video',
                    'successful',
                    'visibility',
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
        $response = $this->patchJson('/api/pitch_versions/2/approve', $data, $headers);
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
                    'organisation_id',
                    'name',
                    'description',
                    'land_types',
                    'land_ownerships',
                    'land_size',
                    'land_continent',
                    'land_country',
                    'land_geojson',
                    'restoration_methods',
                    'restoration_goals',
                    'funding_sources',
                    'funding_amount',
                    'revenue_drivers',
                    'estimated_timespan',
                    'long_term_engagement',
                    'reporting_frequency',
                    'reporting_level',
                    'sustainable_development_goals',
                    'avatar',
                    'cover_photo',
                    'video',
                    'successful',
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
        $response = $this->patchJson('/api/pitch_versions/2/reject', $data, $headers);
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
                    'organisation_id',
                    'name',
                    'description',
                    'land_types',
                    'land_ownerships',
                    'land_size',
                    'land_continent',
                    'land_country',
                    'land_geojson',
                    'restoration_methods',
                    'restoration_goals',
                    'funding_sources',
                    'funding_amount',
                    'revenue_drivers',
                    'estimated_timespan',
                    'long_term_engagement',
                    'reporting_frequency',
                    'reporting_level',
                    'sustainable_development_goals',
                    'avatar',
                    'cover_photo',
                    'video',
                    'successful',
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
        $response = $this->deleteJson('/api/pitch_versions/2', $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJson([
            'data' => [],
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
        $response = $this->getJson('/api/pitches/1/pitch_versions', $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                [
                    'id',
                    'status',
                    'approved_rejected_by',
                    'rejected_reason',
                    'rejected_reason_body',
                    'data' => [
                        'id',
                        'organisation_id',
                        'name',
                        'description',
                        'land_types',
                        'land_ownerships',
                        'land_size',
                        'land_continent',
                        'land_country',
                        'land_geojson',
                        'restoration_methods',
                        'restoration_goals',
                        'funding_sources',
                        'funding_amount',
                        'revenue_drivers',
                        'estimated_timespan',
                        'long_term_engagement',
                        'reporting_frequency',
                        'reporting_level',
                        'sustainable_development_goals',
                        'avatar',
                        'cover_photo',
                        'video',
                        'successful',
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
        $response = $this->patchJson('/api/pitch_versions/3/revive', $data, $headers);
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
                    'organisation_id',
                    'name',
                    'description',
                    'land_types',
                    'land_ownerships',
                    'land_size',
                    'land_continent',
                    'land_country',
                    'land_geojson',
                    'restoration_methods',
                    'restoration_goals',
                    'funding_sources',
                    'funding_amount',
                    'revenue_drivers',
                    'estimated_timespan',
                    'long_term_engagement',
                    'reporting_frequency',
                    'reporting_level',
                    'sustainable_development_goals',
                    'avatar',
                    'cover_photo',
                    'video',
                    'successful',
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
