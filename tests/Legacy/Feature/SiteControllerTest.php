<?php

namespace Tests\Legacy\Feature;

use App\Models\Programme;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Queue;
use Tests\Legacy\LegacyTestCase;

final class SiteControllerTest extends LegacyTestCase
{
    public function testCreateAction(): void
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');
        $data = [
            'programme_id' => 1,
            'site_name' => 'test site',
            'site_description' => 'test site desc',
            'site_history' => 'test site history',
            'end_date' => '2023-10-06',
            'planting_pattern' => 'some planting pattern',
            'stratification_for_heterogeneity' => 16,
        ];

        $this->postJson('/api/site', $data, $headers)
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(201)
            ->assertJsonFragment([
                'continent' => 'europe',
                'country' => 'se',
                'end_date' => '2023-10-06',
                'control_site' => false,
            ]);
    }

    public function testCreateControlSiteAction(): void
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');
        $data = [
            'programme_id' => 1,
            'control_site' => true,
            'site_name' => 'test control site',
            'site_description' => 'test control site desc',
            'site_history' => 'test site history',
            'end_date' => '2023-10-06',
            'planting_pattern' => 'some planting pattern',
            'stratification_for_heterogeneity' => 16,
        ];

        $this->postJson('/api/site', $data, $headers)
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(201)
            ->assertJsonFragment([
                'continent' => 'europe',
                'country' => 'se',
                'end_date' => '2023-10-06',
                'control_site' => true,
            ]);
    }

    public function testControlSiteRelations(): void
    {
        $programme = Programme::find(1);
        $this->assertCount(1, $programme->controlSites);
        $this->assertCount(7, $programme->sites);
    }

    public function testCreateActionUserMustBeInProgramme(): void
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');
        $data = [
            'programme_id' => 4,
            'site_name' => 'test site',
            'site_description' => 'test site desc',
            'site_history' => 'test site history',
            'end_date' => '2023-10-06',
            'planting_pattern' => 'some planting pattern',
            'stratification_for_heterogeneity' => 16,
        ];

        $this->postJson('/api/site', $data, $headers)
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(403);
    }

    public function testUpdateAction(): void
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');
        $data = [
            'name' => 'new site name',
            'description' => 'new desc',
            'history' => 'new history',
            'establishment_date' => '2019-01-01',
        ];

        $this->patchJson('/api/site/1', $data, $headers)
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(200)
            ->assertJsonFragment([
                'name' => 'new site name',
                'description' => 'new desc',
                'history' => 'new history',
                'establishment_date' => '2019-01-01',
                'end_date' => '2098-04-24',
            ]);
    }

    public function testUpdateAdditionalTreeSpeciesAction(): void
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        Queue::fake();

        $uploadResponse = $this->post('/api/uploads', [
            'upload' => $this->fakeValidCsv(),
        ], $headers);


        $data = [
            'name' => 'new site name',
            'description' => 'new desc',
            'history' => 'new history',
            'additional_tree_species' => $uploadResponse->json('data.id'),
        ];

        $response = $this->patchJson('/api/site/1', $data, $headers)
            ->assertHeader('Content-Type', 'application/json')
            ->assertStatus(200)
            ->assertJsonFragment([
                'name' => 'new site name',
                'description' => 'new desc',
                'history' => 'new history',
                'end_date' => '2098-04-24',
            ]);
    }

    public function testReadAllAction(): void
    {
        $headers = $this->getHeaders('jane@example.com', 'Password123');

        $response = $this->getJson('/api/sites', $headers);
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => 1,
            'programme_id' => 1,
            'name' => 'Some Site',
            'name_with_id' => '#1 - Some Site',
            'description' => 'A site, somewhere',
        ]);
    }

    public function testReadAllForUserAction(): void
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $response = $this->getJson('/api/my/sites', $headers);
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => 1,
            'programme_id' => 1,
            'name' => 'Some Site',
            'name_with_id' => '#1 - Some Site',
            'description' => 'A site, somewhere',
        ]);
        $response->assertJsonMissing([
            'id' => 7,
        ]);
    }

    public function testReadAllActionRequiresAdmin(): void
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $response = $this->getJson('/api/sites', $headers);
        $response->assertStatus(403);
    }

    public function testReadAction(): void
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $response = $this->getJson('/api/site/1/overview', $headers);
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => 1,
            'programme_id' => 1,
            'name' => 'Some Site',
            'name_with_id' => '#1 - Some Site',
            'description' => 'A site, somewhere',
            'next_due_submission_id' => 3,
            'workdays_paid' => 25,
            'workdays_volunteer' => 49,
            'total_workdays' => 74,
        ]);
        $response->assertJsonCount(1, 'data.media');
        $response->assertJsonPath('data.submissions.0.id', 1);
    }

    public function testReadActionAllowsAdminAccess(): void
    {
        $headers = $this->getHeaders('jane@example.com', 'Password123');

        $response = $this->getJson('/api/site/1/overview', $headers);
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => 1,
            'programme_id' => 1,
            'name' => 'Some Site',
            'name_with_id' => '#1 - Some Site',
            'description' => 'A site, somewhere',
            'next_due_submission_id' => 3,
            'workdays_paid' => 25,
            'workdays_volunteer' => 49,
        ]);
    }

    public function testReadAllByProgrammeAction(): void
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $this->getJson('/api/programme/1/sites', $headers)
        ->assertStatus(200)
        ->assertJsonFragment([
            'id' => 1,
            'programme_id' => 1,
            'name' => 'Some Site',
            'name_with_id' => '#1 - Some Site',
            'description' => 'A site, somewhere',
            'next_due_submission_id' => 3,
            'workdays_paid' => 25,
            'workdays_volunteer' => 49,
        ]);
    }

    public function testAddBoundaryToSiteAction(): void
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');
        $data = [
            'site_id' => 1,
            'boundary_geojson' => '{"type":"Polygon","coordinates":[[[-1.864006519317627,50.7219083651253],[-1.8627190589904783,50.7219083651253],[-1.8627190589904783,50.72276418262861],[-1.864006519317627,50.72276418262861],[-1.864006519317627,50.7219083651253]]]}',
        ];

        $response = $this->postJson('/api/site/boundary', $data, $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
    }

    public function testReadAllByProgrammeActionPaginatesAtFive(): void
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');
        $this->getJson('/api/programme/1/sites', $headers)
        ->assertStatus(200)
        ->assertJsonMissingExact([
            'id' => 6,
            'programme_id' => 1,
            'name' => 'Some Site',
            'description' => 'A site, somewhere',
        ]);
    }

    public function testReadAllByProgrammeActionCanGetSecondPage(): void
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');
        $this->getJson('/api/programme/1/sites?page=2', $headers)
        ->assertStatus(200)
        ->assertJsonFragment([
            'id' => 6,
            'programme_id' => 1,
            'name' => 'Some Site',
            'description' => 'A site, somewhere',
        ]);
    }

    public function testReadAllByProgrammeActionRequiresBelongingToProgramme(): void
    {
        $headers = $this->getHeaders('sue@example.com', 'Password123');
        $this->getJson('/api/programme/1/sites', $headers)
        ->assertStatus(403);
    }

    public function testAttachRestorationMethodsAction(): void
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $this->postJson('/api/site/1/restoration_methods', [
            'site_restoration_method_ids' => [1],
        ], $headers)
        ->assertStatus(201)
        ->assertJsonFragment([
            'id' => 1,
        ])
        ->assertJsonPath('data.restoration_methods.0.id', 1)
        ->assertJsonPath('data.restoration_methods.0.name', 'Mangrove Tree Restoration');
    }

    public function testAttachRestorationMethodsActionRequiresRestorationMethodIDs(): void
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $this->postJson('/api/site/1/restoration_methods', $headers)
        ->assertStatus(422);
    }

    public function testAttachRestorationMethodsActionRequiresRestorationMethodIDsToExist(): void
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $this->postJson('/api/site/1/restoration_methods', [
            'site_restoration_method_ids' => [10, 11, 2144],
        ], $headers)
        ->assertStatus(422);
    }

    public function testUpdateEstablishmentDateAction(): void
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $this->postJson('/api/site/1/establishment_date', [
            'establishment_date' => '2000-10-06',
        ], $headers)
        ->assertStatus(201)
        ->assertJsonFragment([
            'id' => 1,
            'programme_id' => 1,
            'name' => 'Some Site',
            'description' => 'A site, somewhere',
            'establishment_date' => '2000-10-06',
        ]);
    }

    public function testAttachLandTenureAction(): void
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $this->postJson('/api/site/1/land_tenure', [
            'land_tenure_ids' => [1],
        ], $headers)
        ->assertStatus(201)
        ->assertJsonFragment([
            'id' => 1,
            'programme_id' => 1,
        ])
        ->assertJsonPath('data.land_tenures.0.id', 1)
        ->assertJsonPath('data.land_tenures.0.name', 'Public');
    }

    public function testAttachLandTenureActionRequiresLandTenureIdToExist(): void
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $this->postJson('/api/site/1/land_tenure', [
            'land_tenure_ids' => [138274],
        ], $headers)
        ->assertStatus(422);
    }

    public function testCreateNarrativeAction(): void
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $this->postJson('/api/site/1/narrative', [
                'technical_narrative' => 'this is a technical narrative piece',
                'public_narrative' => 'this is a public narrative piece',
            ], $headers)
            ->assertStatus(201)
            ->assertJsonFragment([
                'id' => 1,
                'technical_narrative' => 'this is a technical narrative piece',
                'public_narrative' => 'this is a public narrative piece',
            ]);
    }

    public function testUpdateEstablishmentDateActionRequiresDate(): void
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $this->postJson('/api/site/1/establishment_date', [
            'establishment_date' => 'this is not a date',
        ], $headers)
        ->assertStatus(422);
    }

    public function testCreateNarrativeActionRequiresTechnicalNarrative(): void
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $this->postJson('/api/site/1/narrative', [
            'public_narrative' => 'this is a public narrative piece',
        ], $headers)
        ->assertStatus(422);
    }

    public function testCreateNarrativeActionRequiresPublicNarrative(): void
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $this->postJson('/api/site/1/narrative', [
            'technical_narrative' => 'this is a technical narrative piece',
        ], $headers)
        ->assertStatus(422);
    }

    public function testCreateNarrativeActionRequiresBeingJoinedToTheSitesProgramme(): void
    {
        $headers = $this->getHeaders('sue@example.com', 'Password123');

        $this->postJson('/api/site/1/narrative', [
            'technical_narrative' => 'this is a technical narrative piece',
            'public_narrative' => 'this is a public narrative piece',
        ], $headers)
        ->assertStatus(403);
    }

    public function testCreateAimAction(): void
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $this->postJson('/api/site/1/aims', [
            'aim_survival_rate' => 24,
            'aim_year_five_crown_cover' => 50,
            'aim_direct_seeding_survival_rate' => 50,
            'aim_natural_regeneration_trees_per_hectare' => 2000,
            'aim_natural_regeneration_hectares' => 137,
            'aim_soil_condition' => 'good',
            'aim_number_of_mature_trees' => 1000,
        ], $headers)
        ->assertStatus(201)
        ->assertJsonFragment([
            'id' => 1,
            'aim_survival_rate' => 24,
            'aim_year_five_crown_cover' => 50,
            'aim_direct_seeding_survival_rate' => 50,
            'aim_natural_regeneration_trees_per_hectare' => 2000,
            'aim_natural_regeneration_hectares' => 137,
            'aim_soil_condition' => 'good',
            'aim_number_of_mature_trees' => 1000,
        ]);
    }

    public function testCreateAimActionDoesNotRequireSurvivalRate(): void
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $this->postJson('/api/site/1/aims', [
            'aim_year_five_crown_cover' => 50,
        ], $headers)
        ->assertStatus(201);
    }

    public function testCreateAimActionRequiresYearFiveCrownCover(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->postJson('/api/site/1/aims', [
            'aim_survival_rate' => 50,
        ], $headers)
        ->assertStatus(422);
    }
}
