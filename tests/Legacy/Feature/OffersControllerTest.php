<?php

namespace Tests\Legacy\Feature;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Queue;
use Tests\Legacy\LegacyTestCase;

final class OffersControllerTest extends LegacyTestCase
{
    public function testCreateAction(): void
    {
        $data = [
            'name' => 'Example Offer',
            'description' => 'Lorem ipsum dolor sit amet',
            'land_types' => ['cropland', 'mangrove'],
            'land_ownerships' => ['private'],
            'land_size' => 'lt_10',
            'land_continent' => 'europe',
            'land_country' => null,
            'restoration_methods' => ['agroforestry', 'riparian_buffers'],
            'restoration_goals' => ['agriculture_and_commodities'],
            'funding_sources' => ['equity_investment', 'loan_debt', 'grant_with_reporting'],
            'funding_amount' => 1000000,
            'funding_bracket' => 'gt_1m',
            'price_per_tree' => 1.5,
            'long_term_engagement' => false,
            'reporting_frequency' => 'gt_quarterly',
            'reporting_level' => 'low',
            'sustainable_development_goals' => ['goal_1', 'goal_7', 'goal_9', 'goal_13'],
            'cover_photo' => null,
            'video' => null,
        ];
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $response = $this->postJson('/api/offers', $data, $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(201);
        $response->assertJsonStructure([
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
            'meta' => [],
        ]);
    }

    public function testReadAllByOrganisationAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'andrew@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];
        $response = $this->getJson('/api/organisations/1/offers', $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                [
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
        $response = $this->getJson('/api/offers/1', $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJsonStructure([
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
                'restoration_methods',
                'restoration_goals',
                'funding_sources',
                'funding_amount',
                'price_per_tree',
                'long_term_engagement',
                'reporting_frequency',
                'reporting_level',
                'sustainable_development_goals',
                'cover_photo',
                'video',
                'created_at',
                'successful',
                'visibility',
            ],
            'meta' => [],
        ]);
    }

    public function testUpdateAction(): void
    {
        $data = [
            'name' => 'Bar',
        ];
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $response = $this->patchJson('/api/offers/1', $data, $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJsonStructure([
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
                'restoration_methods',
                'restoration_goals',
                'funding_sources',
                'funding_amount',
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
            'meta' => [],
        ]);
        $response->assertJson([
            'data' => [
                'name' => 'Bar',
            ],
        ]);
    }

    public function testSearchAction(): void
    {
        Queue::fake();
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];
        $data = [
            'filters' => [
                [
                    'attribute' => 'land_types',
                    'operator' => 'contains',
                    'value' => [
                        'mangrove',
                        'cropland',
                    ],
                ],
                [
                    'attribute' => 'funding_bracket',
                    'operator' => 'in',
                    'value' => [
                        'lt_50k',
                    ],
                ],
            ],
            'sortDirection' => 'desc',
            'page' => 1,
        ];
        $response = $this->postJson('/api/offers/search', $data, $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                [
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
                    'price_per_tree',
                    'long_term_engagement',
                    'reporting_frequency',
                    'reporting_level',
                    'sustainable_development_goals',
                    'cover_photo',
                    'video',
                    'created_at',
                    'compatibility_score',
                    'successful',
                ],
            ],
            'meta' => [],
        ]);
    }

    public function testInspectByOrganisationAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];
        $response = $this->getJson('/api/organisations/1/offers/inspect', $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                [
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
            ],
            'meta' => [],
        ]);
    }

    public function testMostRecentAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'andrew@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];

        $response = $this->getJson('/api/offers/most_recent', $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                [
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
            ],
            'meta' => [
                'count',
            ],
        ]);
    }

    public function testUpdateVisibilityAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $data = [
            'visibility' => 'talking',
        ];
        $response = $this->patchJson('/api/offers/1/visibility', $data, $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJsonStructure([
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
                'restoration_methods',
                'restoration_goals',
                'funding_sources',
                'funding_amount',
                'price_per_tree',
                'long_term_engagement',
                'reporting_frequency',
                'reporting_level',
                'sustainable_development_goals',
                'cover_photo',
                'video',
                'created_at',
                'successful',
                'visibility',
            ],
            'meta' => [],
        ]);
        $response->assertJson([
            'data' => [
                'visibility' => 'talking',
            ],
        ]);
    }

    public function testUpdateVisibilityActionMonitoringExistsException(): void
    {
        $token = Auth::attempt([
            'email_address' => 'andrew@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $data = [
            'visibility' => 'talking',
        ];
        $response = $this->patchJson('/api/offers/4/visibility', $data, $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(422);
        $response->assertJsonStructure([
            'errors' => [],
        ]);
    }
}
