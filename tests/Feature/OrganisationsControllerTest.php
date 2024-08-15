<?php

namespace Tests\Feature;

use App\Models\V2\User;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class OrganisationsControllerTest extends TestCase
{
    #[DataProvider('revenuesDataProvider')]
    public function testCreateActionRevenues(array $revenues): void
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
        $user = User::factory()->create([
            'organisation_id' => null,
        ]);
        $this->actingAs($user);
        $response = $this->postJson('/api/organisations', array_merge($data, $revenues));
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(201);
    }

    public static function revenuesDataProvider(): array
    {
        return [
            [['revenues_19' => 1000.00]],
            [['revenues_19' => 123456789.12]],
            [['revenues_19' => 123456789]],
            [['revenues_19' => 1.00]],
            [['revenues_19' => 0]],
            [['revenues_19' => 0.00]],
            [['revenues_19' => 123456789012]],
            [['revenues_19' => 123456789012.1]],
            [['revenues_19' => 123456789012.10]],
            [['revenues_19' => 123456789012]],
            [['revenues_19' => 999999999999.99]],
            [['revenues_19' => 999999999999]],
            [['revenues_19' => 999999999999.00]],
            [['revenues_19' => 999999999999.1]],
            [['revenues_20' => 1000.00]],
            [['revenues_20' => 123456789.12]],
            [['revenues_20' => 123456789]],
            [['revenues_20' => 1.00]],
            [['revenues_20' => 0]],
            [['revenues_20' => 0.00]],
            [['revenues_20' => 123456789012]],
            [['revenues_20' => 123456789012.1]],
            [['revenues_20' => 123456789012.10]],
            [['revenues_20' => 123456789012]],
            [['revenues_20' => 999999999999.99]],
            [['revenues_20' => 999999999999]],
            [['revenues_20' => 999999999999.00]],
            [['revenues_20' => 999999999999.1]],
            [['revenues_21' => 1000.00]],
            [['revenues_21' => 123456789.12]],
            [['revenues_21' => 123456789]],
            [['revenues_21' => 1.00]],
            [['revenues_21' => 0]],
            [['revenues_21' => 0.00]],
            [['revenues_21' => 123456789012]],
            [['revenues_21' => 123456789012.1]],
            [['revenues_21' => 123456789012.10]],
            [['revenues_21' => 123456789012]],
            [['revenues_21' => 999999999999.99]],
            [['revenues_21' => 999999999999]],
            [['revenues_21' => 999999999999.00]],
            [['revenues_21' => 999999999999.1]],
        ];
    }
}
