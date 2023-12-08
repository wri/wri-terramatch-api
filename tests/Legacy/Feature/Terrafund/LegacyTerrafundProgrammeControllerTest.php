<?php

namespace Tests\Legacy\Feature\Terrafund;

use Illuminate\Support\Facades\Auth;
use Tests\Legacy\LegacyTestCase;

final class LegacyTerrafundProgrammeControllerTest extends LegacyTestCase
{
    private function programmeData($overrides = [])
    {
        return array_merge(
            [
                'name' => 'test name',
                'description' => 'test description',
                'planting_start_date' => '2000-01-01',
                'planting_end_date' => '2038-01-28',
                'budget' => 10000,
                'status' => 'existing_expansion',
                'home_country' => 'SE',
                'project_country' => 'AU',
                'boundary_geojson' => '{"type":"Polygon","coordinates":[[[-1.864006519317627,50.7219083651253],[-1.8627190589904783,50.7219083651253],[-1.8627190589904783,50.72276418262861],[-1.864006519317627,50.72276418262861],[-1.864006519317627,50.7219083651253]]]}',
                'history' => 'history',
                'objectives' => 'objectives',
                'environmental_goals' => 'environmental goals',
                'socioeconomic_goals' => 'socioeconomic goals',
                'sdgs_impacted' => 'SDGs impacted',
                'long_term_growth' => 'long term growth',
                'community_incentives' => 'community incentives',
                'total_hectares_restored' => 232323,
                'trees_planted' => 12,
                'jobs_created' => 100,
            ],
            $overrides
        );
    }

    public function testCreateAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'terrafund@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $response = $this->postJson(
            '/api/terrafund/programme',
            $this->programmeData(),
            $headers,
        )
        ->assertStatus(201)
        ->assertJsonFragment(
            $this->programmeData([
                'framework_id' => 2,
                'organisation_id' => 1,
            ])
        );

        $this->assertDatabaseHas('terrafund_programme_user', [
            'user_id' => 12,
            'terrafund_programme_id' => $response->json('data.id'),
        ]);
    }

    public function testCreateActionStatusMustBeValid(): void
    {
        $token = Auth::attempt([
            'email_address' => 'terrafund@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->postJson(
            '/api/terrafund/programme',
            $this->programmeData([
                'status' => 'not valid',
            ]),
            $headers,
        )
        ->assertStatus(422);
    }

    public function testCreateActionStartDateMustBeBeforeEndDate(): void
    {
        $token = Auth::attempt([
            'email_address' => 'terrafund@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->postJson(
            '/api/terrafund/programme',
            $this->programmeData([
                'planting_start_date' => '2000-01-01',
                'planting_end_date' => '1999-01-28',
            ]),
            $headers,
        )
        ->assertStatus(422);
    }

    public function testCreateActionRequiresBeingATerrafundUser(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->postJson(
            '/api/terrafund/programme',
            $this->programmeData(),
            $headers,
        )
        ->assertStatus(403);
    }

    public function testUpdateAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'terrafund@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->patchJson(
            '/api/terrafund/programme/1',
            $this->programmeData(),
            $headers,
        )
        ->assertStatus(200)
        ->assertJsonFragment(
            $this->programmeData([
                'id' => 1,
                'framework_id' => 2,
                'organisation_id' => 1,
            ])
        );
    }

    public function testUpdateActionRequiresAccess(): void
    {
        $token = Auth::attempt([
            'email_address' => 'joe@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->patchJson(
            '/api/terrafund/programme/1',
            $this->programmeData(),
            $headers,
        )
        ->assertStatus(403);
    }

    public function testReadAllAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'jane@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->getJson('/api/terrafund/programmes', $headers)
        ->assertStatus(200)
        ->assertJsonFragment([
            'id' => 1,
        ]);
    }

    public function testReadAllActionAsTerrafundAdmin(): void
    {
        $token = Auth::attempt([
            'email_address' => 'terrafund.admin@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->getJson('/api/terrafund/programmes', $headers)
        ->assertStatus(200)
        ->assertJsonFragment([
            'id' => 1,
        ]);
    }

    public function testReadAllActionRequiresBeingAnAdmin(): void
    {
        $token = Auth::attempt([
            'email_address' => 'joe@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->getJson('/api/terrafund/programmes', $headers)
        ->assertStatus(403);
    }

    public function testReadAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'terrafund@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->getJson('/api/terrafund/programme/1', $headers)
        ->assertStatus(200)
        ->assertJsonFragment([
            'id' => 1,
            'name' => 'Programme name',
            'description' => 'Programme description',
            'planting_start_date' => '2000-10-06',
            'planting_end_date' => '2998-04-24',
            'budget' => 12345,
            'status' => 'new_project',
            'home_country' => 'se',
            'project_country' => 'au',
            'boundary_geojson' => '{"type":"Polygon","coordinates":[[[-1.864006519317627,50.7219083651253],[-1.8627190589904783,50.7219083651253],[-1.8627190589904783,50.72276418262861],[-1.864006519317627,50.72276418262861],[-1.864006519317627,50.7219083651253]]]}',
            'history' => 'history',
            'objectives' => 'objectives',
            'environmental_goals' => 'environmental goals',
            'socioeconomic_goals' => 'socioeconomic goals',
            'sdgs_impacted' => 'SDGs impacted',
            'long_term_growth' => 'long term growth',
            'community_incentives' => 'community incentives',
            'total_hectares_restored' => 20000,
            'trees_planted' => 12,
            'jobs_created' => 100,
            'framework_id' => 2,
        ])
        ->assertJsonPath('data.tree_species.0.id', 1)
        ->assertJsonPath('data.tree_species.1.id', 2)
        ->assertJsonPath('data.additional_files.0.id', 1);
    }

    public function testReadActionUserMustBeInProgramme(): void
    {
        $token = Auth::attempt([
            'email_address' => 'sue@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->getJson('/api/terrafund/programme/1', $headers)
        ->assertStatus(403);
    }

    public function testReadAllPersonalAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'terrafund@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->getJson('/api/terrafund/programmes/personal', $headers)
            ->assertStatus(200)
            ->assertJsonFragment([
                'id' => 1,
                'name' => 'Programme name',
                'home_country' => 'se',
                'project_country' => 'au',
            ])
            ->assertJsonCount(1, 'data');
    }

    public function testReadAllPersonalActionWithOrganisationScoping(): void
    {
        $token = Auth::attempt([
            'email_address' => 'terrafund@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->getJson('/api/terrafund/programmes/personal?organisation_id=1', $headers)
            ->assertStatus(200)
            ->assertJsonFragment([
                'id' => 1,
                'name' => 'Programme name',
                'home_country' => 'se',
                'project_country' => 'au',
            ])
            ->assertJsonCount(1, 'data');

        $this->getJson('/api/terrafund/programmes/personal?organisation_id=2', $headers)
            ->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }

    public function testReadAllPartnersAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'terrafund@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->getJson('/api/terrafund/programme/1/partners', $headers)
        ->assertStatus(200)
        ->assertJsonPath('data.0.id', 12)
        ->assertJsonPath('data.1.id', 16);
    }

    public function testDeletePartnerActionRequiresAccessToProgramme(): void
    {
        $token = Auth::attempt([
            'email_address' => 'joe@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->deleteJson('/api/terrafund/programme/1/partners/16', $headers)
        ->assertStatus(403);
    }

    public function testDeletePartnerAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'terrafund@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->assertDatabaseHas('terrafund_programme_user', [
            'user_id' => 16,
            'terrafund_programme_id' => 1,
        ]);

        $this->deleteJson('/api/terrafund/programme/1/partners/16', $headers)
        ->assertStatus(200);

        $this->assertDatabaseMissing('terrafund_programme_user', [
            'user_id' => 16,
            'terrafund_programme_id' => 1,
        ]);
    }
}
