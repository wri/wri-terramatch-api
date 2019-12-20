<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;
use Illuminate\Support\Facades\Auth;

class TasksControllerTest extends TestCase
{
    public function testReadAllCarbonCertificationVersionsAction()
    {
        $token = Auth::attempt([
            "email_address" => "jane@example.com",
            "password" => "Password123"
        ]);
        $headers = [
            "Authorization" => "Bearer " . $token,
            "Content-Type" => "application/json"
        ];
        $response = $this->getJson("/api/tasks/carbon_certification_versions", $headers);
        $response->assertStatus(200);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertJsonStructure([
            "data" => [
                [
                    "id",
                    "status",
                    "approved_rejected_by",
                    "approved_rejected_at",
                    "rejected_reason",
                    "data" => [
                        "id",
                        "pitch_id",
                        "type",
                        "link"
                    ]
                ]
            ]
        ]);
    }

    public function testReadAllOrganisationDocumentVersionsAction()
    {
        $token = Auth::attempt([
            "email_address" => "jane@example.com",
            "password" => "Password123"
        ]);
        $headers = [
            "Authorization" => "Bearer " . $token,
            "Content-Type" => "application/json"
        ];
        $response = $this->getJson("/api/tasks/organisation_document_versions", $headers);
        $response->assertStatus(200);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertJsonStructure([
            "data" => [
                [
                    "id",
                    "status",
                    "approved_rejected_by",
                    "approved_rejected_at",
                    "rejected_reason",
                    "data" => [
                        "id",
                        "organisation_id",
                        "name",
                        "type",
                        "document"
                    ]
                ]
            ]
        ]);
    }

    public function testReadAllOrganisationVersionsAction()
    {
        $token = Auth::attempt([
            "email_address" => "jane@example.com",
            "password" => "Password123"
        ]);
        $headers = [
            "Authorization" => "Bearer " . $token,
            "Content-Type" => "application/json"
        ];
        $response = $this->getJson("/api/tasks/organisation_versions", $headers);
        $response->assertStatus(200);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertJsonStructure([
            "data" => [
                [
                    "id",
                    "status",
                    "approved_rejected_by",
                    "approved_rejected_at",
                    "rejected_reason",
                    "data" => [
                        "id",
                        "name",
                        "description",
                        "address_1",
                        "address_2",
                        "city",
                        "state",
                        "zip_code",
                        "country",
                        "phone_number",
                        "website",
                        "type",
                        "category",
                        "facebook",
                        "twitter",
                        "linkedin",
                        "instagram",
                        "avatar",
                        "cover_photo",
                        "founded_at"
                    ]
                ]
            ]
        ]);
    }

    public function testReadAllPitchDocumentVersionsAction()
    {
        $token = Auth::attempt([
            "email_address" => "jane@example.com",
            "password" => "Password123"
        ]);
        $headers = [
            "Authorization" => "Bearer " . $token,
            "Content-Type" => "application/json"
        ];
        $response = $this->getJson("/api/tasks/pitch_document_versions", $headers);
        $response->assertStatus(200);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertJsonStructure([
            "data" => [
                [
                    "id",
                    "status",
                    "approved_rejected_by",
                    "approved_rejected_at",
                    "rejected_reason",
                    "data" => [
                        "id",
                        "pitch_id",
                        "name",
                        "type",
                        "document",
                    ]
                ]
            ]
        ]);
    }

    public function testReadAllPitchVersionsAction()
    {
        $token = Auth::attempt([
            "email_address" => "jane@example.com",
            "password" => "Password123"
        ]);
        $headers = [
            "Authorization" => "Bearer " . $token,
            "Content-Type" => "application/json"
        ];
        $response = $this->getJson("/api/tasks/pitch_versions", $headers);
        $response->assertStatus(200);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertJsonStructure([
            "data" => [
                [
                    "id",
                    "status",
                    "approved_rejected_by",
                    "rejected_reason",
                    "data" => [
                        "id",
                        "organisation_id",
                        "name",
                        "description",
                        "land_types",
                        "land_ownerships",
                        "land_size",
                        "land_continent",
                        "land_country",
                        "land_geojson",
                        "restoration_methods",
                        "restoration_goals",
                        "funding_sources",
                        "funding_amount",
                        "revenue_drivers",
                        "estimated_timespan",
                        "long_term_engagement",
                        "reporting_frequency",
                        "reporting_level",
                        "sustainable_development_goals",
                        "avatar",
                        "cover_photo",
                        "video"
                    ]
                ]
            ]
        ]);
    }

    public function testReadAllRestorationMethodMetricVersionsAction()
    {
        $token = Auth::attempt([
            "email_address" => "jane@example.com",
            "password" => "Password123"
        ]);
        $headers = [
            "Authorization" => "Bearer " . $token,
            "Content-Type" => "application/json"
        ];
        $response = $this->getJson("/api/tasks/restoration_method_metric_versions", $headers);
        $response->assertStatus(200);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertJsonStructure([
            "data" => [
                [
                    "id",
                    "status",
                    "approved_rejected_by",
                    "approved_rejected_at",
                    "rejected_reason",
                    "data" => [
                        "id",
                        "pitch_id",
                        "restoration_method",
                        "experience",
                        "land_size",
                        "price_per_hectare",
                        "biomass_per_hectare",
                        "carbon_impact",
                        "species_impacted" => []
                    ]
                ]
            ]
        ]);
    }

    public function testReadAllTreeSpeciesVersionsAction()
    {
        $token = Auth::attempt([
            "email_address" => "jane@example.com",
            "password" => "Password123"
        ]);
        $headers = [
            "Authorization" => "Bearer " . $token,
            "Content-Type" => "application/json"
        ];
        $response = $this->getJson("/api/tasks/tree_species_versions", $headers);
        $response->assertStatus(200);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertJsonStructure([
            "data" => [
                [
                    "id",
                    "status",
                    "approved_rejected_by",
                    "approved_rejected_at",
                    "rejected_reason",
                    "data" => [
                        "id",
                        "pitch_id",
                        "name",
                        "is_native",
                        "count",
                        "price_to_plant",
                        "price_to_maintain",
                        "saplings",
                        "site_prep",
                        "survival_rate",
                        "produces_food",
                        "produces_firewood",
                        "produces_timber",
                        "owner",
                        "season"
                    ]
                ]
            ]
        ]);
    }

    public function testReadAllMatchesAction()
    {
        Artisan::call("find-matches");
        $token = Auth::attempt([
            "email_address" => "jane@example.com",
            "password" => "Password123"
        ]);
        $headers = [
            "Authorization" => "Bearer " . $token,
            "Content-Type" => "application/json"
        ];
        $response = $this->getJson("/api/tasks/matches", $headers);
        $response->assertStatus(200);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertJsonStructure(["data" => [
            [
                "id",
                "offer_id",
                "offer_interest_id",
                "offer_contacts" => [
                    [
                        "first_name",
                        "last_name",
                        "avatar",
                        "email_address",
                        "phone_number"
                    ]
                ],
                "pitch_id",
                "pitch_interest_id",
                "pitch_contacts" => [
                    [
                        "first_name",
                        "last_name",
                        "avatar",
                        "email_address",
                        "phone_number"
                    ]
                ],
                "matched_at"
            ]
        ]]);
    }
}
