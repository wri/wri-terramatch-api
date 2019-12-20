<?php

namespace Tests\Feature;

use Tests\TestCase;

class DataControllerTest extends TestCase
{
    public function testReadAllCountriesAction()
    {
        $response = $this->getJson("/api/countries");
        $response->assertStatus(200);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertJsonStructure(["data" => [["name", "code"]]]);
    }

    public function testReadAllOrganisationTypesAction()
    {
        $response = $this->getJson("/api/organisation_types");
        $response->assertStatus(200);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertJsonStructure(["data" => [["name", "type"]]]);
    }

    public function testReadAllOrganisationCategoriesAction()
    {
        $response = $this->getJson("/api/organisation_categories");
        $response->assertStatus(200);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertJsonStructure(["data" => [["name", "category"]]]);
    }

    public function testReadAllDocumentTypesAction()
    {
        $response = $this->getJson("/api/document_types");
        $response->assertStatus(200);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertJsonStructure(["data" => [["name", "type"]]]);
    }

    public function testReadAllLandTypesAction()
    {
        $response = $this->getJson("/api/land_types");
        $response->assertStatus(200);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertJsonStructure(["data" => [["name", "type"]]]);
    }

    public function testReadAllLandOwnershipsAction()
    {
        $response = $this->getJson("/api/land_ownerships");
        $response->assertStatus(200);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertJsonStructure(["data" => [["name", "ownership"]]]);
    }

    public function testReadAllRestorationMethodsAction()
    {
        $response = $this->getJson("/api/restoration_methods");
        $response->assertStatus(200);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertJsonStructure(["data" => [["name", "method"]]]);
    }

    public function testReadAllRestorationGoalsAction()
    {
        $response = $this->getJson("/api/restoration_goals");
        $response->assertStatus(200);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertJsonStructure(["data" => [["name", "goal"]]]);
    }

    public function testReadAllFundingSourcesAction()
    {
        $response = $this->getJson("/api/funding_sources");
        $response->assertStatus(200);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertJsonStructure(["data" => [["name", "source"]]]);
    }

    public function testReadAllSustainableDevelopmentGoalsAction()
    {
        $response = $this->getJson("/api/sustainable_development_goals");
        $response->assertStatus(200);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertJsonStructure(["data" => [["name", "goal"]]]);
    }

    public function testReadAllReportingLevelsAction()
    {
        $response = $this->getJson("/api/reporting_levels");
        $response->assertStatus(200);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertJsonStructure(["data" => [["name", "level"]]]);
    }

    public function testReadAllReportingFrequenciesAction()
    {
        $response = $this->getJson("/api/reporting_frequencies");
        $response->assertStatus(200);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertJsonStructure(["data" => [["name", "frequency"]]]);
    }

    public function testReadAllContinentsAction()
    {
        $response = $this->getJson("/api/continents");
        $response->assertStatus(200);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertJsonStructure(["data" => [["name", "continent"]]]);
    }

    public function testReadAllRevenueDriversAction()
    {
        $response = $this->getJson("/api/revenue_drivers");
        $response->assertStatus(200);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertJsonStructure(["data" => [["name", "driver"]]]);
    }

    public function testReadAllCarbonCertificationTypesAction()
    {
        $response = $this->getJson("/api/carbon_certification_types");
        $response->assertStatus(200);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertJsonStructure(["data" => [["name", "type"]]]);
    }

    public function testReadAllTreeSpeciesOwnersAction()
    {
        $response = $this->getJson("/api/tree_species_owners");
        $response->assertStatus(200);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertJsonStructure(["data" => [["name", "owner"]]]);
    }

    public function testReadAllLandSizesAction()
    {
        $response = $this->getJson("/api/land_sizes");
        $response->assertStatus(200);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertJsonStructure(["data" => [["name", "size"]]]);
    }
}
