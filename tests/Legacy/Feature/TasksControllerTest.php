<?php

namespace Tests\Legacy\Feature;

use Illuminate\Support\Facades\Auth;
use Tests\Legacy\LegacyTestCase;

final class TasksControllerTest extends LegacyTestCase
{
    public function testReadAllOrganisationsAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'jane@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $response = $this->getJson('/api/tasks/organisations', $headers);
        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data');
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonFragment([
            'id' => 1,
        ]);
        $response->assertJsonFragment([
            'id' => 4,
        ]);
        $response->assertJsonStructure([
            'data' => [
                [
                    'id',
                    'name',
                ],
            ],
        ]);
    }

    public function testReadAllOrganisationsActionAsTerrafundAdmin(): void
    {
        $token = Auth::attempt([
            'email_address' => 'terrafund.admin@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $response = $this->getJson('/api/tasks/organisations', $headers);
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonMissing([
            'id' => 1,
        ]);
        $response->assertJsonFragment([
            'id' => 4,
        ]);
        $response->assertJsonStructure([
            'data' => [
                [
                    'id',
                    'name',
                ],
            ],
        ]);
    }

    public function testReadAllPitchesAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'jane@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $response = $this->getJson('/api/tasks/pitches', $headers);
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure([
            'data' => [
                [
                    'id',
                    'name',
                ],
            ],
        ]);
    }

    public function testReadAllMatchesAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'jane@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $response = $this->getJson('/api/tasks/matches', $headers);
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure(['data' => [
            [
                'id',
                'offer_id',
                'offer_name',
                'offer_interest_id',
                'offer_contacts' => [
                    [
                        'first_name',
                        'last_name',
                        'avatar',
                        'email_address',
                        'phone_number',
                    ],
                ],
                'pitch_id',
                'pitch_name',
                'pitch_interest_id',
                'pitch_contacts' => [
                    [
                        'first_name',
                        'last_name',
                        'avatar',
                        'email_address',
                        'phone_number',
                    ],
                ],
                'monitoring_id',
                'matched_at',
            ],
        ]]);
    }

    public function testReadAllMonitoringsAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'jane@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];
        $response = $this->getJson('/api/tasks/monitorings', $headers);
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure([
            'data' => [
                [
                    'id',
                    'match_id',
                    'initiator',
                    'stage',
                    'negotiating',
                    'created_by',
                    'created_at',
                    'pitch' => [
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
                        'problem',
                        'anticipated_outcome',
                        'who_is_involved',
                        'local_community_involvement',
                        'training_involved',
                        'training_type',
                        'training_amount_people',
                        'people_working_in',
                        'people_amount_nearby',
                        'people_amount_abroad',
                        'people_amount_employees',
                        'people_amount_volunteers',
                        'benefited_people',
                        'future_maintenance',
                        'use_of_resources',
                        'facebook',
                        'twitter',
                        'instagram',
                        'successful',
                        'visibility',
                    ],
                    'offer' => [
                        'id',
                        'organisation_id',
                        'name',
                        'description',
                        'land_types',
                        'land_ownerships',
                        'land_size',
                        'land_continent',
                        'land_country',
                        'restoration_methods',
                        'restoration_goals',
                        'funding_sources',
                        'funding_amount',
                        'funding_bracket',
                        'price_per_tree',
                        'long_term_engagement',
                        'reporting_frequency',
                        'reporting_level',
                        'sustainable_development_goals',
                        'cover_photo',
                        'video',
                        'created_at',
                        'successful',
                    ],
                    'updated_at',
                ],
            ],
        ]);
    }
}
