<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Auth;

class OffersControllerTest extends TestCase
{
    public function testCreateAction()
    {
        $data = [
            "name" => "Example Offer",
            "description" => "Lorem ipsum dolor sit amet",
            "land_types" => ["cropland","mangrove"],
            "land_ownerships" => ["private"],
            "land_size" => "lt_10",
            "land_continent" => "europe",
            "land_country" => null,
            "restoration_methods" => ["agroforestry", "reserve_corridors", "riparian_buffers"],
            "restoration_goals" => ["agriculture_and_commodities"],
            "funding_sources" => ["equity_investment", "loan_debt", "grant_with_reporting"],
            "funding_amount" => 1000000,
            "price_per_tree" => 1.5,
            "long_term_engagement" => false,
            "reporting_frequency" => "gt_quarterly",
            "reporting_level" => "low",
            "sustainable_development_goals" => ["goal_1", "goal_7", "goal_9", "goal_13"],
            "cover_photo" => null,
            "avatar" => null,
            "video" => null
        ];
        $token = Auth::attempt([
            "email_address" => "steve@example.com",
            "password" => "Password123"
        ]);
        $headers = [
            "Authorization" => "Bearer " . $token,
            "Content-Type" => "application/json"
        ];
        $response = $this->postJson("/api/offers", $data, $headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(201);
        $response->assertJsonStructure([
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
                "restoration_methods",
                "restoration_goals",
                "funding_sources",
                "funding_amount",
                "price_per_tree",
                "long_term_engagement",
                "reporting_frequency",
                "reporting_level",
                "sustainable_development_goals",
                "cover_photo",
                "avatar",
                "video",
                "created_at",
                "completed",
                "completed_at",
                "completed_by",
                "successful"
            ],
            "meta" => []
        ]);
    }

    public function testReadAllByOrganisationAction()
    {
        $token = Auth::attempt([
            "email_address" => "steve@example.com",
            "password" => "Password123"
        ]);
        $headers = [
            "Authorization" => "Bearer " . $token
        ];
        $response = $this->getJson("/api/organisations/1/offers", $headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            "data" => [
                [
                    "id",
                    "organisation_id",
                    "name",
                    "description",
                    "land_types",
                    "land_ownerships",
                    "land_size",
                    "land_continent",
                    "land_country",
                    "restoration_methods",
                    "restoration_goals",
                    "funding_sources",
                    "funding_amount",
                    "price_per_tree",
                    "long_term_engagement",
                    "reporting_frequency",
                    "reporting_level",
                    "sustainable_development_goals",
                    "cover_photo",
                    "avatar",
                    "video",
                    "created_at",
                    "completed",
                    "completed_at",
                    "completed_by",
                    "successful"
                ]
            ],
            "meta" => []
        ]);
    }

    public function testReadAction()
    {
        $token = Auth::attempt([
            "email_address" => "steve@example.com",
            "password" => "Password123"
        ]);
        $headers = [
            "Authorization" => "Bearer " . $token
        ];
        $response = $this->getJson("/api/offers/1", $headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(200);
        $response->assertJsonStructure([
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
                "restoration_methods",
                "restoration_goals",
                "funding_sources",
                "funding_amount",
                "price_per_tree",
                "long_term_engagement",
                "reporting_frequency",
                "reporting_level",
                "sustainable_development_goals",
                "cover_photo",
                "avatar",
                "video",
                "created_at",
                "completed",
                "completed_at",
                "completed_by",
                "successful"
            ],
            "meta" => []
        ]);
    }

    public function testUpdateAction()
    {
        $data = [
            "name" => "Bar",
        ];
        $token = Auth::attempt([
            "email_address" => "steve@example.com",
            "password" => "Password123"
        ]);
        $headers = [
            "Authorization" => "Bearer " . $token,
            "Content-Type" => "application/json"
        ];
        $response = $this->patchJson("/api/offers/1", $data, $headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(200);
        $response->assertJsonStructure([
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
                "restoration_methods",
                "restoration_goals",
                "funding_sources",
                "funding_amount",
                "price_per_tree",
                "long_term_engagement",
                "reporting_frequency",
                "reporting_level",
                "sustainable_development_goals",
                "cover_photo",
                "avatar",
                "video",
                "created_at",
                "completed",
                "completed_at",
                "completed_by",
                "successful"
            ],
            "meta" => []
        ]);
        $response->assertJson([
            "data" => [
                "name" => "Bar"
            ]
        ]);
    }

    public function testSearchAction()
    {
        $token = Auth::attempt([
            "email_address" => "steve@example.com",
            "password" => "Password123"
        ]);
        $headers = [
            "Authorization" => "Bearer " . $token
        ];
        $data = [
        	"filters" => [[
                "attribute" =>  "land_types",
                "operator" =>  "contains",
                "value" => [
                    "mangrove",
                    "cropland"
                ]]],
            "sortDirection" => "desc",
            "page" =>  1
        ];
        $response = $this->postJson("/api/offers/search", $data ,$headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            "data" => [
                [
                    "id",
                    "organisation_id",
                    "name",
                    "description",
                    "land_types",
                    "land_ownerships",
                    "land_size",
                    "land_continent",
                    "land_country",
                    "restoration_methods",
                    "restoration_goals",
                    "funding_sources",
                    "funding_amount",
                    "price_per_tree",
                    "long_term_engagement",
                    "reporting_frequency",
                    "reporting_level",
                    "sustainable_development_goals",
                    "cover_photo",
                    "avatar",
                    "video",
                    "created_at",
                    "compatibility_score",
                    "completed",
                    "completed_at",
                    "completed_by",
                    "successful"
                ]
            ],
            "meta" => []
        ]);
    }

    public function testCompleteAction()
    {
        $token = Auth::attempt([
            "email_address" => "steve@example.com",
            "password" => "Password123"
        ]);
        $headers = [
            "Authorization" => "Bearer " . $token,
            "Content-Type" => "application/json"
        ];
        $data = [
            "successful" => true
        ];
        $response = $this->patchJson("/api/offers/1/complete", $data, $headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(200);
        $response->assertJsonStructure([
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
                "restoration_methods",
                "restoration_goals",
                "funding_sources",
                "funding_amount",
                "price_per_tree",
                "long_term_engagement",
                "reporting_frequency",
                "reporting_level",
                "sustainable_development_goals",
                "cover_photo",
                "avatar",
                "video",
                "created_at",
                "completed",
                "completed_at",
                "completed_by",
                "successful"
            ],
            "meta" => []
        ]);
        $response->assertJson([
            "data" => [
                "completed" => true
            ]
        ]);
    }
}
