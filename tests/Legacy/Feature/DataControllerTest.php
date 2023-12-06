<?php

namespace Tests\Legacy\Feature;

use Tests\Legacy\LegacyTestCase;

final class DataControllerTest extends LegacyTestCase
{
    public function testReadAllCountriesAction(): void
    {
        $response = $this->getJson('/api/countries');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure(['data' => [['name', 'code', 'continent']]]);
    }

    public function testReadAllOrganisationTypesAction(): void
    {
        $response = $this->getJson('/api/organisation_types');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure(['data' => [['name', 'type']]]);
    }

    public function testReadAllOrganisationCategoriesAction(): void
    {
        $response = $this->getJson('/api/organisation_categories');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure(['data' => [['name', 'category']]]);
    }

    public function testReadAllDocumentTypesAction(): void
    {
        $response = $this->getJson('/api/document_types');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure(['data' => [['name', 'type']]]);
    }

    public function testReadAllLandTypesAction(): void
    {
        $response = $this->getJson('/api/land_types');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure(['data' => [['name', 'type']]]);
    }

    public function testReadAllLandOwnershipsAction(): void
    {
        $response = $this->getJson('/api/land_ownerships');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure(['data' => [['name', 'ownership']]]);
    }

    public function testReadAllRestorationMethodsAction(): void
    {
        $response = $this->getJson('/api/restoration_methods');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure(['data' => [['name', 'method']]]);
    }

    public function testReadAllRestorationGoalsAction(): void
    {
        $response = $this->getJson('/api/restoration_goals');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure(['data' => [['name', 'goal']]]);
    }

    public function testReadAllFundingSourcesAction(): void
    {
        $response = $this->getJson('/api/funding_sources');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure(['data' => [['name', 'source']]]);
    }

    public function testReadAllSustainableDevelopmentGoalsAction(): void
    {
        $response = $this->getJson('/api/sustainable_development_goals');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure(['data' => [['name', 'goal']]]);
    }

    public function testReadAllReportingLevelsAction(): void
    {
        $response = $this->getJson('/api/reporting_levels');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure(['data' => [['name', 'level']]]);
    }

    public function testReadAllReportingFrequenciesAction(): void
    {
        $response = $this->getJson('/api/reporting_frequencies');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure(['data' => [['name', 'frequency']]]);
    }

    public function testReadAllContinentsAction(): void
    {
        $response = $this->getJson('/api/continents');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure(['data' => [['name', 'continent']]]);
    }

    public function testReadAllRevenueDriversAction(): void
    {
        $response = $this->getJson('/api/revenue_drivers');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure(['data' => [['name', 'driver']]]);
    }

    public function testReadAllCarbonCertificationTypesAction(): void
    {
        $response = $this->getJson('/api/carbon_certification_types');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure(['data' => [['name', 'type']]]);
    }

    public function testReadAllLandSizesAction(): void
    {
        $response = $this->getJson('/api/land_sizes');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure(['data' => [['name', 'size']]]);
    }

    public function testReadAllRejectedReasonsAction(): void
    {
        $response = $this->getJson('/api/rejected_reasons');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure(['data' => [['name', 'reason']]]);
    }

    public function testReadAllFundingBracketsAction(): void
    {
        $response = $this->getJson('/api/funding_brackets');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure(['data' => [['name', 'bracket']]]);
    }

    public function testReadAllVisibilitiesAction(): void
    {
        $response = $this->getJson('/api/visibilities');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure(['data' => [['name', 'visibility']]]);
    }

    public function testReadAllTerrafundLandTenuresAction(): void
    {
        $response = $this->getJson('/api/terrafund/site/land_tenures');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonFragment([
            'name' => 'Public',
            'key' => 'public',
        ]);
    }

    public function testReadAllTerrafundRestorationMethodsAction(): void
    {
        $response = $this->getJson('/api/terrafund/site/restoration_methods');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonFragment([
            'name' => 'Mangrove Tree Restoration',
            'key' => 'mangrove_tree_restoration',
        ]);
    }
}
