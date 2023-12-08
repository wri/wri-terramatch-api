<?php

namespace Tests\Legacy\Feature;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Tests\Legacy\LegacyTestCase;

final class MonitoringsControllerTest extends LegacyTestCase
{
    public function testCreateAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'dominic@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];
        $data = [
            'visibility' => 'fully_invested_funded',
        ];
        $this->patchJson('/api/pitches/1/visibility', $data, $headers);
        $data = [
            'match_id' => 1,
        ];
        $response = $this->postJson('/api/monitorings', $data, $headers);
        $response->assertStatus(201);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure([
            'data' => [
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
        ]);
    }

    public function testReadAction(): void
    {
        $this->callReadActionAsUser();
        $this->callReadActionAsAdmin();
    }

    private function callReadActionAsUser()
    {
        $token = Auth::attempt([
            'email_address' => 'dominic@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];
        $response = $this->getJson('/api/monitorings/1', $headers);
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure([
            'data' => [
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
        ]);
    }

    private function callReadActionAsAdmin()
    {
        $token = Auth::attempt([
            'email_address' => 'jane@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];
        $response = $this->getJson('/api/monitorings/1', $headers);
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure([
            'data' => [
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
        ]);
    }

    public function testReadAllByOfferAction(): void
    {
        $this->callReadAllByOfferActionAsOwner();
        $this->callReadAllByOfferActionAsNotOwner();
    }

    private function callReadAllByOfferActionAsOwner()
    {
        $token = Auth::attempt([
            'email_address' => 'andrew@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];
        $response = $this->getJson('/api/offers/4/monitorings', $headers);
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

    private function callReadAllByOfferActionAsNotOwner()
    {
        $token = Auth::attempt([
            'email_address' => 'dominic@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];
        $response = $this->getJson('/api/offers/4/monitorings', $headers);
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

    public function testReadAllByPitchAction(): void
    {
        $this->callReadAllByPitchActionAsOwner();
        $this->callReadAllByPitchActionAsNotOwner();
    }

    private function callReadAllByPitchActionAsOwner()
    {
        $token = Auth::attempt([
            'email_address' => 'dominic@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];
        $response = $this->getJson('/api/pitches/2/monitorings', $headers);
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

    private function callReadAllByPitchActionAsNotOwner()
    {
        $token = Auth::attempt([
            'email_address' => 'andrew@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];
        $response = $this->getJson('/api/pitches/2/monitorings', $headers);
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

    public function testReadAllAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'dominic@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];
        $response = $this->getJson('/api/monitorings', $headers);
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

    public function testSummariseAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'dominic@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];
        $response = $this->getJson('/api/monitorings/2/summarise', $headers);
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure([
            'data' => [
                [
                    'attribute',
                    'target',
                    'progress_update',
                    'updated_at',
                ],
            ],
        ]);
        $response->assertJson([
            'data' => [
                0 => [
                    'attribute' => 'trees_planted',
                    'target' => 123,
                    'progress_update' => 9,
                ],
                2 => [
                    'attribute' => 'survival_rate',
                    'target' => 75,
                    'progress_update' => 100,
                ],
            ],
        ]);
    }

    public function testReadLandGeoJsonAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'jane@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];
        $response = $this->getJson('/api/monitorings/2/land_geojson', $headers);
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
        $response->assertHeader('Content-Disposition', 'attachment; filename="monitoring_2_land_geojson.geojson"');
        json_decode($response->getContent());
        $this->assertEquals(0, json_last_error());
        $response->assertSee('"type": "Polygon"', false);
    }
}
