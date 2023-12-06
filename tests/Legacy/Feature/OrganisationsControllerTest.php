<?php

namespace Tests\Legacy\Feature;

use Illuminate\Support\Facades\Auth;
use Tests\Legacy\LegacyTestCase;

final class OrganisationsControllerTest extends LegacyTestCase
{
    public function testCreateAction(): void
    {
        $data = [
            'name' => 'Acme Corporation',
            'description' => 'Lorem ipsum dolor sit amet',
            'address_1' => '1 Foo Road',
            'address_2' => null,
            'city' => 'Bar Town',
            'state' => 'Baz State',
            'zip_code' => 'Qux',
            'country' => 'GB',
            'phone_number' => '0123456789',
            'full_time_permanent_employees' => '200',
            'seasonal_employees' => '30',
            'part_time_permanent_employees' => '100',
            'percentage_female' => '50',
            'website' => 'http://www.example.com',
            'key_contact' => 'K. Contact',
            'type' => 'other',
            'account_type' => 'ppc',
            'category' => 'both',
            'facebook' => null,
            'twitter' => null,
            'linkedin' => null,
            'instagram' => null,
            'avatar' => null,
            'cover_photo' => null,
            'video' => null,
            'founded_at' => '2000-01-01',
            'revenues_19' => null,
            'revenues_20' => null,
            'revenues_21' => null,
            'community_engagement_strategy' => 'strategy',
            'three_year_community_engagement' => 'engagement',
            'women_farmer_engagement' => 57,
            'young_people_engagement' => 89,
            'monitoring_and_evaluation_experience' => 'experience',
            'community_follow_up' => 'follow up',
            'total_hectares_restored' => 1010,
            'hectares_restored_three_years' => 4321,
            'total_trees_grown' => 10000,
            'tree_survival_rate' => 90,
            'tree_maintenance_and_aftercare' => 'some maintenance',
        ];
        $token = Auth::attempt([
            'email_address' => 'joe@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $response = $this->postJson('/api/organisations', $data, $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'rejected_reason',
                'rejected_reason_body',
                'approved_rejected_by',
                'approved_rejected_at',
                'status',
                'data' => [
                    'id',
                    'name',
                    'description',
                    'address_1',
                    'address_2',
                    'city',
                    'state',
                    'zip_code',
                    'country',
                    'phone_number',
                    'website',
                    'key_contact',
                    'type',
                    'category',
                    'facebook',
                    'twitter',
                    'linkedin',
                    'instagram',
                    'cover_photo',
                    'avatar',
                    'video',
                    'founded_at',
                    'community_engagement_strategy',
                    'three_year_community_engagement',
                    'women_farmer_engagement',
                    'young_people_engagement',
                    'monitoring_and_evaluation_experience',
                    'community_follow_up',
                    'total_hectares_restored',
                    'total_trees_grown',
                    'tree_survival_rate',
                    'hectares_restored_three_years',
                    'tree_maintenance_and_aftercare',
                    'founded_at',
                    'revenues_19',
                    'revenues_20',
                    'revenues_21',
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
        $response = $this->getJson('/api/organisations/2', $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'description',
                'city',
                'state',
                'country',
                'website',
                'key_contact',
                'type',
                'category',
                'facebook',
                'twitter',
                'linkedin',
                'instagram',
                'cover_photo',
                'avatar',
                'video',
                'founded_at',
                'community_engagement_strategy',
                'three_year_community_engagement',
                'women_farmer_engagement',
                'young_people_engagement',
                'monitoring_and_evaluation_experience',
                'community_follow_up',
                'total_hectares_restored',
                'total_trees_grown',
                'tree_survival_rate',
                'hectares_restored_three_years',
                'tree_maintenance_and_aftercare',
            ],
        ]);
    }

    public function testInspectAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];

        $this->getJson('/api/organisations/1/inspect', $headers)
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'description',
                    'address_1',
                    'address_2',
                    'city',
                    'state',
                    'zip_code',
                    'country',
                    'phone_number',
                    'website',
                    'key_contact',
                    'type',
                    'category',
                    'facebook',
                    'twitter',
                    'linkedin',
                    'instagram',
                    'cover_photo',
                    'avatar',
                    'video',
                    'founded_at',
                    'community_engagement_strategy',
                    'three_year_community_engagement',
                    'women_farmer_engagement',
                    'young_people_engagement',
                    'monitoring_and_evaluation_experience',
                    'community_follow_up',
                    'total_hectares_restored',
                    'total_trees_grown',
                    'tree_survival_rate',
                    'hectares_restored_three_years',
                    'tree_maintenance_and_aftercare',
                ],
            ]);
    }

    public function testUpdateAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];
        $data = [
            'name' => 'Acme Corporation 3',
            'phone_number' => '+44123456789',
            'website' => 'https://www.example.com',
            'key_contact' => 'Key C',
        ];

        $this->patchJson('/api/organisations/1', $data, $headers)
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'status',
                    'rejected_reason',
                    'rejected_reason_body',
                    'approved_rejected_by',
                    'data' => [
                        'id',
                        'name',
                        'description',
                        'address_1',
                        'address_2',
                        'city',
                        'state',
                        'zip_code',
                        'country',
                        'phone_number',
                        'website',
                        'key_contact',
                        'type',
                        'category',
                        'facebook',
                        'twitter',
                        'linkedin',
                        'instagram',
                        'cover_photo',
                        'avatar',
                        'video',
                        'founded_at',
                        'community_engagement_strategy',
                        'three_year_community_engagement',
                        'women_farmer_engagement',
                        'young_people_engagement',
                        'monitoring_and_evaluation_experience',
                        'community_follow_up',
                        'total_hectares_restored',
                        'total_trees_grown',
                        'tree_survival_rate',
                        'hectares_restored_three_years',
                        'tree_maintenance_and_aftercare',
                    ],
                ],
            ]);
    }

    public function testReadAllAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'jane@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];
        $response = $this->getJson('/api/organisations', $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                [
                    'id',
                    'name',
                ],
            ],
        ]);
    }
}
